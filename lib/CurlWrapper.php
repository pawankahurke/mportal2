<?php

class CurlWrapper
{
    
    protected $ch = null;
    
    protected $cookieFile = '';
    
    protected $cookies = array();
    
    protected $headers = array();
    
    protected $options = array();
    
    protected static $predefinedUserAgents = array(
                'ie'       => 'Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.1; WOW64; Trident/6.0)',
                'firefox'  => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:29.0) Gecko/20120101 Firefox/29.0',
                'opera'    => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.46 Safari/537.36 OPR/20.0.1387.24 (Edition Next)',
                'chrome'   => 'Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1667.0 Safari/537.36',
                'bot'      => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
    );
    
    protected $requestParams = array();
    
    protected $response = '';
    
    protected $transferInfo = array();

    
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new CurlWrapperException('cURL extension is not loaded.');
        }

        $this->ch = curl_init();

        if (!$this->ch) {
            throw new CurlWrapperCurlException($this->ch);
        }

        $this->setDefaults();
    }

    
    public function __destruct()
    {
        if (is_resource($this->ch)) {
            curl_close($this->ch);
        }

        $this->ch = null;
    }

    
    public function addCookie($name, $value = null)
    {
        if (is_array($name)) {
            $this->cookies = $name + $this->cookies;
        } else {
            $this->cookies[$name] = $value;
        }
    }

    
    public function addHeader($header, $value = null)
    {
        if (is_array($header)) {
            $this->headers = $header + $this->headers;
        } else {
            $this->headers[$header] = $value;
        }
    }

    
    public function addOption($option, $value = null)
    {
        if (is_array($option)) {
            $this->options = $option + $this->options;
        } else {
            $this->options[$option] = $value;
        }
    }

    
    public function addRequestParam($name, $value = null)
    {
        if (is_array($name)) {
            $this->requestParams = $name + $this->requestParams;
        } elseif (is_string($name) && $value === null) {
            parse_str($name, $params);
            if (!empty($params)) {
                $this->requestParams = $params + $this->requestParams;
            }
        } else {
            $this->requestParams[$name] = $value;
        }
    }

    
    public function clearCookieFile()
    {
        if (!is_writable($this->cookieFile)) {
            throw new CurlWrapperException('Cookie file "'.($this->cookieFile).'" is not writable or does\'n exists!');
        }

        file_put_contents($this->cookieFile, '', LOCK_EX);
    }

    
    public function clearCookies()
    {
        $this->cookies = array();
    }

    
    public function clearHeaders()
    {
        $this->headers = array();
    }

    
    public function clearOptions()
    {
        $this->options = array();
    }

    
    public function clearRequestParams()
    {
        $this->requestParams = array();
    }

    
    public function delete($url, $requestParams = null)
    {
        return $this->request($url, 'DELETE', $requestParams);
    }

    
    public function get($url, $requestParams = null)
    {
        return $this->request($url, 'GET', $requestParams);
    }

    
    public function getResponse()
    {
        return $this->response;
    }

    
    public function getTransferInfo($key = null)
    {
        if (empty($this->transferInfo)) {
            throw new CurlWrapperException('There is no transfer info. Did you do the request?');
        }

        if ($key === null) {
            return $this->transferInfo;
        }

        if (isset($this->transferInfo[$key])) {
            return $this->transferInfo[$key];
        }

        throw new CurlWrapperException('There is no such key: '.$key);
    }

    
    public function head($url, $requestParams = null)
    {
        return $this->request($url, 'HEAD', $requestParams);
    }

    
    public function post($url, $requestParams = null)
    {
        return $this->request($url, 'POST', $requestParams);
    }

    public function patch($url, $requestParams = null)
    {
        return $this->request($url, 'POST', $requestParams);
    }
    
    public function put($url, $requestParams = null)
    {
        return $this->request($url, 'PUT', $requestParams);
    }

    
    public function rawPost($url, $data)
    {
        $this->prepareRawPayload($data);

        return $this->request($url, 'RAW_POST');
    }

    
    public function rawPut($url, $data)
    {
        $this->prepareRawPayload($data);

        return $this->request($url, 'PUT');
    }

    public function rawPatch($url)
    {
        
        return $this->request($url, 'PATCH');
    }

    public function rawPatchPost($url, $data)
    {
        $this->prepareRawPayload($data);

        return $this->request($url, 'PATCH');
    }

    

    
    public function removeCookie($name)
    {
        if (isset($this->cookies[$name])) {
            unset($this->cookies[$name]);
        }
    }

    
    public function removeHeader($header)
    {
        if (isset($this->headers[$header])) {
            unset($this->headers[$header]);
        }
    }

    
    public function removeOption($option)
    {
        if (isset($this->options[$option])) {
            unset($this->options[$option]);
        }
    }

    
    public function removeRequestParam($name)
    {
        if (isset($this->requestParams[$name])) {
            unset($this->requestParams[$name]);
        }
    }

    
    public function request($url, $method = 'GET', $requestParams = null)
    {
        
        $this->setURL($url);
        $this->setRequestMethod($method);

        if (!empty($requestParams)) {
            $this->addRequestParam($requestParams);
        }

        $this->initOptions();
        $this->response = curl_exec($this->ch);

        if ($this->response === false) {
            throw new CurlWrapperCurlException($this->ch);
        }

        $this->transferInfo = curl_getinfo($this->ch);

        return $this->response;
    }

    
    public function reset()
    {
        $this->__destruct();
        $this->transferInfo = array();
        $this->__construct();
    }

    
    public function resetAll()
    {
        $this->clearHeaders();
        $this->clearOptions();
        $this->clearRequestParams();
        $this->clearCookies();
        $this->clearCookieFile();
        $this->reset();
    }

    
    public function setConnectTimeOut($seconds)
    {
        $this->addOption(CURLOPT_CONNECTTIMEOUT, $seconds);
    }

    
    public function setCookieFile($filename)
    {
        if (!is_writable($filename)) {
            throw new CurlWrapperException('Cookie file "'.$filename.'" is not writable or does\'n exists!');
        }

        $this->cookieFile = $filename;
    }

    
    public function setDefaultHeaders()
    {
        $this->headers = array(
            'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Charset'  => 'utf-8;q=0.7,*;q=0.7',
            'Accept-Language' => 'en-US,en;q=0.8',
            'Accept-Encoding' => 'gzip,deflate',
            'Keep-Alive'      => '300',
            'Connection'      => 'keep-alive',
            'Cache-Control'   => 'max-age=0',
            'Pragma'          => ''
        );
    }

    
    public function setDefaultOptions()
    {
        $this->options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_ENCODING       => 'gzip,deflate',
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT        => 30,
        );
    }

    
    public function setDefaults($userAgent = null)
    {
        $this->setDefaultHeaders();
        $this->setDefaultOptions();

        if (!empty($userAgent)) {
            $this->setUserAgent($userAgent);
        } else {
            $this->setUserAgent('chrome');
        }
    }

    
    public function setFollowRedirects($value)
    {
        $this->addOption(CURLOPT_FOLLOWLOCATION, $value);
    }

    
    public function setReferer($referer)
    {
        $this->addOption(CURLOPT_REFERER, $referer);
    }

    
    public function setTimeout($seconds)
    {
        $this->addOption(CURLOPT_TIMEOUT, $seconds);
    }

    
    public function setUserAgent($userAgent)
    {
        if (isset(self::$predefinedUserAgents[$userAgent])) {
            $this->addOption(CURLOPT_USERAGENT, self::$predefinedUserAgents[$userAgent]);
        } else {
            $this->addOption(CURLOPT_USERAGENT, $userAgent);
        }
    }

    
    public function setAuthType($type = CURLAUTH_BASIC) {
        $this->addOption(CURLOPT_HTTPAUTH, $type);
    }

    
    public function setAuthCredentials($username, $password) {
        $this->addOption(CURLOPT_USERPWD, "$username:$password");
    }

    
    public function unsetCookieFile()
    {
        $this->cookieFile = '';
    }

    
    protected function buildUrl($parsedUrl)
    {
        return (isset($parsedUrl['scheme'])   ?     $parsedUrl["scheme"].'://' : '').
               (isset($parsedUrl['user'])     ?     $parsedUrl["user"].':'     : '').
               (isset($parsedUrl['pass'])     ?     $parsedUrl["pass"].'@'     : '').
               (isset($parsedUrl['host'])     ?     $parsedUrl["host"]         : '').
               (isset($parsedUrl['port'])     ? ':'.$parsedUrl["port"]         : '').
               (isset($parsedUrl['path'])     ?     $parsedUrl["path"]         : '').
               (isset($parsedUrl['query'])    ? '?'.$parsedUrl["query"]        : '').
               (isset($parsedUrl['fragment']) ? '#'.$parsedUrl["fragment"]     : '');
    }

    
    protected function initOptions()
    {
        if (!empty($this->requestParams)) {
            if (isset($this->options[CURLOPT_HTTPGET])) {
                $this->prepareGetParams();
            } else {
                $this->addOption(CURLOPT_POSTFIELDS, http_build_query($this->requestParams));
            }
        }

        if (!empty($this->headers)) {
            $this->addOption(CURLOPT_HTTPHEADER, $this->prepareHeaders());
        }

        if (!empty($this->cookieFile)) {
            $this->addOption(CURLOPT_COOKIEFILE, $this->cookieFile);
            $this->addOption(CURLOPT_COOKIEJAR, $this->cookieFile);
        }

        if (!empty($this->cookies)) {
            $this->addOption(CURLOPT_COOKIE, $this->prepareCookies());
        }

        if (!curl_setopt_array($this->ch, $this->options)) {
            throw new CurlWrapperCurlException($this->ch);
        }
    }

    
    protected function prepareCookies()
    {
        $cookiesString = '';

        foreach ($this->cookies as $cookie => $value) {
            $cookiesString .= $cookie.'='.$value.'; ';
        }

        return $cookiesString;
    }

    
    protected function prepareGetParams()
    {
        $parsedUrl = parse_url($this->options[CURLOPT_URL]);
        $query = http_build_query($this->requestParams);

        if (isset($parsedUrl['query'])) {
            $parsedUrl['query'] .= '&'.$query;
        } else {
            $parsedUrl['query'] = $query;
        }

        $this->setUrl($this->buildUrl($parsedUrl));
    }

    
    protected function prepareRawPayload($data)
    {
        $this->clearRequestParams();
        $this->addHeader('Content-Length', strlen($data));
        $this->addOption(CURLOPT_POSTFIELDS, $data);
    }

    
    protected function prepareHeaders()
    {
        $headers = array();

        foreach ($this->headers as $header => $value) {
            $headers[] = $header.': '.$value;
        }

        return $headers;
    }

    
    protected function setRequestMethod($method)
    {
                $this->removeOption(CURLOPT_NOBODY);
        $this->removeOption(CURLOPT_HTTPGET);
        $this->removeOption(CURLOPT_POST);
        $this->removeOption(CURLOPT_CUSTOMREQUEST);

        switch (strtoupper($method)) {
            case 'HEAD':
                $this->addOption(CURLOPT_NOBODY, true);
            break;

            case 'GET':
                $this->addOption(CURLOPT_HTTPGET, true);
            break;

            case 'POST':
                $this->addOption(CURLOPT_POST, true);
            break;

            case 'RAW_POST':
                $this->addOption(CURLOPT_CUSTOMREQUEST, 'POST');
            break;

            default:
                $this->addOption(CURLOPT_CUSTOMREQUEST, $method);
        }
    }

    
    protected function setUrl($url)
    {
        $this->addOption(CURLOPT_URL, $url);
    }
}


class CurlWrapperException extends Exception
{
    
    public function __construct($message)
    {
        $this->message = $message;
    }
}


class CurlWrapperCurlException extends CurlWrapperException
{
    
    public function __construct($curlHandler)
    {
        $this->message = curl_error($curlHandler);
        $this->code = curl_errno($curlHandler);
    }
}