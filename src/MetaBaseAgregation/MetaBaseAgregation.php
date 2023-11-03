<?php

/**
 * Status page https://mbag-emea.nanoheal.app/Dashboard/cron/MetaBaseAgregation.php?status=1
 * Update https://mbag-emea.nanoheal.app/Dashboard/cron/MetaBaseAgregation.php?now=1
 * Force Update https://mbag-emea.nanoheal.app/Dashboard/cron/MetaBaseAgregation.php?now=force
 *
 */

class MetaBaseAgregation
{
    private $token;
    private $chartsId;
    private $pdo;
    public function __construct()
    {
        $this->pdo = NanoDB::connect('core');
        $this->chartsId = $this->getChartsId();
    }

    public function getToken(): String
    {

        $curl = curl_init();

        logs::log('MetaBaseAgregation getToken');


        $metaBaseUserName = getenv('METABASE_USERNAME');
        $metaBasePassword = getenv('METABASE_PASSWORD');
        $metaBaseSiteUrl = getenv('METABASE_SITE_URL');
        $queryString = json_encode(["username" => $metaBaseUserName, "password" => $metaBasePassword]);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $metaBaseSiteUrl.'/api/session',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $queryString,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Cookie: metabase.DEVICE=38fe75e7-984c-4f03-934a-9b55a7b3102d; metabase.SESSION=a063c847-d748-4b33-bdc8-9b8f427b2649; metabase.TIMEOUT=alive'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        logs::log('MetaBaseAgregation getToken answer', $response);
        $token = json_decode($response, true)['id'];


        if (!$token) {
            return 'null';
        }

        return $token;
    }

    private function getChartsId()
    {
        if (getenv('CHARTS_ID')) {
            // $chartsId = explode(',', getenv('CHARTS_ID'));
            $chartsId = explode(',', preg_replace("#[^0-9,]#", "", getenv('CHARTS_ID')));
            return $chartsId;
        }
        return [];
    }

    public function getChartsStatus()
    {
        if ($this->chartsId === []) {
            echo '<br>charts not set';
            return;
        }
        $now = date('U');
        $rc = RedisLink::connect();
        echo "<table>";
        echo "<tr>";
        echo "<td>chart</td>";
        echo "<td>status</td>";
        echo "<td>state</td>";
        echo "<td>in work time</td>";
        echo "<td>last update time</td>";
        echo "<td>last time spend</td>";
        echo "<td>last redis status</td>";
        echo "<td>last error</td>";
        echo "</tr>";
        foreach ($this->chartsId as $id) {
            echo "<tr>";
            echo "<td>{$id}</td>";

            $in_work = $rc->get("MetaBaseAgregation_Chart{$id}_time_in_work");
            $last_update = $rc->get("MetaBaseAgregation_Chart{$id}_last_update");
            $status = 'no data';
            $last_spend =  $rc->get("MetaBaseAgregation_Chart{$id}_spend");
            $last_error =  $rc->get("MetaBaseAgregation_Chart{$id}_last_error");

            $last_update_res = ($now -  $last_update);
            $in_work_res = ($now -  $in_work);

            if ($last_update > 0) {
                $in_work_res  = '--';
                $status = 'done';
            } else {
                $status = 'in work';
                $last_update_res = '--';
            }


            echo "<td>" . $status . "</td>";
            echo "<td>" . $rc->get("MetaBaseAgregation_Chart{$id}") . "</td>";
            echo "<td>" . ($in_work_res) . " sec</td>";
            echo "<td>" . ($last_update_res) . " sec</td>";
            echo "<td>" . ($last_spend) . " sec</td>";
            echo "<td>" . ($rc->get("MetaBaseAgregation_Chart{$id}")) . "</td>";
            echo "<td>" . ($last_error) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    public function getChartsInfo()
    {
      $metaBaseSiteUrl = getenv('METABASE_SITE_URL');
        logs::log('MetaBaseAgregation getChartsInfo', ["chartsId" => $this->chartsId]);
        if ($this->chartsId === []) {
            echo '<br>charts not set';
            return;
        }

        if ($this->getToken() === null) {
            logs::log("charts can't uploaded");
            return;
        }
        $this->token = $this->getToken();

        $rc = RedisLink::connect();

        foreach ($this->chartsId as $id) {
            $rc->set("MetaBaseAgregation_Chart{$id}_last_update", 0);
            $rc->set("MetaBaseAgregation_Chart{$id}", "waiting answer from metabase");
            $rc->set("MetaBaseAgregation_Chart{$id}_time_in_work", date('U'));
            $startTime = date('U');
            logs::log("MetaBaseAgregation getChartsInfo[{$id}] start");
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $metaBaseSiteUrl."/api/card/{$id}/query/json",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => array(
                    "X-Metabase-Session: {$this->token}",
                    "Cookie: metabase.DEVICE=38fe75e7-984c-4f03-934a-9b55a7b3102d; metabase.SESSION={$this->token}; metabase.TIMEOUT=alive"
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            logs::log("MetaBaseAgregation getChartsInfo[{$id}] get answer: $response");
            $rc->set("MetaBaseAgregation_Chart{$id}", "get answer");

            if (strpos($response, "ExceptionInfo") > 0) {
                $rc->set("MetaBaseAgregation_Chart{$id}", "get wrong answer");
                continue;
            }

            echo "<br><br>\n\n$id -> $response\n";
            $chartsInfo = json_decode($response, true);
            if (safe_count($chartsInfo)) {
                try {
                    $this->pdo->beginTransaction();
                    $delete_stmt = $this->pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'core.chartsData where chartId = ?');
                    $delete_stmt->execute([$id]);
                    logs::log("MetaBaseAgregation getChartsInfo[{$id}] delete old values", []);
                    $rc->set("MetaBaseAgregation_Chart{$id}", "delete old values");

                    foreach ($chartsInfo as $string) {
                        $chart_stmt = $this->pdo->prepare('insert into ' . $GLOBALS['PREFIX'] . 'core.chartsData (chartId, value, createdAt)' . 'values (?, ?, NOW())');

                        $data = json_encode($string);
                        $chart_stmt->execute([$id, $data]);
                        echo "<br><br>==>\t$id -> $data\n";
                    }
                    logs::log("MetaBaseAgregation getChartsInfo[{$id}] insert new values", []);
                    $rc->set("MetaBaseAgregation_Chart{$id}", "updated");
                    $rc->set("MetaBaseAgregation_Chart{$id}_last_update", date('U'));
                    $rc->set("MetaBaseAgregation_Chart{$id}_spend", date('U') - $startTime);
                    $this->pdo->commit();
                } catch (Exception | PDOException $e) {
                    $rc->set("MetaBaseAgregation_Chart{$id}_last_error", date("Y-m-d H:i:s") . "-" . json_encode($e));
                    $this->pdo->rollBack();
                    throw $e;
                }
            } else {
                $rc->set("MetaBaseAgregation_Chart{$id}", "empty answer");
                $rc->set("MetaBaseAgregation_Chart{$id}_last_error", date("Y-m-d H:i:s") . "-" . $response);
            }
        }
        logs::log('charts info uploaded');
        return;
    }
}
