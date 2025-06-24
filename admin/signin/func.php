<?php

function validateCredentials($username, $password)
{
    if (trim($password) == "") return false;
    $ldapHost = $GLOBALS['ldapServer'];
    $ldapDomain = $GLOBALS['ldapDomain'];

    putenv('LDAPTLS_REQCERT=never');

    $ldapConn = ldap_connect($ldapHost) or die("Could not connect to LDAP");
    ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);

    if (@ldap_bind($ldapConn, $username . $ldapDomain, $password)) {
        return true;
    } else {
        return false;
    }
}