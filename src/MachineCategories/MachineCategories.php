<?php

class MachineCategories
{
    public static   $catIdByName = [
        "All" => 1,
        'User' => 2,
        'Site' =>  3,
        'Local' =>  4,
        'OS' => 5,
        'OS_Site' => 6,
        'OS_Lang' => 7,
        'OS_Lang_Site' => 8,
        'Machine' => 9,
        'Wiz_SCOP_MC' =>  10,
        'OS_DSPLY_Lang' => 11,
        'OS_DSPLY_Lang_Site' =>  12,
    ];
    public static function getCatIdByName($name)
    {
        return self::$catIdByName[$name];
    }
}
