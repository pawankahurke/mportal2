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

    echo SITE_Import('0c2a12d2a2810385b846d2694d9c8244',
        '{"VarValues":[{"def": 0, "host": "", "last": 0, "name": "", "revl": 3, "scop": 0, "valu": "5", "revldef": 1, "seminit": 0, '
        . '"varuniq": "d3302621e81fc7691272549e459d0f40", "mcatuniq": "a7d6475ec8993b7224d6facc8cb0ead6", "clientconf": 1, '
        . '"mgroupuniq": "0c2a12d2a2810385b846d2694d9c8244", "varnameuniq": "144430da5a65d913ce7103b8f595970b", '
        . '"varscopuniq": "072b030ba126b2f4b2374f342be9ed44", "revlclientconf": 0},{"def": 0, "host": "", "last": 0, '
        . '"name": "", "revl": 3, "scop": 0, "valu": "7", "revldef": 1, "seminit": 0, '
        . '"varuniq": "409b6fd5777f4202a99e0bc39e25bdbc", "mcatuniq": "a7d6475ec8993b7224d6facc8cb0ead6", "clientconf": 1, '
        . '"mgroupuniq": "0c2a12d2a2810385b846d2694d9c8244", "varnameuniq": "ddd44f84a1e5d08d6ba8f1888e4748e5", '
        . '"varscopuniq": "335f5352088d7d9bf74191e006d8e24c", "revlclientconf": 0}]}', $db);

?>