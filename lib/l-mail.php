<?php


function send_mail($userEmail, $subject, $message, $fromEmail)
{
  // send from visualisationService
  $arrayPost = array(
    'from' => getenv('SMTP_USER_LOGIN'),
    'to' => $userEmail,
    'subject' => $subject,
    'text' => '',
    'html' => $message,
    'token' => getenv('APP_SECRET_KEY'),
  );
  $url = getenv('VISUALISATION_SERVICE_API_URL') . "/mailer/sendmassage";
  CURL::sendDataCurl($url, $arrayPost);

  return 1;
}
