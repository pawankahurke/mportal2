<?php




     global $alg;
     global $hash_123;
     global $data;
    
    function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
    
   function encode($header, $payload, $key) {
        global $data;
        $data = base64url_encode($header) . '.' . base64url_encode($payload);
        return $data.'.'.JWS($header, $key);
    }
    
   function decode($token, $key) {
        global $data;
        list($header, $payload, $signature) = explode('.', $token);
        $data = $header . '.' . $payload;
        if ($signature == JWS(base64url_decode($header), $key)) {
            return base64url_decode($payload);
        }
        exit('Invalid Signature');
    }
    
    function setAlgorithm($algorithm) {
        global $hash_123;
        global $alg;
        switch ($algorithm[0]) {
            case n:
                $alg = 'plaintext';
                break;
            case H:
                $alg = 'HMAC';
                break;
                        
            default: exit("RSA and ECDSA not implemented yet!");
        }
        switch ($algorithm[2]) {
            case a:
                $alg = 'plaintext';
                break;
            case 2:
                $hash = 'sha256';
                break;
            case 3:
                $hash = 'sha384';
                break;
            case 5:
                $hash = 'sha512';
                break;
        }
        if (in_array($hash, hash_algos())) $hash_123 = $hash;
    }

   function JWS($header, $key) {
        global $data;
        global $hash_123;
        global $alg;
        $json = safe_json_decode($header);
        setAlgorithm($json->alg);
        if ($alg == 'plaintext') {
            return '';
        }
        return base64url_encode(hash_hmac($hash_123, $data, $key, true));
    }


?>