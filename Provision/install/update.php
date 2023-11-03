<?php

/*
Revision History

16-May-03   NL      Initial creation, based on server/acct/update.php.
16-Jun-03   NL      Require login if admin accounts exist;
                    Allow create_init_user whenever no admin accounts exist;
                    Include header.php & l-user.php.
16-Jun-03   NL      Minor wording change.
24-Jun-03   NL      Undo Eric's workaround fix (bug fixed in header.php).
24-Jun-03   NL      Prompt to create an admin user account even if command != 1.
13-Jul-03   NL      check_any_admins(): use command, not redcommand.
15-Jul-03   NL      Add Users.emailsubject, Users.emailsender, Users.emailxheaders.
                    Add Sites.emailsubject, Sites.emailsender, Sites.emailxheaders.
 8-Aug-03   NL      Add Servers.global for servers that should be available to all.
24-Sep-03   NL      Add "install: " to error_log entries.
30-Sep-03   NL      Add numresponses & numinstalls columns to Siteemail table.
27-May-05   EWB     mysql 4 does not support mysql_create_db
07-Aug-06   BTE     Function find_tables is now in l-db.php.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
19-Jun-07   AAM     Big update for changes to database schema to support
                    automation.
27-Jun-07   AAM     Added user values for automation.
11-Jul-07   AAM     Updates to use machine groups for service offerings.
03-Oct-08   BTE     Bug 4828: Change customization feature of server.
25-Oct-08   AAM     Bug 4823: backed out "automation" related changes, but left
                    in some cleanups that were done at the same time.  This was
                    a pretty big change.

*/

die("Code was removed");
