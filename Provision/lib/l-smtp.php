<?php
//error_reporting(-1);
//ini_set('display_errors', 'On');

include_once 'class.phpmailer.php';


if (url::issetInRequest('function')) { // roles: resendmail
    nhRole::dieIfnoRoles(['resendmail']); // roles: resendmail

    $function = url::requestToAny('function'); // roles: resendmail
    $function();
}

function SMTP_sendMail($db, $email, $subject, $messagetext, $headers, $mime_head)
{

    nhRole::dieIfnoRoles(['resendmail']); // roles: resendmail
    $sql = "select * from " . $GLOBALS['PREFIX'] . "install.mailConfig";
    $res = redcommand($sql, $db);

    if ($res) {
        if (mysqli_num_rows($res)) {
            $sqlres = mysqli_fetch_assoc($res);
        }
    }

    //$name = $sqlres['name'];
    $host = $sqlres['host'];
    $port = $sqlres['port'];
    $username = $sqlres['username'];
    $pwd = $sqlres['password'];
    $from = 'support@nanoheal.com'; //$sqlres['fromEmail'];

    $mail = new PHPMailer;
    $mail->isSMTP();

    $mail->SMTPDebug = 1;
    $mail->Host = $host;
    $mail->Port = $port;
    $mail->SMTPAuth = true;
    $mail->Username = $username;
    $mail->Password = $pwd;

    $mail->setFrom($from, 'Support');
    $mail->addReplyTo($from, 'No Reply');
    $mail->addAddress($email, $email);
    $mail->Subject = $subject;
    //$mail->Body = $messagetext;
    $mail->msgHTML($messagetext);
    $mail->isHTML(true);
    $mail->AltBody = 'Download link mail';
    //$mail->addAttachment('images/phpmailer_mini.png');

    if (!$mail->send()) {
        //echo 'Failed: ' . $mail->ErrorInfo;
        $retVal = 0;
    } else {
        //echo 'success';
        $retVal = 1;
    }
    return $retVal;
}
