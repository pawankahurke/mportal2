<?php

define('AppCache_always', 0);
define('AppCache_ifNotNull', 1);
class AppCache
{
    public static $CacheType_always = AppCache_always;
    public static $CacheType_ifNotNull = AppCache_ifNotNull;

    public static function ifNotExists($keyName, $keyArgs, $callFunc,  $ttl = 300, $onSkipCall = null, $mode = AppCache_always)
    {
        if (getenv('NO_AppCache') === 'true') {
            $callFunc();
            return false;
        }
        $rc = RedisLink::connect(false, RedisLink::$REDIS_MEM_ONLY);

        $key = "AC" . __FUNCTION__ . (getenv('CI_PIPELINE_ID') ?: '0') . "_" . $keyName . "_" . md5(json_encode($keyArgs));
        if ($rc->exists($key)) {
            logs::tag("Cache", __FUNCTION__, ["status" => 'cached', "key" => $key], 1);
            if ($onSkipCall) {
                $onSkipCall();
            }
            return true;
        }
        logs::tag("Cache", __FUNCTION__, ["status" => 'none', "key" => $key], 1);

        $res = $callFunc();

        if (($mode === AppCache_ifNotNull &&  !!$res) || $mode === AppCache_always) {
            $rc->set($key, json_encode($res),  $ttl);
        }

        return true;
    }
    public static function getValue($keyName, $keyArgs, $callFunc,  $ttl = 300, $onSkipCall = null, $mode = AppCache_always)
    {
        if (getenv('NO_AppCache') === 'true') {
            return $callFunc();
        }
        $rc = RedisLink::connect(false, RedisLink::$REDIS_MEM_ONLY);

        $key = "AC" . __FUNCTION__ . (getenv('CI_PIPELINE_ID') ?: '0') . "_" . $keyName . "_" . md5(json_encode($keyArgs));
        $value = $rc->get($key);

        if ($value) {
            $r = json_decode($value, true);
            logs::tag("Cache", __FUNCTION__, ["status" => 'cached', "key" => $key], 1);
            if ($onSkipCall) {
                $onSkipCall($r);
            }
            return $r;
        }

        logs::tag("Cache", __FUNCTION__, ["status" => 'none', "key" => $key], 1);
        $res = $callFunc();

        if (($mode === AppCache_ifNotNull &&  !!$res) || $mode === AppCache_always) {
            $rc->set($key, json_encode($res),  $ttl);
        }
        return $res;
    }
}
