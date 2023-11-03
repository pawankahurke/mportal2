<?php 

//error_reporting(-1);
//ini_set('display_errors', 'On');
include_once '../config.php';
include_once $absDocRoot . 'vendors/csrf-magic.php';

$_SESSION['windowtype'] = 'home';
$_SESSION['currentwindow'] = 'home';
$bussLevel = $_SESSION['user']['busslevel'];
$custEmail = $_SESSION['user']['adminEmail'];
$custName = $_SESSION['user']['companyName'];
global $kibana_url;

require_once '../layout/header_home.php'; 
//require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';
require_once '../home/home_html.php'; 
require_once '../layout/footer.php'; 

?>

