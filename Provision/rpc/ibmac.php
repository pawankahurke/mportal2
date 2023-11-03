<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
08-Nov-04   AAM     Original creation.
10-Nov-04   AAM     Updated msg attribute to contain Scrip name; added
                    extensionName attribute; quoted quote characters in
                    attribute values

*/

/* This is the logging interface to the IBM Autonomic Computing environment.
    To enable it, browse to /main/acct/server.php on your server and set the
    option "event_code" to:

        "include ('ibmac.php');"

    Be sure to include the trailing semicolon, but not the quotes.  The server
    will then start writing event logs in CBE format into the file
    /tmp/events.cbe.
*/

    $fname  = "/tmp/events.cbe";
    $fh = fopen($fname,'a');
    if ($fh)
    {
        /* Set up CBE common attributes. */
        $creationTime = gmdate("Y-m-d\TH:i:s\Z", $args['entered']);
        $extensionName = 'CommonBaseEvent';
        $globalInstanceId = md5(uniqid(''));
        $localInstanceId = $args['idx'];
        $version = '1.0.1';
        $priority = $args['priority'] * 10;
        $msg = $args['description'] . '; ' . str_replace("\n", '; ',
            str_replace("\r\n", "\n", $args['text1']));
        $sequenceNumber = $args['idx'];
        /* Quote any of them that might be unsafe, by replacing " with \" */
        $msg = str_replace("\"", "\\\"", $msg);

        /* Set up situation attributes. */
        $categoryName = 'ReportSituation';
        $reasoningScope = 'INTERNAL';
        $reportCategory = 'STATUS';

        /* Set up sourceComponentId attributes. */
        $location = $args['machine'];
        $locationType = 'Hostname';
        if ($args['executable'] && $args['version'])
        {
            $application = $args['executable'] . '#' . $args['version'];
        }
        else if ($args['executable'])
        {
            $application = $args['executable'];
        }
        else
        {
            $application = $args['version'];
        }
        $executionEnvironment = $args['machine'] . ':' . $args['customer'];
        $component = $args['scrip'] . '.' . $args['description'];
        $subComponent = $args['clientversion'];
        $componentIdType = 'ProductName';
        $componentType = 'HandsFreeNetworks_Scrip';
        $processId = $args['id'];
        /* Quote any of them that might be unsafe, by replacing " with \" */
        $location =             str_replace("\"", "\\\"", $location);
        $application =          str_replace("\"", "\\\"", $application);
        $executionEnvironment = str_replace("\"", "\\\"", $executionEnvironment);
        $component =            str_replace("\"", "\\\"", $component);

        /* Set up the Info extended data element. */
        $infoStr =
            ($args['string1'] ? ($args['string1'] . "\n") : '') .
            ($args['string2'] ? ($args['string2'] . "\n") : '') .
            ($args['text2'] ? ($args['text2'] . "\n") : '') .
            $args['text3'];
        if ($infoStr)
        {
            $info = explode("\n", str_replace("\r\n", "\n", $infoStr));
        }
        else
        {
            $info = array();
        }

        /* Set up the Detail extended data element. */
        if ($args['text4'])
        {
            $detail = explode("\n", str_replace("\r\n", "\n", $args['text4']));
        }
        else
        {
            $detail = array();
        }

        /* Set up the ServerTime extended data element. */
        $serverTime = gmdate("Y-m-d\TH:i:s\Z", $args['servertime']);

        /* Now output the CBE log.  Note that there are multiple threads
            writing into the file, so we build up the output as a single
            string and then write it all at once to avoid having the output
            from multiple threads interleaving. */
        $outStr = "<CommonBaseEvent " .
            "creationTime=\"$creationTime\" " .
            "extensionName=\"$extensionName\" " .
            "globalInstanceId=\"$globalInstanceId\" " .
            "localInstanceId=\"$localInstanceId\" " .
            "version=\"$version\" " .
            "priority=\"$priority\" " .
            "msg=\"$msg\" " .
            "sequenceNumber=\"$sequenceNumber\">\n";

        $outStr .= "<situation categoryName=\"$categoryName\">\n" .
            "<situationType reasoningScope=\"$reasoningScope\" " .
                "reportCategory=\"$reportCategory\"/>\n" .
            "</situation>\n";

        $outStr .= "<sourceComponentId " .
            "location=\"$location\" " .
            "locationType=\"$locationType\" " .
            ($application ? "application=\"$application\" " : '') .
            "executionEnvironment=\"$executionEnvironment\" " .
            "component=\"$component\" " .
            "subComponent=\"$subComponent\" " .
            "componentIdType=\"$componentIdType\" " .
            "componentType=\"$componentType\" " .
            "processId=\"$processId\"/>\n";

        if (safe_count($info))
        {
            $outStr .= "<extendedDataElements name=\"Info\" " .
                "type=\"StringArray\">\n";
            for ($i=0; $i<count($info); $i++)
            {
                if ($info[$i])
                {
                    $outStr .= "<values>" . $info[$i] . "</values>\n";
                }
            }
            $outStr .= "</extendedDataElements>\n";
        }

        if (safe_count($detail))
        {
            $outStr .= "<extendedDataElements name=\"Detail\" " .
                "type=\"StringArray\">\n";
            for ($i=0; $i<count($detail); $i++)
            {
                if ($detail[$i])
                {
                    $outStr .= "<values>" . $detail[$i] . "</values>\n";
                }
            }
            $outStr .= "</extendedDataElements>\n";
        }

        $outStr .= "<extendedDataElements name=\"ServerTime\" type=\"dateTime\">\n" .
            "<values>$serverTime</values>\n" .
            "</extendedDataElements>\n";

        $outStr .= "</CommonBaseEvent>\n";

        fwrite($fh, $outStr);
        fclose($fh);
    }
    else
    {
        logs::log(__FILE__, __LINE__, "could not write to $fname",0);
    }
