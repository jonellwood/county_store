<?php
// session init

session_start();

// unset vars

$_SESSION = array();

// kill session

session_destroy();

// redirect back to login page
header("location: login-ldap.php");
exit;
