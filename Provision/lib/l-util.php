<?php

/*
Revision history:

Date        Who     What
----        ---     ----
19-Sep-02   EWB     Created.
10-Oct-02   EWB     Added get_argument
 7-Jan-03   EWB     created magic_unquote
 4-Mar-03   NL      server_var() modified to check for existance of var
22-May-03   EWB     Quote crusade continues.
30-Jul-03   EWB     Created html_target.
29-Aug-03   EWB     Created get_integer(), get_string()
11-Dec-03   EWB     Fixed a subtle defaulting bug.
28-Jan-04   EWB     Moved html_page here.
20-Sep-06   BTE     Added session functions.

*/

/*
    |  Formats the date/time in a human-friendly and
    |  unambiguous format.   This is actually the
    |  default format of the unix date command.
    */

function datestring($utime)
{
    return date('D M d H:i:s T Y', $utime);
}

function fontspeak($msg)
{
    return "<font face=\"verdana,helvetica\" size=\"2\">$msg</font>";
}

function html_link($href, $text)
{
    return "<a href=\"$href\">$text</a>";
}

function html_target($href, $target, $text)
{
    return "<a href=\"$href\" target=\"$target\">$text</a>";
}

function html_page($href, $text)
{
    return html_target($href, '_blank', $text);
}

/*
    |  Undo the effect of magic_quotes.
    |  Works on arrays as well as scalers.
    */

function magic_unquote($quoted, $valu)
{
    if (is_array($valu)) {
        reset($valu);
        foreach ($valu as $key => $data) {
            $valu[$key] = magic_unquote($quoted, $data);
        }
    } else {
        // if (get_magic_quotes_gpc()) {
        //     if (!$quoted) {
        //         $valu = stripslashes($valu);
        //     }
        // } else {
        if ($quoted) {
            $valu = safe_addslashes($valu);
        }
        // }
    }
    return $valu;
}


/*
    |  We have many sites running older versions of php.
    |  So ... we'll use the new superglobals if they
    |  exist, and fall back to the older method if
    |  that fails.
    |
    |  In any event, we want to continue to run
    |  whether register_globals is turned on or not,
    |  and also whether magic_quotes_gpc is turned
    |  on or not.
    |
    |  Quoted means we want it quoted or not, no
    |  matter what the magic_quotes are doing to us.
    */

function get_argument($name, $quoted, $default)
{
    $valu = $default;

    if ((isset($_GET)) || (isset($_POST))) {
        if (isset($_GET[$name]))  $valu = $_GET[$name];
        if (isset($_POST[$name])) $valu = $_POST[$name];
    } else {
        if (isset($GLOBALS['HTTP_GET_VARS'][$name]))
            $valu = $GLOBALS['HTTP_GET_VARS'][$name];
        if (isset($GLOBALS['HTTP_POST_VARS'][$name]))
            $valu = $GLOBALS['HTTP_POST_VARS'][$name];
    }
    return magic_unquote($quoted, $valu);
}


/*
    |  We have many sites running older versions of php.
    |  So ... we'll use the new superglobals if they
    |  exist, and fall back to the older method if
    |  that fails.
    |
    |  In any event, we want to continue to run
    |  whether register_globals is turned on or not.
    |
    |  http://www.php.net/manual/en/security.registerglobals.php
    |  http://www.php.net/manual/en/reserved.variables.php
    */

function server_var($name)
{
    if (isset($_SERVER)) {
        if (isset($_SERVER[$name]))
            return $_SERVER[$name];
        else
            return '';
    }
    if (isset($GLOBALS['HTTP_SERVER_VARS'])) {
        if (isset($GLOBALS['HTTP_SERVER_VARS'][$name]))
            return $GLOBALS['HTTP_SERVER_VARS'][$name];
        else
            return '';
    }
    if (isset($GLOBALS[$name])) {
        return $GLOBALS[$name];
    }
    return '';
}


/*
    |  We want to return the default if the requested
    |  argument exists, but is an empty string.  This
    |  happens when you want to read an integer from
    |  a posted input string ... if the user types
    |  nothing, we want the default value, NOT zero.
    */

function get_integer($name, $def)
{
    $tmp = get_argument($name, 0, '');
    return ($tmp == '') ? intval($def) : intval($tmp);
}

function get_string($name, $def)
{
    return trim(get_argument($name, 0, $def));
}


/* UTIL_GetStoredInteger

        Identical to get_integer, but will also search through the session
        data.
    */
function UTIL_GetStoredInteger($name, $def)
{
    $tmp = UTIL_GetStoredVariable($name, $def);
    return intval($tmp);
}


/* UTIL_GetStoredString

        Identical to get_string, but will also search through the session
        data.
    */
function UTIL_GetStoredString($name, $def)
{
    $tmp = UTIL_GetStoredVariable($name, $def);
    return trim($tmp);
}


/* UTIL_GetStoredVariable

        Attempts to retrieve the variable $name from the query string, post
        data, and session state.  Returns the value or $def if not found.
    */
function UTIL_GetStoredVariable($name, $def)
{
    $tmp = get_argument($name, 0, '');
    if ($tmp == '') {
        if (@$_SESSION && isset($_SESSION[$name])) {
            $tmp = $_SESSION[$name];
        } else {
            $tmp = $def;
        }
    }

    return $tmp;
}


/* UTIL_StoreSessionVariable

        Stores the variable $name with a value of $value into session state.
    */
function UTIL_StoreSessionVariable($name, $value)
{
    $_SESSION[$name] = $value;
}


/* UTIL_StoreEnvironment

        Stores the entire contents of the array $env into session state.  Each
        key in the array will be a variable name.
    */
function UTIL_StoreEnvironment($env)
{
    foreach ($env as $key => $value) {
        UTIL_StoreSessionVariable($key, $value);
    }
}


/* UTIL_ClearSession

        Deletes all currently stored session data for the existing session.
    */
function UTIL_ClearSession()
{
    $_SESSION = null;
}
