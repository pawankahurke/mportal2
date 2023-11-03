<?php

header('Access-Control-Allow-Origin: *');

header("Access-Control-Allow-Headers: *");

try {
    $host = getenv('DB_HOST') ?: "mysql-svc";
    $dsn = "mysql:host=$host;port=3306;dbname=analytics";
    $user = getenv('DB_USERNAME') ?: "weblog";
    $passwd = getenv('DB_PASSWORD') ?: "b6Q4qT17xyfYJS9CJP2019#";

    $pdo = new PDO($dsn, $user, $passwd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stm = $pdo->query("SELECT VERSION()");

    $version = $stm->fetch();
} catch (Exception $e) {
    logs::log(__FILE__, __LINE__, $e, 0);
    echo "There was an error connecting to the database";
}
