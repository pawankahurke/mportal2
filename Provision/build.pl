#!/usr/bin/perl -w

$year = (localtime)[5];

$root = 'reports';
$mode = 0777;

$path = $root;

mkdir $path, $mode;
for ($i = 0; $i < 5; $i++)
{
    $y = ($year + $i) % 100;
    $path = sprintf("%s/%02d",$root,$y);
    mkdir $path, $mode;
    for ($j = 0; $j <= 366; $j++)
    {
        $path = sprintf("%s/%02d/%03d",$root,$y,$j);
        mkdir $path, $mode;
    }
}

$euid = $>;
if ($euid == 0)
{
    `chown -R apache.apache reports`;
}
