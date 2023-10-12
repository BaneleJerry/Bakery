<?php

if (isset($_SESSION['username'])) {
  destroySession();
}
require_once '../shared/header.php';

$error = $username = $password = $email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = sanitizeString($_POST['username']);
  $password = sanitizeString($_POST['password']);
  $email = sanitizeString($_POST['email']);

  // Server-side validation
  if (empty($username) || empty($password) || empty($email)) {
    $error = 'Not all fields were entered<br><br>';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Invalid email format<br><br>';
  } elseif (strlen($password) < 6) {
    $error = 'Password must be at least 6 characters long<br><br>';
  } else {
    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM Customers WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $error = 'That username already exists<br><br>';
    } else {
      // Hash the password before storing it in the database
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

      $stmt = $conn->prepare("INSERT INTO Customers (username, password, email) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $username, $hashedPassword, $email);

      if ($stmt->execute()) {
        header('location: login.php');
        echo <<<_script
          <script
          alart(Your account has been created)
          </sript>
          _script;
      } else {
        $error = 'An error occurred while creating the account. Please try again later.<br><br>';
      }
    }
  }
}
?>

<script>
  function checkusername(username) {
    if (username.value === '') {
      $('#used').html('&nbsp;');
      return;
    }

    $.post(
      'checkusername.php',
      { username: username.value },
      function (data) {
        $('#used').html(data);
      }
    );
  }
</script>

<div class="log-container">
  <div class="login-signup-form" id="signup">
    <h2>Sign Up</h2>
    <?php
    if (!empty($error)) {
      echo '<p class="error-message">' . $error . '</p>';
    }
    ?>
    <form method="post">
      <div data-role="fieldcontain">
        <label for="username">username</label>
        <input type="text" id="username" name="username" value="<?php $username ?>" required onblur="checkusername(this)">
        <div class="error-message" id="used">&nbsp;</div>
      </div>
      <div data-role="fieldcontain">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <div data-role="fieldcontain">
        <label for="email">Email</label>
        <input type="text" id="email" name="email">
      </div>
      <button data-theme="b" type="submit" id="custom-button">Sign Up</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
  </div>
</div>

<?php require_once '../shared/footer.php'; ?>