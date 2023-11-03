<?php

/*
Revision history:

Date        Who     What
----        ---     ----
18-Apr-21   BTE     Created.
*/

require __DIR__ . '/vendor/autoload.php';

define('SHARED_SECRET', '67f52e19-d29f-44cd-b5c0-7ecd5b85ad0a');
define('constServerCommandChannel', 'servercommand_');


/**
 * Generate connection JWT.  Copied from phpcent library.
 *
 * @param string $userId
 * @param int $exp
 * @param array $info
 * @param array $channels
 * @return string
 */
function generateConnectionToken($userId = '', $exp = 0, $info = array(), $channels = array())
{
    $header = array('typ' => 'JWT', 'alg' => 'HS256');
    $payload = array('sub' => (string) $userId);
    if (!empty($info)) {
        $payload['info'] = $info;
    }
    if (!empty($channels)) {
        $payload['channels'] = $channels;
    }
    if ($exp) {
        $payload['exp'] = $exp;
    }
    $segments = array();
    $segments[] = urlsafeB64Encode(json_encode($header));
    $segments[] = urlsafeB64Encode(json_encode($payload));
    $signing_input = implode('.', $segments);
    $signature = sign($signing_input, SHARED_SECRET);
    $segments[] = urlsafeB64Encode($signature);
    return implode('.', $segments);
}

function urlsafeB64Encode($input)
{
    return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
}

function sign($msg, $key)
{
    return hash_hmac('sha256', $msg, $key, true);
}

\Ratchet\Client\connect('ws://localhost:8000/connection/websocket')->then(function ($conn) {
    $conn->on('message', function ($msg) use ($conn) {
        echo "Received: {$msg}\n";
        $msg = safe_json_decode($msg);
        if ((property_exists($msg, 'id')) && ($msg->id == 1)) {
            $sub = '{"method":"subscribe","params":{"channel":"clientrpc"},"id":2}';
            $conn->send($sub);
        } else if ((property_exists($msg, 'id')) && ($msg->id == 2)) {
            //Ignore but for now just log it
            logs::log(__FILE__, __LINE__, 'PHP centrifugo subscriber received message on id: ' . $msg->id
                . ', [' . print_r($msg, 1) . ']', 0);
        } else if ((property_exists($msg, 'result')) && (property_exists($msg->result, 'channel'))) {
            logs::log(__FILE__, __LINE__, 'PHP centrifugo subscriber received message on channel: ' . $msg->result->channel
                . ', [' . $msg->result->data->data->str . ']', 0);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "http://localhost/main/rpc/rpc.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $msg->result->data->data->str);

            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            // Receive server response ...
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $server_output = curl_exec($ch);

            curl_close($ch);

            logs::log(__FILE__, __LINE__, "RPC response: " . $server_output, 0);

            $channel = constServerCommandChannel . md5($msg->result->data->info->user);
            $sub = '{"method":"publish","params":{"channel":"' . $channel . '", "data":"BASE64RPC_' . base64_encode($server_output) . '"},"id":2}';
            logs::log(__FILE__, __LINE__, "Sending publish: " . $sub, 0);
            $conn->send($sub);
        }
    });
    $conn->on('close', function ($code, $reason) use ($conn) {
        echo "Connection closed: {$code}, {$reason}\n";
        $conn->close();
    });
    $conn->on('ping', function ($frame) use ($conn) {
        echo "got ping\n";
    });
    $conn->on('pong', function ($frame) use ($conn) {
        echo "got pong\n";
    });
    $conn->on('error', function ($error) use ($conn) {
        echo "Error: {$error}\n";
    });

    $token = generateConnectionToken('1');
    $initial = '{"params":{"token":"' . $token . '"},"id":1}';
    $conn->send($initial);
}, function ($e) {
    echo "Could not connect: {$e->getMessage()}\n";
});
