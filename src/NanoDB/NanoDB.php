<?php

class NanoDB
{
    /**
     * @return PDO
     */
    protected static $instance = [];

    protected function __construct()
    {
    }

    /**
     * @deprecated
     * Let use `group by id` instead of this function.
     */
    public static function find_several($sql, $db = null, $params = [])
    {
        $timeStart = microtime(true);
        if (is_null($db)) {
            $db = self::connect();
        } else {
            // @todo find all calls in logs and replace
            logs::trace(1, "warn:find_several with not null DB");
        }
        logs::tag("SQL", __FUNCTION__, ["cache" => 'no', "sql" => $sql, $params], 1);
        if (is_string($db)) {
            $db = self::connect($db);
        }
        if ($db instanceof PDO) {
            $q = $db->prepare($sql);
            $err = $db->errorInfo();
            if ($err[1] != null) {
                logs::error("PDO::Error", ["cache" => 'no', "sql" => $sql, "error" => $err]);
            }
            $q->execute($params);
            $err = $db->errorInfo();
            if ($err[1] != null) {
                logs::error("PDO::Error", ["cache" => 'no', "sql" => $sql, "error" => $err]);
            }
            $sqlres = $q->fetchAll(PDO::FETCH_ASSOC);
            $res = [];
            foreach ($sqlres as $row) {
                $id = $row['id'];
                $res[$id] = $row;
            }
            $timeEnd = microtime(true);
            $timeAll = $timeEnd - $timeStart;
            $timeAll = round($timeAll, 3);
            logs::tag("SQL", ["cache" => 'no', "sql" => $sql, "time" => $timeAll]);
            return $res;
        }

        $tmp = array();
        $res = command($sql, $db);
        if ($res) {
            while ($row = mysqli_fetch_array($res)) {
                $id = $row['id'];
                $tmp[$id] = $row;
            }
            mysqli_free_result($res);
        }
        return $tmp;
    }

    public static function findOneCached($sql,   $params = [], $mode = null, $ttl = 300)
    {
        if ($mode === null) {
            $mode = AppCache::$CacheType_ifNotNull;
        }


        return AppCache::getValue("find_many", ['sql' => $sql, "params" => $params],  function () use ($sql,  $params) {

            return NanoDB::find_one($sql, null, $params);
        }, $ttl, function ()  use ($sql,  $params) {

            logs::tag("SQL", __FUNCTION__, ["cache" => 'fromCache', "sql" => $sql, $params], 1);
        }, $mode);
    }

    public static function findManyCached($sql,  $params = [], $mode = null, $ttl = 300)
    {
        if ($mode === null) {
            $mode = AppCache::$CacheType_ifNotNull;
        }

        return AppCache::getValue("find_many", ['sql' => $sql, "params" => $params],  function () use ($sql,  $params) {

            return NanoDB::find_many($sql, null, $params);
        }, $ttl, function ()  use ($sql,  $params) {

            logs::tag("SQL", __FUNCTION__, ["cache" => 'fromCache', "sql" => $sql, $params], 1);
        }, $mode);
    }

    public static function queryCached($sql,  $params = [], $mode = AppCache_ifNotNull, $ttl = 300)
    {
        return AppCache::getValue("query", ['sql' => $sql, "params" => $params],  function () use ($sql,  $params) {

            return NanoDB::query($sql, $params);
        }, $ttl, function ()  use ($sql,  $params) {

            logs::tag("SQL", __FUNCTION__, ["cache" => 'skip', "sql" => $sql, $params], 1);
        }, $mode);
    }

    public static function find_many($sql, $db = null, $params = null)
    {
        $timeStart = microtime(true);
        if (is_array($db) && $params === null) {
            $params = $db;
            $db = null;
        }
        if (is_null($db)) {
            $db = self::connect();
        } else {
            // @todo find all calls in logs and replace
            logs::trace(1, "warn:find_many with not null DB");
        }
        logs::tag("SQL", __FUNCTION__, ["cache" => 'no', "sql" => $sql, $params], 1);
        if (is_string($db)) {
            $db = self::connect($db);
        }
        if ($db instanceof PDO) {
            $q = $db->prepare($sql);
            $err = $db->errorInfo();
            if ($err[1] != null) {
                logs::error("PDO::Error", ["cache" => 'no', "sql" => $sql, "error" => $err]);
            }
            $q->execute($params);
            $err = $db->errorInfo();
            if ($err[1] != null) {
                logs::error("PDO::Error", ["cache" => 'no', "sql" => $sql, "error" => $err]);
            }
            $timeEnd = microtime(true);
            $timeAll = $timeEnd - $timeStart;
            $timeAll = round($timeAll, 3);
            logs::tag("SQL", ["cache" => 'no', "sql" => $sql, "time" => $timeAll]);
            return $q->fetchAll(PDO::FETCH_ASSOC);
        }
        $set = array();
        $res = redcommand($sql, $db);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $set[] = $row;
            }
            mysqli_free_result($res);
        }
        return $set;
    }

    public static function find_one($sql, $db = null, $params = null)
    {
        $timeStart = microtime(true);
        if (is_array($db) && $params === null) {
            $params = $db;
            $db = null;
        }

        if (is_null($db)) {
            $db = self::connect();
        } else {
            // @todo find all calls in logs and replace
            logs::trace(1, "warn:find_one with not null DB");
        }
        if (is_string($db)) {
            $db = self::connect($db);
        }
        logs::tag("SQL", __FUNCTION__, ["cache" => 'no', "sql" => $sql, $params], 1);
        if ($db instanceof PDO) {
            $q = $db->prepare($sql);
            $err = $db->errorInfo();
            if ($err[1] != null) {
                logs::error("PDO::Error", ["cache" => 'no', "sql" => $sql, "error" => $err]);
            }
            $q->execute($params);
            $err = $db->errorInfo();
            if ($err[1] != null) {
                logs::error("PDO::Error", ["cache" => 'no', "sql" => $sql, "error" => $err]);
            }
            $timeEnd = microtime(true);
            $timeAll = $timeEnd - $timeStart;
            $timeAll = round($timeAll, 3);
            logs::tag("SQL", ["cache" => 'no', "sql" => $sql, "time" => $timeAll]);

            return $q->fetch(PDO::FETCH_ASSOC);
        }

        $row = array();
        $res = redcommand($sql, $db);
        if ($res) {
            if (mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
            }
            mysqli_free_result($res);
        }
        return $row;
    }

    /**
     * @return PDO
     * @link https://php.net/manual/en/class.pdo.php
     */
    public static function connect($dataBase = "core", $step = 5)
    {
        $db_user =  getenv('DB_USERNAME');
        $db_password = getenv('DB_PASSWORD');
        $db_host = getenv('DB_HOST');
        $db_port =  getenv('DB_PORT') ?: '3306';

        if (!$dataBase || empty($dataBase)) {
            $dataBase = "core";
        }

        if (!isset(self::$instance[$dataBase]) || !self::$instance[$dataBase]) {

            $ATTR_PERSISTENT =  getenv('PDO_PERSISTENT') !== 'false';
            try {
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="TRADITIONAL"',
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                    PDO::ATTR_PERSISTENT => $ATTR_PERSISTENT,
                ];

                self::$instance[$dataBase] = new PDO("mysql:host=$db_host;port=$db_port;charset=latin1;dbname=" . $dataBase, $db_user, $db_password, $options);
                self::$instance[$dataBase]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                logs::error("Error in DB:connect (host=$db_host;port=$db_port;dbname=$dataBase; user=$db_user)", $e->getMessage());

                if ($step > 0) {
                    logs::error("Error in DB:connect [re-try] (host=$db_host;port=$db_port;dbname=$dataBase; user=$db_user)", $e->getMessage());
                    return self::connect($dataBase, $step  - 1);
                }
                logs::error("Error in DB:connect (host=$db_host;port=$db_port;dbname=$dataBase; user=$db_user)", $e->getMessage());
                http_response_code(500);
                die("Unable to connect to database");
            }
        }

        return self::$instance[$dataBase];
    }

    public static function query($sql, $params = null)
    {
        $timeStart = microtime(true);
        $db = self::connect();
        $q = $db->prepare($sql);
        $err = $db->errorInfo();
        logs::tag("SQL", __FUNCTION__, ["cache" => 'no', "sql" => $sql, $params], 1);
        if ($err[1] != null) {
            logs::error("PDO::Error", ["cache" => 'no', "sql" => $sql, "error" => $err, "params" => $params]);
            return false;
        }
        $q->execute($params);

        $timeEnd = microtime(true);
        $timeAll = $timeEnd - $timeStart;
        $timeAll = round($timeAll, 3);
        logs::tag("SQL", ["cache" => 'no', "sql" => $sql, "time" => $timeAll]);

        $err = $db->errorInfo();
        if ($err[1] != null) {
            logs::error("PDO::Error", ["cache" => 'no', "sql" => $sql, "error" => $err, "params" => $params]);
            return false;
        }

        return true;
    }

    public static function insert($sql, $params = null)
    {
        $timeStart = microtime(true);
        $db = self::connect();
        $q = $db->prepare($sql);
        $err = $db->errorInfo();
        logs::tag("SQL", __FUNCTION__, ["cache" => 'no', "sql" => $sql, $params], 1);
        if ($err[1] != null) {
            logs::error("PDO::Error", ["cache" => 'no', "sql" => $sql, "error" => $err, "params" => $params]);
            return false;
        }
        $q->execute($params);
        $err = $db->errorInfo();
        if ($err[1] != null) {
            logs::error("PDO::Error", ["cache" => 'no', "sql" => $sql, "error" => $err, "params" => $params]);
            return false;
        }

        $r = $db->lastInsertId();
        if (!$r) {
            logs::error("PDO::Error", ["cache" => 'no', "sql" => $sql, "error" => 'lastInsertId is null', "params" => $params]);
            return false;
        }

        $timeEnd = microtime(true);
        $timeAll = $timeEnd - $timeStart;
        $timeAll = round($timeAll, 3);
        logs::tag("SQL", ["cache" => 'no', "sql" => $sql, "time" => $timeAll]);

        return $r;
    }
}
