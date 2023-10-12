<?php
require_once 'DB_config.php';

$conn = mysqli_connect(DB_HOST, DB_username, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    echo $conn->connect_error;
}
function db_connection(){
    global $conn;
    return $conn;
}
function queryMysql($query)
  {
      global $conn;
      $result = $conn->query($query);
    if (!$result) die("Fatal Error" . $conn->error);
    return $result;
  }

  function destroySession()
  {
    $_SESSION=array();

    if (session_id() != "" || isset($_COOKIE[session_name()]))
      setcookie(session_name(), '', time()-2592000, '/');

    session_destroy();
  }

  function sanitizeString($var): string
  {
      global $conn;
      $var = strip_tags($var);
      $var = htmlentities($var, ENT_QUOTES, 'UTF-8');
      return $conn->real_escape_string($var);
  }

  function reverseSanitizeString($var): string
{
    global $conn;
    
    // To reverse real_escape_string, use stripslashes
    $var = stripslashes($var);
    
    // To reverse htmlentities, use html_entity_decode
    $var = html_entity_decode($var, ENT_QUOTES, 'UTF-8');
    
    return $var;
}
  
?>