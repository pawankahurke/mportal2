<?php

if (!function_exists('pdo_connect')) {
    function pdo_connect($dbname = null)
    {
        return NanoDB::connect($dbname);
    }
}
