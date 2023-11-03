<?php
$cubeUrl = "'self' " . str_replace("visualization", "", getenv('VISUALISATION_SERVICE_DASH_URL'));
if (getenv('METABASE_SITE_URL') && getenv('METABASE_SECRET_KEY')) {
    $cubeUrl = "'self' " .  getenv('METABASE_SITE_URL');
}

$customCubeUrl = "";
if (getenv('ADD_CUSTOM_DASHBOARD_HOST')) {
    $customCubeUrl = 'https://' . getenv('ADD_CUSTOM_DASHBOARD_HOST');
}

$nodeUrl = getenv('OPTIONS_NODEURL');

$s3 = "https://" . getenv('STORAGE_S3_BUCKET_NAME') . ".s3.amazonaws.com/*";
if (getenv('STORAGE_TYPE') != 's3' || empty(getenv('STORAGE_S3_BUCKET_NAME'))) {
    $s3 = "";
}

$contentSecurityPolicy = "Content-Security-Policy: connect-src  'self' wss://" . $nodeUrl . "/hfnws; frame-src " . $cubeUrl . " " . $customCubeUrl . ";  img-src 'self' " . $s3;
$ReferrerPolicy = "Referrer-Policy: strict-origin-when-cross-origin";
$FrameOptions = "X-Frame-Options: DENY";
header($contentSecurityPolicy);
header($ReferrerPolicy);
header($FrameOptions);
header('X-Permitted-Cross-Domain-Policies: none');

/** generated here https://www.permissionspolicy.com/ */
header('Permissions-Policy: accelerometer=(), autoplay=(self), camera=(), fullscreen=(self), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), midi=(), payment=(), picture-in-picture=(), publickey-credentials-get=(), sync-xhr=*, usb=(), xr-spatial-tracking=(), clipboard-read=(self), clipboard-write=(self), gamepad=()');
