<?php
require_once '../shared/header.php';
$error = "";
$username = "";
$password = "";
if (isset($_SESSION['username'])) {
    redirectBasedOnRole($_SESSION['role']);
}

if (isset($_POST['username'])) {
    $username = sanitizeString($_POST['username']);
    $password = sanitizeString($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = 'Not all fields were entered';
    } else {
        // Retrieve the hashed password from the database
        $result = queryMySQL("SELECT username, password, role FROM Customers WHERE username='$username'");

        if ($result->num_rows == 0) {
            $error = "Your username or Password is incorrect";
        } else {
            $row = $result->fetch_assoc();
            $hashedPassword = $row['password'];

            // Use password_verify to check if the entered password matches the hashed password
            if (password_verify($password, $hashedPassword)) {
                $role = $row['role'];
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                redirectBasedOnRole($role);
            } else {
                $error = "Your username or Password is incorrect";
            }
        }
    }
}

function  redirectBasedOnRole ($role){
    if ($role == 'admin') {
        // Redirect to admin page using jQuery
        echo '<script type="text/javascript">';
        echo 'window.location.href = "../admin/index.php";';
        echo '</script>';
        exit();
    } else {
        // Redirect to username catalog page using jQuery
        echo '<script type="text/javascript">';
        echo 'window.location.href = "../user/catalog.php";';
        echo '</script>';
        exit();
    }
}
?>
<div class="log-container">
    <div class="login-signup-form">
        <h2>Login</h2>
        <?php
        if (!empty($error)) {
            echo '<p class="error-message">' . $error . '</p>';
        }
        ?>
        <form id="login-form" method="post" onsubmit="validateLoginForm();">
            <div data-role="fieldcontain" class="input-container">
                <label for="username">username</label>
                <input type="text" id="username" name="username">
            </div>
            <div data-role="fieldcontain" class="input-container">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
            </div>
            <button type="submit" id="custom-button">Login</button>
        </form>
        <p>Don't have an account? <a href="signup.php">Sign up</a></p>
    </div>
</div>
<?php require_once '../shared/footer.php'; ?>