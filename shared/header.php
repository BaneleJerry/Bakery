<?php // Example 02: header.php

session_start();

echo <<<_INIT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
    <script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
    <script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
    <!--    <script src="static/js/javascript.js"></script>-->
    <link rel="stylesheet" href="../static/css/style.css" type="text/css">
    <script src="../static/js/javascript.js"></script>
_INIT;

require_once '../Database/DB_functions.php';

$usernamestr = 'Welcome Guest';
$randstr = substr(md5(rand()), 0, 7);

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $loggedin = TRUE;
    $usernamestr = "Logged in as: $username";
} else{$loggedin = FALSE;}

echo <<<_MAIN
<title>Banele-Bakery: $</title>
</head>
<body class="custom-body">
<header>
    <img class="logo" src="../static/images/logo.svg" alt="logo">
    <nav>
        <ul id='nav-list' class="nav__links"> 
_MAIN;
if ($loggedin) {
    echo <<<_LOGGEDIN
                <li><a href="#">Profile</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
_LOGGEDIN;
}else{
    echo <<<_LOGGEDIN
                <li><a href="../auth/login.php">Login</a></li>
                <li><a href="../auth/signup.php">Sign-Up</a></li>
        _LOGGEDIN;
} 
echo <<<HTML
            
        </ul>
    </nav>
</header>
<div id="content" class="div-page" data-role="content">
HTML;
// ?>