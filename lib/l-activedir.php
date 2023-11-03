<?php





include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../include/common_functions.php';
include_once '../lib/l-dbConnect.php';



nhRole::dieIfnoRoles(['configureswdupload']);

//Replace $routes['post'] with if else
if (url::postToText('function') === 'saveConfiguredQuestionDetails') { //roles: configureswdupload
    saveConfiguredQuestionDetails();
}

//Replace $routes['get'] with if else
if (url::postToText('function') === 'getConfiguredDetails') { //roles: configureswdupload
    getConfiguredDetails();
}




function getConfiguredDetails()
{
    $pdo = pdo_connect();

    $username = $_SESSION['user']['username'];

    $stmt = $pdo->prepare("select domain, questions from " . $GLOBALS['PREFIX'] . "node.activeConfigQuestions "
        . "where username = ? order by id desc limit 1");
    $stmt->execute([$username]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    $impersonateUser = getImpersonationDetails();

    if ($data) {
        $rdata = ['status' => 'success', 'rdata' => $data, 'impersonateuser' => $impersonateUser];
    } else {
        $rdata = ['status' => 'failed', 'rdata' => 'No data found!', 'impersonateuser' => $impersonateUser];
    }
    echo json_encode($rdata);
}

function saveConfiguredQuestionDetails()
{
    $pdo = pdo_connect();

    $username = $_SESSION['user']['username'];

    $domain = url::postToAny('domain');
    $ques1 = url::postToAny('q1');
    $ques2 = url::postToAny('q2');
    $ques3 = url::postToAny('q3');
    $ques4 = url::postToAny('q4');
    $ques5 = url::postToAny('q5');

    $questions = "$ques1###$ques2###$ques3###$ques4###$ques5";

    $stmt = $pdo->prepare("select domain, questions from " . $GLOBALS['PREFIX'] . "node.activeConfigQuestions "
        . "where username = ? order by id desc limit 1");
    $stmt->execute([$username]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        $stmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "node.activeConfigQuestions set domain = ?, questions = ? where username = ?");
        $resdata = $stmt->execute([$domain, $questions, $username]);
    } else {
        $stmt = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "node.activeConfigQuestions (username, domain, questions) values (?,?,?)");
        $stmt->execute([$username, $domain, $questions]);

        $resdata = $pdo->lastInsertId();
    }

    if ($resdata) {
        echo 'success';
    } else {
        echo 'failed';
    }
}

function getImpersonationDetails()
{
    $pdo = pdo_connect();

    $parentSiteName = $_SESSION['rparentName'];
    $machinename = $_SESSION['searchValue'];

    $varstmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Variables where scop = ? and name = ?");
    $varstmt->execute([43, 'S00043ImpersonateUser']);
    $vardata = $varstmt->fetch(PDO::FETCH_ASSOC);
    $varuniq = $vardata['varuniq'];

    $machgrpvalue = $parentSiteName . ':' . $machinename;
    $mgstmt = $pdo->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = ? or name = ? limit 1");
    $mgstmt->execute([$machgrpvalue, $parentSiteName]);
    $mgdata = $mgstmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($mgdata as $value) {
        $mgroupuniqlist[] = $value['mgroupuniq'];
    }
    $mguniq_in = str_repeat('?,', safe_count($mgroupuniqlist) - 1) . '?';

    $vvalstmt = $pdo->prepare("select valu from " . $GLOBALS['PREFIX'] . "core.VarValues where mgroupuniq IN ($mguniq_in) and varuniq = ?");
    $params = array_merge($mgroupuniqlist, [$varuniq]);
    $vvalstmt->execute($params);
    $vvaldata = $vvalstmt->fetch(PDO::FETCH_ASSOC);

    if ($vvaldata) {
        $impersonateuser = $vvaldata['valu'];
    } else {
        $impersonateuser = '';
    }

    return $impersonateuser;
}
