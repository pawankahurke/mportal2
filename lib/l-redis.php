<?PHP

$debug = 1;
if ($debug) {
}


// function allCustomers() {

//     global $redis_url;
//     global $redis_port;
//     global $redis_pwd;

//         $redis = new Redis();
//     $redis->connect($redis_url, $redis_port);
//     $redis->auth($redis_pwd);

//     $res = getall($redis);
//     $json = safe_json_decode($res, true);
//     return $json;
// }

// function getall($redis) {
//     $res = $redis->HGetall('MStat');

//     $arr = array();
//         foreach ($res as $key => $value) {
//         $temp = explode(":", $value);

//         $sname  = $temp[0];
//         $ordnum = $temp[1];
//         $OS     = $temp[3];

//         $status = "Offline";
//         if ($temp[4] == 1) {
//             $status = "Online";
//         }

//         if (empty($arr[$temp[2]])) {
//             $arr[$temp[2]][] = array("machine" => $key, "site" => $sname, "orderno" => $ordnum, "os" => $OS, "status" => $status);
//         } else {
//             $arr[$temp[2]][] = array("machine" => $key, "site" => $sname, "orderno" => $ordnum, "os" => $OS, "status" => $status);
//         }
//     }
//     return json_encode($arr);
// }


function getAllMachinesStatus($val)
{

    $redis = RedisLink::connect();
    $res = $redis->HGetall('MStat');

    $arr = array();
    foreach ($res as $key => $value) {
        $temp = explode(":", $value);
        if ($temp[2] != $val) {
            continue;
        }

        $sname  = $temp[0];
        $ordnum = $temp[1];
        $OS     = $temp[3];

        $status = "Offline";
        if ($temp[4] == 1) {
            $status = "Online";
        }

        if (empty($arr[$temp[2]])) {
            $arr[$temp[2]][] = array("machine" => $key, "site" => $sname, "orderno" => $ordnum, "os" => $OS, "status" => $status);
        } else {
            $arr[$temp[2]][] = array("machine" => $key, "site" => $sname, "orderno" => $ordnum, "os" => $OS, "status" => $status);
        }
    }
    return formatAllMachinesStatus($arr, $val);
}

function formatAllMachinesStatus($array, $customerNum)
{
    $resultArray = [];
    foreach ($array[$customerNum] as $key => $value) {
        $resultArray[$value['machine']]['site']    = isset($value['site']) ? $value['site'] : "";
        $resultArray[$value['machine']]['orderno'] = isset($value['orderno']) ? $value['orderno'] : "";
        $resultArray[$value['machine']]['os']      = isset($value['os']) ? $value['os'] : "Windows";
        $resultArray[$value['machine']]['status']  = isset($value['status']) ? $value['status'] : "Offline";
    }
    return $resultArray;
}

// function getDeviceStatus($DeviceTag) {
    
            
//         $OnlineOffline = "offline";
//     $OS = 'Windows';
    
//         global $redis_url;
//     global $redis_port;
//     global $redis_pwd;
  
//     $redis = new Redis();
//     $redis->connect($redis_url, $redis_port);
//     $redis->auth($redis_pwd);
    
//         $redis->select(0);
//     $res = $redis->lrange($DeviceTag, 4, 5);

//     if (!empty($res)) {
//                 $OnlineOffline = $res[1];
        
//     }
//     return $OnlineOffline;
// }