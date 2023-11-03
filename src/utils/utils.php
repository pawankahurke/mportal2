<?php

function loadClass($class_name)
{
    $claspath = getClassPath($class_name);
    if ($claspath) {
        // logs::log(__FILE__, __LINE__, "loadClass:" . $claspath, 0);
        include_once $claspath;
        return true;
    }

    // logs::log(__FILE__, __LINE__, "[WARN] psr0 class not found: " . $class_name, 0);
    return false;
}

function getClassPath($class_name)
{

    $filePath = __DIR__ . "/../" . $class_name . "/" . $class_name . ".php";
    if (file_exists($filePath)) {
        return $filePath;
    }

    $fileName = __DIR__ . "/../" . $class_name . '.php';
    $fileName = str_replace('\\', '/', $fileName);
    if (file_exists($fileName)) {
        return $fileName;
    }

    $filePath = __DIR__ . "/../models/" . $class_name . ".php";
    if (file_exists($filePath)) {
        return $filePath;
    }
}

spl_autoload_register("loadClass");

function safe_count($v)
{
    if (is_array($v) || $v instanceof Countable) {
        return count($v);
    }

    if (is_string($v)) {
        logs::trace(1,  "Warn:safe_count with string:`" . substr($v, 0, 100) . "`");
        if (empty($v)) {
            return 0;
        }
        return 1;
    }

    if (is_object($v)) {
        logs::trace(1,  "Warn:safe_count with object:`" . substr(json_encode($v), 0, 100) . "`");
        return 1;
    }
    if ($v) {
        return 1;
    }
    return 0;
}

function safe_addslashes($v)
{
    if (strpos($v, '\\') !== false) {
        logs::trace(1, "call::safe_addslashes", substr(json_encode($v), 0, 100));
    }

    if (is_string($v)) {
        return  addslashes($v);
    }
    logs::trace(1, "Warn: addslashes with object:`" . substr(json_encode($v), 0, 100) . "`");
    return null;
}

function safe_array_keys($v)
{
    if (is_array($v) || $v instanceof Countable) {
        return array_keys($v);
    }
    logs::trace(1, "Warn:safe_array_keys with object:`" . substr(json_encode($v), 0, 100) . "`");
    return null;
}

function mysqli_result($result, $number, $field = 0)
{
    mysqli_data_seek($result, $number);
    $row = mysqli_fetch_array($result);
    return $row[$field];
}

function safe_json_decode($v, $opt = false, $d = 512, $f = 0)
{
    if ($v === '\"\"' || $v === '') {
        return "";
    }

    if (is_string($v)) {
        $v = preg_replace('#\[,([\\"{])#im', '[$1', $v);
        $v = preg_replace('#\\r#im', '', $v);
        $v = preg_replace('#\\n#im', '', $v);

        $res = call_user_func_array('json_decode', func_get_args());
        if ($res  === null) {

            $v = preg_replace('#\[,([\\"{])#im', '[$1', $v);
            $v = preg_replace('#\\r#im', '', $v);
            $v = preg_replace('#\\n#im', '', $v);

            $res = json_decode($v, $opt, $d, $f);
            if ($res  === null) {
                $v2 = preg_replace('#\\\"#im', '"', $v);
                $res = json_decode($v2, $opt, $d, $f);
                if ($res  === null) {
                    logs::trace(1, "Warn:safe_json_decode with invalid string:`" . $v . "`");
                } else {
                    logs::trace(1, "Warn:safe_json_decode save for:`" . $v . "`");
                }
            } else {
                logs::trace(1, "Warn:safe_json_decode save for:`" . $v . "`");
            }
        }
        return $res;
    }

    logs::trace(1, "Warn:safe_json_decode with not string:`" . substr(json_encode($v), 0, 100) . "`");
    return $v;
}

function nhSqli_query($l, $f, $mdb, $sql)
{
    logs::tag("SQL", ["sql" => $sql, "tag" => "sql"], null, 1);
    $res = mysqli_query($mdb, $sql);

    if (mysqli_errno($mdb)) {
        logs::log($l, $f, ["errno" => mysqli_errno($mdb), "error" => mysqli_error($mdb), "sql" => $sql, "tag" => "mysqli_error"]);
    }
    return $res;
}

function nhSqli_prepared_query($l, $f, $mdb, $sql, $types, $params)
{
    if ($stmt = mysqli_prepare($mdb, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, $types, $params);
        mysqli_stmt_execute($stmt);
    }

    if (mysqli_errno($mdb)) {
        logs::log($l, $f, ["errno" => mysqli_errno($mdb), "error" => mysqli_error($mdb), "sql" => $sql, "tag" => "mysqli_error"]);
    }

    // Close statement
    mysqli_stmt_close($stmt);
}

function safe_sizeof($v)
{
    if (is_array($v) || $v instanceof Countable) {
        return sizeof($v);
    }
    return 0;
}


// error handling function
function nhJsonLogErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting,
        // so let it be handled by PHP's standard error handler
        return false;
    }

    switch ($errno) {
        case E_USER_ERROR:
            logs::log($errfile, $errline, [
                "errno" => $errno,
                "error" => $errstr,
                "tag" => "E_USER_ERROR",
            ]);
            exit(1);
        case E_USER_WARNING:
            logs::log($errfile, $errline, [
                "errno" => $errno,
                "error" => $errstr,
                "tag" => "E_USER_WARNING",
            ]);
            break;

        case E_USER_NOTICE:
            logs::log($errfile, $errline, [
                "errno" => $errno,
                "error" => $errstr,
                "tag" => "E_USER_NOTICE",
            ]);
            break;

        default:
            logs::log($errfile, $errline, [
                "errno" => $errno,
                "error" => $errstr,
                "tag" => $errno,
            ]);
            break;
    }

    return true;
}

$old_error_handler = set_error_handler("nhJsonLogErrorHandler");


function fatal_handler()
{
    $errfile = "unknown file";
    $errstr  = "shutdown";
    $errno   = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if ($error !== NULL) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];
        logs::log($errfile, $errline, [
            "errno" => $errno,
            "error" => $errstr,
            "tag" => $errno,
        ]);
    }
}
register_shutdown_function("fatal_handler");

function PHP_REPF_UpdateDynamicList(...$d)
{
    logs::log("Warn: acceld the wrong function:" . __FUNCTION__, [$d]);
    return 2; // constAppNoErr
}

function PHP_DSYN_InvalidateRow(...$d)
{
    logs::log("Warn: acceld the wrong function:" . __FUNCTION__, [$d]);
    return 2; // constAppNoErr
}
