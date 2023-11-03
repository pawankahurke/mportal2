<?php

/*
Revision history:

Date        Who     What
----        ---     ----
30-Jan-03   EWB     Created.
18-Mar-03   EWB     Added server_def
12-Jun-03   EWB     server_int
23-Jul-03   EWB     work even if another database is selected.
16-Feb-04   EWB     Added server_name($db)
 7-Jul-04   EWB     update_opt returns number of changes.
26-Oct-04   EWB     find_opt, opt_insert, opt_update
27-Sep-05   BJS     Added find_site_email().
13-Oct-05   BJS     Added customize_name(), build_custom_list() & set_site_email().
18-Oct-05   BJS     Added anchors to customize_name().
06-Dec-05   BJS     find_site_email() error handling.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/


/*
    |  Returns an associative array of all the
    |  server options.  This is ok because the
    |  Options table is very small.
    */

function serveroptions($db)
{
    $opt = array();
    $sql = 'select * from Options';
    $res = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $name = $row['name'];
            $opt[$name] = $row['value'];
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $opt;
}


/*
    |  Note we don't use redcommand for this, it's called 
    |  all over the place.  I'd like to use find_one for
    |  this, but then everyone would have to include
    |  l-gsql ... so we do it the normal way.
    */

function find_opt($name, $db)
{
    $row = array();
    $qn  = safe_addslashes($name);
    $sql = "select * from " . $GLOBALS['PREFIX'] . "core.Options where name = '$qn'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_assoc($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $row;
}


/*
    |  Create a new server option.
    */

function opt_insert($name, $value, $ed, $db)
{
    $qn  = safe_addslashes($name);
    $qv  = safe_addslashes($value);
    $ed  = ($ed) ? 1 : 0;
    $now = time();
    $sql = "insert into " . $GLOBALS['PREFIX'] . "core.Options set\n"
        . " name = '$qn',\n"
        . " value = '$qv',\n"
        . " editable = $ed,\n"
        . " modified = $now";
    $res = redcommand($sql, $db);
    return affected($res, $db);
}


function opt_update($name, $value, $ed, $db)
{
    $qn  = safe_addslashes($name);
    $qv  = safe_addslashes($value);
    $ed  = ($ed) ? 1 : 0;
    $now = time();
    $sql = "update " . $GLOBALS['PREFIX'] . "core.Options set\n"
        . " value = '$qv',\n"
        . " editable = $ed,\n"
        . " modified = $now\n"
        . " where name = '$qn'";
    $res = command($sql, $db);
    return affected($res, $db);
}


/*
    |  This returns just a single server option,
    |  or the empty string.
    */

function server_opt($name, $db)
{
    $row = find_opt($name, $db);
    return ($row) ? $row['value'] : '';
}


/*
    |  This returns just a single server option,
    |  or the default value of the option is not 
    |  set or empty.  Note that many of the oem
    |  server variables (footer_left) exist
    |  and are empty.
    */

function server_def($name, $def, $db)
{
    $out = $def;
    $row = find_opt($name, $db);
    if ($row) {
        /* If value is blank use default */
        $tmp = $row['value'];
        if (($tmp) == ('')) {
            $out = $def;
        } else {
            $out = $tmp;
        }
    }
    return $out;
}


/*
    |  This does the same thing as server_def
    |  but we know we are expecting an integer.
    */

function server_int($name, $def, $db)
{
    $row = find_opt($name, $db);
    $val = ($row) ? $row['value'] : $def;
    return intval($val);
}

/*
    |  Updates the value of an already existing option.
    |  If the option doesn't exist yet, that's ok, just
    |  fail silently.  The issue of creating the option
    |  initially is handled separately.
    |
    |  It is important that this changes just the value
    |  of an existing option ... this means we can
    |  use it for locking.
    */

function update_opt($name, $valu, $db)
{
    $qn  = safe_addslashes($name);
    $qv  = safe_addslashes($valu);
    $sql = "update " . $GLOBALS['PREFIX'] . "core.Options\n"
        . " set value = '$qv'\n"
        . " where name = '$qn'";
    $res = command($sql, $db);
    return affected($res, $db);
}


/*
    |  Set server option $name to $value.
    |
    |  Returns the old value, if there was one.
    */

function server_set($name, $value, $db)
{
    $old = '';
    $row = find_opt($name, $db);
    if ($row) {
        $old = $row['value'];
        opt_update($name, $value, 1, $db);
    } else {
        opt_insert($name, $value, 1, $db);
    }
    return $old;
}


/*
    |  Note $SERVER_NAME does *NOT* include the port
    |  number when running on a non-standard port.
    |
    |  $HTTP_HOST does include the nonstandard port,
    |  but it just returns "localhost" when run
    |  from the cron job.  So, we just do the the
    |  best we can.
    |
    |  In php 4.2.2 $SERVER_NAME just returns localhost
    |  when run via curl for the cron jobs ...
    */

function server_name($db)
{
    $host = server_opt('server_name', $db);
    if ($host == '') {
        $host = server_var('SERVER_NAME');
    }
    if (($host == 'localhost') || ($host == '')) {
        $fqdn = `/bin/hostname -f`;
        $host = str_replace("\n", '', $fqdn);
    }
    return $host;
}


/*
    |  $site = name of site
    |  $db   = database handle
    |
    |  Returns the value of notify_sender or
    |  blank if not set.
    */
function find_site_email($site, $db)
{
    $qs = safe_addslashes($site);
    $sql = "select notify_sender from " . $GLOBALS['PREFIX'] . "core.Customers\n"
        . " where username = '' and\n"
        . " customer = '$qs'";
    $res = find_one($sql, $db);
    if (isset($res['notify_sender'])) {
        return $res['notify_sender'];
    }
    logs::log(__FILE__, __LINE__, "l-serv: notify_sender is not set for site($site)", 0);
    return '';
}


/*
    |  $site_email = desired site email address
    |  $db         = database handle
    |  $qs         = site name
    |  Attempts to set the notify_sender address
    |  to the $site_email value for the site
    |  $qs.
    |
    |  Returns true on success, false on failure.
    */
function set_site_email($qs, $site_email, $db)
{
    $good       = false;
    $site_email = safe_addslashes($site_email);

    $sql = "update Customers set\n"
        . " notify_sender  = '$site_email'\n"
        . " where username = ''\n"
        . " and customer   = '$qs'";
    $res = redcommand($sql, $db);
    if (affected($res, $db)) {
        $good = true;
    }
    return $good;
}



/*
    |  $custom = the name of the post variable.
    |  Returns an array indexed by the name
    |  of the variable(s) {if any} returned from
    |  the call get_string($custom,'');
    */
function build_custom_list($custom)
{
    $custom_list   = array();
    $custom_string = get_string($custom, '');
    $custom_list   = explode(',', $custom_string);
    $custom_list   = array_flip($custom_list);
    return $custom_list;
}


/*
    |  $name            = the current server variable
    |  $custom_list     = the list of variables we want to modify
    |  $text_type_start = the html text start tag
    |  $text_type_end   = the html text end tag
    |  Returns the name unmodified if the $name passed in doesn't equal any
    |  names (indexes) of $custom_list. If $name is found in custom list
    |  we place it between the start and end html tags.
    |    Added the ability to create an anchor where name is bold. 
    */
function customize_name($name, &$custom_list, $text_type_start, $text_type_end)
{
    if (isset($custom_list[$name])) {
        $anch = "<a name=\"#$name\"></a>";
        return "$anch $text_type_start $name $text_type_end";
    } else {
        return $name;
    }
}


/*
    |  Note we don't use redcommand for this, it's called 
    |  all over the place.
    |  This takes in the "name" of the options required and 
    |  retrieves it out of the database then Returns.
    */

function find_user_option($user, $db)
{
    $row = array();
    $name = safe_addslashes($user);
    $sql = "select * from " . $GLOBALS['PREFIX'] . "core.Users where username = '$name'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_assoc($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $row;
}


/*
    |  This returns just a single user option,
    |  or the default value of the option is not 
    |  set or empty.  Note that many of the oem
    |  server variables (footer_left) exist
    |  and are empty.
    */
function get_user_options($option, $user, $def, $db)
{
    $out = $def;
    $user_option = safe_addslashes($option);
    $row = find_user_option($user, $db);
    if ($row) {
        /* If value is blank use default */
        $tmp = $row[$user_option];
        //logs::log(__FILE__, __LINE__, "($user_option) = ($tmp)", 0);            
        if (($tmp) == ('')) {
            /* Returning the default */
            $out = $def;
        } else {
            /* Returning user defined option*/
            $out = $tmp;
        }
    }
    return $out;
}
