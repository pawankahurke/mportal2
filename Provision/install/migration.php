<?php

/*
  Revision history:

  Date                Who             What
  ----                ---             ----
  30-Sept-19          SVG       Created date. To add missing columns with respect to Mac,Linux and Ios client upload funct.
  03-Oct-19           SVG       Updated. Customer mapping related column/table addition
  13-Dec-19           SVG       Updated. sites table new column  : cid
 */

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once ( '../lib/l-util.php' );
include_once ( '../lib/l-db.php' );
include_once ( '../lib/l-sql.php' );
include_once ( '../lib/l-dberr.php' );
include_once ( '../lib/l-serv.php' );
include_once ( '../lib/l-rcmd.php' );
include_once ( '../lib/l-config.php' );


$db = db_connect();
db_change($GLOBALS['PREFIX'].'install', $db);

$col_arr = ['client_mac_name', 'client_ios_name', 'client_linux_name'];
$col_arr_SR = ['customerid'];
$col_arr_sites = ['cid'];
$col_arr_CUST = ['customerid', 'tenatid'];
$sql_create = "CREATE TABLE `Customers` (
	`cid` INT(11) NOT NULL AUTO_INCREMENT,
	`customer_name` VARCHAR(255) NULL DEFAULT NULL,
	`tenant_id` VARCHAR(100) NULL DEFAULT NULL,
	`sku_list` VARCHAR(255) NULL DEFAULT NULL,
	`created_time` INT(11) NULL DEFAULT '0',
        `last_update` INT(11) NULL DEFAULT '0',
	PRIMARY KEY (`cid`)
)
COLLATE='latin1_swedish_ci'
ENGINE=MyISAM
ROW_FORMAT=DYNAMIC
;";

echo '<pre>';
echo "migration process stated\n";

foreach ($col_arr as $key => $value) {
    analyze_col($value, 'Servers', $db);
    analyze_col($value, 'Sites', $db);
}

foreach ($col_arr_SR as $key_SR => $value_SR) {
    analyze_col_Cust($value_SR, 'serviceRequest', $db);
}

foreach ($col_arr_CUST as $key_CUST => $value_CUST) {
    analyze_col_Cust($value_CUST, 'Siteemail', $db);
}

foreach ($col_arr_sites as $key_SR => $value_SR) {
    analyze_col_int($value_SR, 'Sites', $db);
}

check_tbl_exists('Customers', $sql_create, $db);

$sql = "SELECT wsurl FROM Sites limit 1";
$res = command($sql, $db);
if(!$res) {
    $altSql = "ALTER TABLE " . $GLOBALS['PREFIX'] . "install.Sites ADD COLUMN wsurl VARCHAR(255) NULL DEFAULT NULL AFTER skuids;";
    $altRes = command($altSql, $db);
    if($altRes) {
        echo "<b>wsurl</b> has been added to table : Sites.\n";
    } else {
        echo "Failed to add <b>wsurl</b> to the table : Sites.\n";
    }
} else {
    echo "<b>wsurl</b> column already exists in table : Sites.\n";
}

function analyze_col_int($col, $table, $db) {
    $sql = "SELECT $col FROM $table limit 1";
    $res = command($sql, $db);
    if (!$res) {
        $sql_col = "ALTER TABLE $table ADD $col INT(11)";
        command($sql_col, $db);
        echo $col . " has been added to table : " . $table . "\n";
    } else {
        echo $col . " has been ignored to table : " . $table . "\n";
    }
    ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    return true;
}

function analyze_col($col, $table, $db) {
    $sql = "SELECT $col FROM $table limit 1";
    $res = command($sql, $db);
    if (!$res) {
        $sql_col = "ALTER TABLE $table ADD $col varchar(255)";
        command($sql_col, $db);
        echo $col . " has been added to table : " . $table . "\n";
    } else {
        echo $col . " has been ignored to table : " . $table . "\n";
    }
    ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    return true;
}

function analyze_col_Cust($col, $table, $db) {
    $sql = "SELECT $col FROM $table limit 1";
    $res = command($sql, $db);
    if (!$res) {
        $sql_col = "ALTER TABLE $table ADD $col varchar(100)";
        command($sql_col, $db);
        echo $col . " has been added to table : " . $table . "\n";
    } else {
        echo $col . " has been ignored to table : " . $table . "\n";
    }
    ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    return true;
}

function check_tbl_exists($table, $sql_create, $db) {
    $sql = "select 1 from $table LIMIT 1";
    $res = command($sql, $db);
    if (!$res) {
        $res = command($sql_create, $db);
        echo "Create table query executed: " . $table . "\n";
    } else {
        echo "Create table query ignored : " . $table . "\n";
    }
    ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    return true;
}

echo "migration process completed\n";
