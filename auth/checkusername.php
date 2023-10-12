<?php // Example 27-6: checkusername.php
  require_once '../Database/DB_functions.php';

  if (isset($_POST['username']))
  {
    $username   = sanitizeString($_POST['username']);
    $result = queryMysql("SELECT * FROM customers WHERE username='$username'");
    if ($result->num_rows)
      echo "The username '$username' is taken";
    else
      echo "<span class='available'>&nbsp;&#x2714; " .
           "The usernamename '$username' is available</span>";
  }
?>
