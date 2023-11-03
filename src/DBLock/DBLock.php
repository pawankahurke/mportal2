<?php

class DBLock
{
    private $isLocked;
    private $name;

    public function __construct($name = 'reportDBLock')
    {
        $maxTime = 100;
        $this->name = $name;

        $row = NanoDB::find_one("SELECT GET_LOCK('" . $this->name . "'," . (int) $maxTime . ") as result");
        if ((int) $row['result'] !== 1) {
            logs::log("already running (DBLock=$name)", $row);
            die("already running (DBLock=$name)!" . PHP_EOL);
        }
        $this->isLocked = true;
        return true;
    }

    public function free()
    {
        if (!$this->isLocked) {
            return;
        }
        NanoDB::query("SELECT RELEASE_LOCK('" . $this->name . "')");
        $this->isLocked = false;
    }

    public function __destruct()
    {
        $this->free();
    }
}
