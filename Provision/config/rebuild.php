<?php

/*
Revision history:

Date        Who     What
----        ---     ----
23-Aug-02   EWB     Created
26-Aug-02   EWB     Can create database if it does not exist.
26-Aug-02   EWB     Asks for confirmation before rebuild.
28-Aug-02   EWB     Minor phrasing changes, Alex suggestions.
29-Aug-02   EWB     Added scope to variable tables.
30-Aug-02   EWB     Log the event of a database rebuild.
 3-Sep-02   EWB     Fixed some typos.
 9-Sep-02   EWB     No action choice changed from index to admin.
 9-Sep-02   EWB     Added link back to machine page.
20-Sep-02   EWB     Giant refactoring
 4-Dec-02   EWB     Reorginization Day
10-Dec-02   EWB     Uses standard header
10-Dec-02   EWB     Factored shared code into libraries.
13-Jan-03   EWB     Removed 'Home' link.
13-Jan-03   EWB     Does not require register_globals
17-Jan-03   EWB     Access to $_SERVER variables.
17-Jan-03   EWB     Don't go back to admin page.
20-Jan-03   AAM     Removed extraneous $revl.  Updated databases:
                    Globals: removed type; added itype; added index uniq
                    Locals: removed type; added srev, itype; added index uniq
                    Revisions: removed crevl, srevl
22-Jan-03   EWB     Get user_data from it's library.
10-Feb-03   EWB     Uses sandbox libraries.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
23-Apr-04   EWB     Also remove GlobalCache, LocalCache.
13-May-05   EWB     New gconfig version
27-May-05   EWB     mysql 4 does not support mysql_create_db
12-Oct-05   BTE     Updated to support gconfig tables in core.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.

*/

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-cbld.php'  );
include_once ( '../lib/l-user.php'  );


    function para($txt)
    {
        return "<p>$txt</p>\n";
    }


    function database_exists($dbname,$db)
    {
        $exists = 0;
        $res  = (($___mysqli_tmp = mysqli_query($db, "SHOW DATABASES")) ? $___mysqli_tmp : false);
        if ($res)
        {
            $n = mysqli_num_rows($res);
            for ($i = 0; $i < $n; $i++)
            {
                $name = ((mysqli_data_seek($res, $i) && (($___mysqli_tmp = mysqli_fetch_row($res)) !== NULL)) ? array_shift($___mysqli_tmp) : false);
                if ($name == $dbname)
                {
                    $exists = 1;
                }
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
        return $exists;
    }


    function rebuild($name,$exist,$db)
    {
        CBLD_BuildGConfig('core',$db);
    }



    function regenerate($dbname,$exist,$db)
    {
        rebuild($dbname,$exist,$db);
        echo para('The account administration database has been rebuilt.');
        echo para(html_link('index.php','Home'));
    }


    function noaccess()
    {
        echo para('This operation requires administative access.');
        echo para('Permission denied.');
        echo para(html_link('index.php','Home'));
    }


/*
 |  This will remove the current site administration database
 |  and rebuild all of the database tables.  All of the current
 |  database values would be lost.  If you don't need to rebuild
 |  the tables, you might consider purging the database instead.
 */

    function confirm($self)
    {
        $y = "$self?confirm=1";
        $n = 'index.php';
        $p = 'purge.php';

        $ylnk = html_link($y,'Yes, go ahead');
        $nlnk = html_link($n,"No, don't do anything");
        $plnk = html_link($p,'purging');

        echo <<< WHAT

        <p>
          This will remove the current site
          administration database<br>and
          rebuild all of the database tables.

          All of the current<br>database
          values would be lost.

          If you don't need to rebuild<br>the
          tables, you might consider
          $plnk the database instead.
        </p>

        <p>
          Would you like to rebuild the site
          administration database?
        </p>

        <p>$ylnk</p>
        <p>$nlnk</p>
WHAT;

    }






   /*
    |  Main program
    */

    $db   = db_connect();
    $auth = process_login($db);
    $comp = component_installed();

    $confirm = get_integer('confirm',0);

    $user = user_data($auth,$db);

    $priv_admin = @ ($user['priv_admin'])? 1 : 0;
    $priv_debug = @ ($user['priv_debug'])? 1 : 0;


    $debug = $priv_debug;

   /*
    |  If the database does not yet exist, then we don't check the
    |  account privs ... we allow anyone at all to create the database.
    |  in the specific case when does not yet exist.
    */

    $dbname = 'core';
    $dbname = (getenv('DB_PREFIX') ?: '').'core';

    $exist = 0;
    if (database_exists($dbname,$db))
    {
        $exist = 1;
    }
    else
    {
        $confirm = 1;
        $priv_admin = 1;
    }


    $name = 'Rebuild Site Administration Database';
    $msg  = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                      // (now dump the buffer)
    echo standard_html_header($name,$comp,$auth,0,0,0,$db);
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    $self = $comp['self'];
    $does = ($exist)? 'does' : 'does not';
    debug_note("$dbname database $does exist");
    debug_note("debug:$debug admin:$priv_admin confirm:$confirm");

    if ($priv_admin)
    {
        if ($confirm)
        {
            regenerate($dbname,$exist,$db);
        }
        else
        {
            confirm($self);
        }
    }
    else
    {
        noaccess();
    }
    echo head_standard_html_footer($auth,$db);
