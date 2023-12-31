<?php






class POP3 {

  public $POP3_PORT = 110;


  public $POP3_TIMEOUT = 30;


  public $CRLF = "\r\n";


  public $do_debug = 2;


  public $host;


  public $port;


  public $tval;


  public $username;


  public $password;


  private $pop_conn;
  private $connected;
  private $error;

  public function __construct() {
    $this->pop_conn  = 0;
    $this->connected = false;
    $this->error     = null;
  }


  public function Authorise ($host, $port, $tval, $username, $password, $debug_level = 0) {
    $this->host = $host;

        if ($port == false) {
      $this->port = $this->POP3_PORT;
    } else {
      $this->port = $port;
    }

        if ($tval == false) {
      $this->tval = $this->POP3_TIMEOUT;
    } else {
      $this->tval = $tval;
    }

    $this->do_debug = $debug_level;
    $this->username = $username;
    $this->password = $password;

        $this->error = null;

        $result = $this->Connect($this->host, $this->port, $this->tval);

    if ($result) {
      $login_result = $this->Login($this->username, $this->password);

      if ($login_result) {
        $this->Disconnect();

        return true;
      }

    }

        $this->Disconnect();

    return false;
  }


  public function Connect ($host, $port = false, $tval = 30) {
        if ($this->connected) {
      return true;
    }



    set_error_handler(array(&$this, 'catchWarning'));

        $this->pop_conn = fsockopen($host,                      $port,                      $errno,                     $errstr,                    $tval);
        restore_error_handler();

        if ($this->error && $this->do_debug >= 1) {
      $this->displayErrors();
    }

        if ($this->pop_conn == false) {
            $this->error = array(
        'error' => "Failed to connect to server $host on port $port",
        'errno' => $errno,
        'errstr' => $errstr
      );

      if ($this->do_debug >= 1) {
        $this->displayErrors();
      }

      return false;
    }


        if (version_compare(phpversion(), '5.0.0', 'ge')) {
      stream_set_timeout($this->pop_conn, $tval, 0);
    } else {
            if (substr(PHP_OS, 0, 3) !== 'WIN') {
        socket_set_timeout($this->pop_conn, $tval, 0);
      }
    }

        $pop3_response = $this->getResponse();

        if ($this->checkResponse($pop3_response)) {
        $this->connected = true;
      return true;
    }

  }


  public function Login ($username = '', $password = '') {
    if ($this->connected == false) {
      $this->error = 'Not connected to POP3 server';

      if ($this->do_debug >= 1) {
        $this->displayErrors();
      }
    }

    if (empty($username)) {
      $username = $this->username;
    }

    if (empty($password)) {
      $password = $this->password;
    }

    $pop_username = "USER $username" . $this->CRLF;
    $pop_password = "PASS $password" . $this->CRLF;

        $this->sendString($pop_username);
    $pop3_response = $this->getResponse();

    if ($this->checkResponse($pop3_response)) {
            $this->sendString($pop_password);
      $pop3_response = $this->getResponse();

      if ($this->checkResponse($pop3_response)) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }


  public function Disconnect () {
    $this->sendString('QUIT');

    fclose($this->pop_conn);
  }



  private function getResponse ($size = 128) {
    $pop3_response = fgets($this->pop_conn, $size);

    return $pop3_response;
  }


  private function sendString ($string) {
    $bytes_sent = fwrite($this->pop_conn, $string, strlen($string));

    return $bytes_sent;
  }


  private function checkResponse ($string) {
    if (substr($string, 0, 3) !== '+OK') {
      $this->error = array(
        'error' => "Server reported an error: $string",
        'errno' => 0,
        'errstr' => ''
      );

      if ($this->do_debug >= 1) {
        $this->displayErrors();
      }

      return false;
    } else {
      return true;
    }

  }


  private function displayErrors () {
    echo '<pre>';

    foreach ($this->error as $single_error) {
      print_r($single_error);
    }

    echo '</pre>';
  }


  private function catchWarning ($errno, $errstr, $errfile, $errline) {
    $this->error[] = array(
      'error' => "Connecting to the POP3 server raised a PHP warning: ",
      'errno' => $errno,
      'errstr' => $errstr
    );
  }

  }
?>
