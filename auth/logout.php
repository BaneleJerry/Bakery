<?php // Example 27-12: logout.php
  require_once '../shared/header.php';

  if (isset($_SESSION['username']))
  {
    destroySession();
    // Redirect to username catalog page using jQuery
    echo '<script type="text/javascript">';
    echo 'window.location.href = "../user/catalog.php";';
    echo '</script>';
    exit();
  }
  else echo "<div class='center'>You cannot log out because
             you are not logged in</div>";
?>
    </div>
  </body>
</html>
