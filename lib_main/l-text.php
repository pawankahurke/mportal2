<?php

    


    

    $report_display = 'Please select the display for which to'
                    . ' create a report:';
    $report_format  = 'Please select the report format type(s)'
                    . ' you would like:';
    $report_output  = 'Please select the output destination(s) of the report:';
    $config_output  = 'For every output format selected'
                    . ' the appropriate file extension will be generated'
                    . ' when the report is run.'
                    . '<br><br> Please configure the variables below:';
    $email_rcpt     = 'E-mail recipients:';
    $email_send     = 'E-mail sender:';
    $email_subj     = 'E-mail subject:';
    $email_file     = 'E-mail filename:';
    $ftp_user       = 'FTP Username:';
    $ftp_pass       = 'FTP Password:';
    $ftp_conf       = 'Verify FTP Password:';
    $ftp_url        = 'FTP URL:';
    $ftp_file       = 'FTP Filename:';
    $ftp_pasv       = 'FTP Passive Mode:';
    $get_file       = 'Filename:';
    $email_header   = 'E-mail';
    $ftp_header     = 'FTP';
    $dload_header   = 'Download';
    $email_rcpt_xpl = 'Enter all the recipients addresses seperated by commas';
    $email_send_xpl = 'Enter the from address';
    $email_subj_xpl = 'Enter text to be used as the subject line';
    $email_file_xpl = 'Enter the filename';
    $ftp_user_xpl   = '';
    $ftp_pass_xpl   = '';
    $ftp_conf_xpl   = '';
    $ftp_url_xpl    = "For example, <b>ftp.yoursite.com/path/to/folder</b>";

    $ftp_file_xpl   = 'Enter the filename';
    $ftp_pasv_xpl   = 'When passive FTP mode is enabled, the file transfer is'
                    . ' initiated by the ASI client instead of the FTP server.'
                    . ' It may be necessary for systems behind a firewall.'
                    . ' By default Passive FTP is enabled.';
    $get_file_xpl   = 'Enter the filename';

    

    define('constReportOutput',  $report_output);
    define('constReportFormat',  $report_format);
    define('constReportDisplay', $report_display);
    define('constConfigOutput',  $config_output);
    define('constEAddressDir',   $email_rcpt);
    define('constESenderDir',    $email_send);
    define('constESubjectDir',   $email_subj);
    define('constEFileDir',      $email_file);
    define('constFTPUserDir',    $ftp_user);
    define('constFTPPassDir',    $ftp_pass);
    define('constFTPConfDir',    $ftp_conf);
    define('constFTPUrlDir',     $ftp_url);
    define('constFTPFileDir',    $ftp_file);
    define('constFTPPasvDir',    $ftp_pasv);
    define('constDWNGetfileDir', $get_file);
    define('constELMHeader',     $email_header);
    define('constFTPHeader',     $ftp_header);
    define('constDWNHeader',     $dload_header);
    define('constEAddressXpl',   $email_rcpt_xpl);
    define('constESenderXpl',    $email_send_xpl);
    define('constESubjectXpl',   $email_subj_xpl);
    define('constEFileXpl',      $email_file_xpl);
    define('constFTPUserXpl',    $ftp_user_xpl);
    define('constFTPPassXpl',    $ftp_pass_xpl);
    define('constFTPConfXpl',    $ftp_conf_xpl);
    define('constFTPUrlXpl',     $ftp_url_xpl);
    define('constFTPFileXpl',    $ftp_file_xpl);
    define('constFTPPasvXpl',    $ftp_pasv_xpl);
    define('constDWNGetfileXpl', $get_file_xpl);

    $udns = 'You did not select';
    define('constSelectOutputError',    "$udns a format.");
    define('constSelectGenReportError', "$udns an output destination.");
    define('constSelectFormatError',    "$udns a display");

    $fib = 'field is blank';
    define('constFTPUserError',         "FTP User $fib");
    define('constFTPPassMisMatch',      'Passwords do not match.');
    define('constFTPPassError',         "FTP Password $fib.");
    define('constFTPConfError',         "Verfiy FTP Password $fib.");
    define('constFTPUrlError',          "FTP URL $fib.");
    define('constFTPFileError',         "FTP filename $fib.");
    define('constELMEaddressError',     "E-mail recipient address $fib.");
    define('constELMEsenderError',      "E-mail sender address $fib.");
    define('constELMEfileError',        "E-mail filename $fib.");
    define('constDWNGetfileError',      "Filename $fib.");
    

   
   
   

?>
