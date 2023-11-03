<?PHP

if (url::requestToInt('debug') === 1) {
    error_reporting(-1);
    ini_set('display_errors', 'On');
}

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
global $redis_url;
global $redis_port;
global $redis_pwd;
$redis = new Redis();
$redis->connect($redis_url, $redis_port);
$redis->auth($redis_pwd);
$redis->select(0);
$machines = $redis->keys('*');
echo "<font color=blue>Machines Count :" . safe_count($machines) . "<br></font color=blue>";

$onlineCount = 0;
$offlineCount = 0;
$machineMsg = "";

for ($i = 0; $i < safe_count($machines); $i++) {

    $Redisres = $redis->lrange("$machines[$i]", 0, -1);
    if (safe_count($Redisres) > 0) {
        $redis->select(1);
        $allJobs = $redis->keys($machines[$i] . '*');
        $redis->select(0);
        if ($Redisres[5] === "Online") {

            $onlineCount++;
            if (stripos($Redisres[4], "windows") !== false) {
                echo "<font color=green>" . sprintf("%05d", $i) . ": " . $Redisres[0] . " [Windows] [Jobs= " . safe_count($allJobs) . "]</font color=green><br>";
            } else {
                echo "<font color=green>" . sprintf("%05d", $i) . ": " . $Redisres[0] . " [$Redisres[4]] [Jobs= " . safe_count($allJobs) . "]</font color=green><br>";
            }
        } else if ($Redisres[5] === "Offline") {
            $offlineCount++;
            if (stripos($Redisres[4], "windows") !== false) {
                echo "<font color=red>" . sprintf("%05d", $i) . ": " . $Redisres[0] . " [Windows] [Jobs= " . safe_count($allJobs) . "]</font color=red><br>";
            } else {
                echo "<font color=red>" . sprintf("%05d", $i) . ": " . $Redisres[0] . " [$Redisres[4]] [Jobs= " . safe_count($allJobs) . "]</font color=red><br>";
            }
        }
    } else {
        echo $machines[$i] . "Not found" . "<br>";
    }
    echo $machineMsg;
}
$servicetag = 'DESKTOP-E4A9SMV-3165673D705445239A50DE5883D52BB5';
$redis->select(1);
$allJobs = $redis->keys($servicetag . '*');
print_r($allJobs);
