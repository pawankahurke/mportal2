<?php



    define('ReportTypeInvalid',  '-1');
    define('ReportTypeDaily',     '0');
    define('ReportTypeWeekly',    '1');
    define('ReportTypeMonthly',   '2');
    define('ReportTypeWeekdays',  '3');
    define('ReportTypeImmediate', '4');

    $daynames = array (
        'Sunday'    ,
        'Monday'    ,
        'Tuesday'   ,
        'Wednesday' ,
        'Thursday'  ,
        'Friday'    ,
        'Saturday'  );

    $cyclenames = array (
        'Daily'     ,
        'Weekly'    ,
        'Monthly'   ,
        'Weekdays'  ,
        'Immediate' );

    $enames = array (
        'dummy'         => 'Nothing'        ,
        'idx'           => 'Record Index'   ,
        'scrip'         => 'Scrip'          ,
        'entered'       => 'Client Time'    ,
        'customer'      => 'Site'           ,
        'machine'       => 'Machine'        ,
        'username'      => 'User Name'      ,
        'clientversion' => 'Client Version' ,
        'clientsize'    => 'Client Size'    ,
        'priority'      => 'Priority'       ,
        'description'   => 'Description'    ,
        'type'          => 'Event Type'     ,
        'path'          => 'Path'           ,
        'executable'    => 'Executable'     ,
        'version'       => 'Version'        ,
        'size'          => 'Size'           ,
        'id'            => 'Identity'       ,
        'windowtitle'   => 'Window Title'   ,
        'string1'       => 'String 1'       ,
        'string2'       => 'String 2'       ,
        'text1'         => 'Text 1'         ,
        'text2'         => 'Text 2'         ,
        'text3'         => 'Text 3'         ,
        'text4'         => 'Text 4'         ,
        'servertime'    => 'Server Time'    ,
        'queryname'     => 'Query Filter'   );

    $valid1 = array ('dummy','customer','machine','username','scrip',
                     'executable','windowtitle','description','queryname');
    $valid2 = array ('dummy','customer','machine','username','scrip',
                     'executable','windowtitle','description','queryname');

    function fake_event_field()
    {
        return 'queryname';
    }

    function fake_event_list($db)
    {
        $list = event_fields($db);
        $list[] = fake_event_field();
        return $list;
    }

    function user_event_names()
    {
        global $enames;
        return $enames;
    }

?>

