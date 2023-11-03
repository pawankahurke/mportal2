<?php



    ob_start();

    include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
    include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();

include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-asstfiltr.php'  );
include_once ( '../lib/l-site.php'  );

    

    $now = time();
    $db   = db_connect();
    $auth = process_login($db);
    $comp = component_installed();

    $user  = user_data($auth,$db);
    $admin = @ ($user['priv_admin'])?  1 : 0;

    if(!$admin) {
        die("need admin");
    }

    echo SITE_Export('0c2a12d2a2810385b846d2694d9c8244', $db);
?>