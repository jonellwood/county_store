<?php
// session init

session_start();

function removeSessionCookies()
{
    foreach ($_COOKIE as $key => $value) {
        setcookie($key, '', time() - 3600, '/');
    }
}
// unset vars

$_SESSION = array();

removeSessionCookies();
// kill session

// session_destroy();

// redirect back to login page
header("location: index.php");
exit;
