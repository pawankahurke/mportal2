<?php




include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();

function DB_Connect()
{
    global $db_host;
    global $db_user;
    global $db_password;
    try {
        $dsn = 'mysql:host=' . $db_host;
        $db = new PDO($dsn, $db_user, $db_password);

        if (!$db) {
            logs::log(__FILE__, __LINE__, 'problem making mysql pdo', 0);
        }

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        return $db;
    } catch (PDOException $e) {
        logs::log(__FILE__, __LINE__, 'database connection failed: ' . $e->getMessage(), 0);
    }
}

function DB_PrepareStatement($db, $sql)
{
    try {
        if ($db instanceof PDO) {
            $stmt = $db->prepare($sql);
        } else {
            $stmt = false;
        }
        return $stmt;
    } catch (PDOException $e) {
        logs::log(__FILE__, __LINE__, 'database connection failed: ' . $e->getMessage(), 0);
    }
}

function DB_ExecuteQuery($stmt, $i)
{
    try {

        $stmt->execute();
        if ($i == 1 || $i == '1') {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $res = db_create_array($result);
        return $res;
    } catch (PDOException $e) {
        logs::log(__FILE__, __LINE__, 'database connection failed: ' . $e->getMessage(), 0);
    }
}

function DB_ExecuteInsert($db, $stmt, $data_array)
{
    try {
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt->execute($data_array);
        $res = $db->lastInsertId();
        return $res;
    } catch (PDOException $e) {
        logs::log(__FILE__, __LINE__, 'database connection failed: ' . $e->getMessage(), 0);
    }
}

function DB_FindMany($sql, $db)
{
    try {
        $stmt = DB_PrepareStatement($db, $sql);
        if ($stmt != false) {
            return $res = DB_ExecuteQuery($stmt, 0);
        } else {
            return $res = [];
        }
    } catch (PDOException $e) {
        logs::log(__FILE__, __LINE__, 'database connection failed: ' . $e->getMessage(), 0);
    }
}

function DB_FindOne($sql, $db)
{
    try {
        $stmt = DB_PrepareStatement($db, $sql);
        if ($stmt != false) {
            return $res = DB_ExecuteQuery($stmt, 1);
        } else {
            return $res = [];
        }
    } catch (PDOException $e) {
        logs::log(__FILE__, __LINE__, 'database connection failed: ' . $e->getMessage(), 0);
    }
}

function DB_InsertRow($db, $sql, $data_array)
{
    try {
        $stmt = DB_PrepareStatement($db, $sql);
        if ($stmt != false) {
            return $res = DB_ExecuteInsert($db, $stmt, $data_array);
        } else {
            return $res = [];
        }
    } catch (PDOException $e) {
        logs::log(__FILE__, __LINE__, 'database connection failed: ' . $e->getMessage(), 0);
    }
}

function DB_CreateArray($res_array)
{
    try {
        if (safe_count($res_array) > 0) {
            $array = $res_array;
        } else {
            $array = [];
        }
        return $array;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        logs::log(__FILE__, __LINE__, $e->getMessage());
    }
}

function DB_GetLastInsertId($stmt)
{
    try {
        $id = $stmt->lastInsertId();
        return $id;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        logs::log(__FILE__, __LINE__, $e->getMessage());
    }
}
