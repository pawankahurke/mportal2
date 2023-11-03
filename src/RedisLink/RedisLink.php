<?php

define('REDIS_PERSISTENCE', 6379);
define('REDIS_MEM_ONLY',  6378);
class RedisLink
{

    protected static $redis = null;
    protected static $redis_mem_only = null;

    public static $REDIS_PERSISTENCE = REDIS_PERSISTENCE;
    public static $REDIS_MEM_ONLY = REDIS_MEM_ONLY;

    public static function tryToSetLock($name, $ttl = 3600)
    {
        /**
         * cron for Daily sql tasks
         */

        // Set redis next run - 24h - this is the daly script.
        $now = time();

        $nextTime = $now + $ttl;
        $redis = self::connect();

        $CI_PIPELINE_ID = getenv('CI_PIPELINE_ID');
        $lockKey = $name . "_" . $CI_PIPELINE_ID;
        $oldStartCron = $redis->get($lockKey);
        if ($oldStartCron) {
            logs::log("Lock key=$lockKey Skip by time, next run in " . ($oldStartCron - $now) . " for CI_PIPELINE_ID=$CI_PIPELINE_ID");
            return false;
        }
        $redis->set($lockKey, $nextTime, $ttl);

        logs::log("Lock key=$lockKey Run by time, next run in " . ($oldStartCron - $now) . " for CI_PIPELINE_ID=$CI_PIPELINE_ID");
        return true;
    }

    public static function deleteLock($name)
    {
        $redis = self::connect();
        $CI_PIPELINE_ID = getenv('CI_PIPELINE_ID');
        $lockKey = $name . "_" . $CI_PIPELINE_ID;
        $redis->del($lockKey);
    }

    public static function connect($ForceNewConnection = false, $type = REDIS_PERSISTENCE)
    {
        $timeStart = microtime(true);
        if (!$ForceNewConnection) {
            if (self::$redis && $type === REDIS_PERSISTENCE) {
                return self::$redis;
            }
            if (self::$redis_mem_only && $type === REDIS_MEM_ONLY) {
                return self::$redis_mem_only;
            }
        }

        $redis_url = getenv('REDIS_HOST');

        $redis_port = getenv('REDIS_PORT') ?: 6379;
        if ($type === REDIS_MEM_ONLY) {
            $redis_port = getenv('REDIS_MEM_PORT') ?: 6378;
        }

        $redis_pwd = getenv('REDIS_PASSWORD');

        $step = 0;
        do {
            $step++;
            try {
                // Connect to Redis Database  
                if ($type === REDIS_PERSISTENCE) {
                    self::$redis = new Redis();
                    self::$redis->connect($redis_url, $redis_port);
                    self::$redis->auth($redis_pwd);
                } else {
                    self::$redis_mem_only = new Redis();
                    self::$redis_mem_only->connect($redis_url, $redis_port);
                    self::$redis_mem_only->auth($redis_pwd);
                }
                $timeEnd = microtime(true);
                $timeAll = $timeEnd - $timeStart;
                $timeAll = round($timeAll, 3);
                if ($timeAll > 2) {
                    logs::log("Redis connected in time (ok)" . $timeAll);
                }
                if ($type === REDIS_PERSISTENCE) {
                    return self::$redis;
                }
                return self::$redis_mem_only;
            } catch (RedisException $ex) {
                logs::error("Can not connect to redis(1) host=$redis_url port=$redis_port pw=$redis_pwd $ex", ["ex" => $ex]);
            } catch (Exception $ex) {
                logs::error("Can not connect to redis(2) host=$redis_url port=$redis_port pw=$redis_pwd $ex", ["ex" => $ex]);
            }
            $timeEnd = microtime(true);
            $timeAll = $timeEnd - $timeStart;
            $timeAll = round($timeAll, 3);
            logs::error("Redis connected in time (err)" . $timeAll);
            sleep(1);
        } while ($step < 120);

        $timeEnd = microtime(true);
        $timeAll = $timeEnd - $timeStart;
        $timeAll = round($timeAll, 3);
        logs::error("Redis connected in time (err)" . $timeAll);
        throw new RedisException("Can not connect");
    }
}
