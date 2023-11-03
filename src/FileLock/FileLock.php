<?php

class FileLock
{
    private $fp;
    public function __construct()
    {
        $this->fp = fopen(__FILE__, 'r');
        if (!flock($this->fp, LOCK_EX | LOCK_NB)) {
            die('already running !' . PHP_EOL);
        }
    }
    public function __destruct()
    {
        flock($this->fp, LOCK_UN);
        fclose($this->fp);
    }
}
