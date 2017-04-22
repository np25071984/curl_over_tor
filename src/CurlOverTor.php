<?php
namespace ghopper;
use ghopper\CurlOverTorException;

interface ICurlOverTor {
    /*
     * Set control port password
     */
    function setAuthCode($code);

    /*
     * Set maximum amount of queries per ip
     */
    function setMaxQueryCount($count=10);

    /*
     * Wrapper for curl_setopt function
     */
    function setopt($opt,$val);

    /*
     * Wrapper for curl_exec function
     */
    function exec($url=NULL);
}

class CurlOverTor implements ICurlOverTor
{
    private $ch;
    private $curPort;
    private $proxy;
    private $aPort;
    private $authCode;
    private $queryMax;
    
    private $queryCount;

    const DEFAULT_MAX_QUERY_COUNT = 5;

    function __construct($proxy='127.0.0.1', array $aPort=array(), $authCode='') {
        if (count($aPort) == 0)
            throw new CurlOverTorException('You have to specify at least one port!');

        $this->queryCount = 0;
        $this->setMaxQueryCount(self::DEFAULT_MAX_QUERY_COUNT);

        $this->ch = curl_init();

        $this->proxy = $proxy;
        $this->aPort = $aPort;
        $this->authCode = $authCode;
        $this->curPort = rand(0, count($this->aPort)-1);
        $s = "{$this->proxy}:{$this->aPort[$this->curPort]['port']}";
        $this->setopt(CURLOPT_PROXY, $s);
        $this->setopt(CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    }

    function setAuthCode($code) {
        echo 'Setup auth code: ',$code,PHP_EOL;
        $this->authCode = $code;
    }

    function setMaxQueryCount($count=10) {
        if ($count < 0)
            throw new CurlOverTorException('Max query count value have to be positive integer');
 
        $this->queryMax = $count;
    }

    function setopt($opt,$val) {
        if (!$this->ch)
            throw new CurlOverTorException('Curl doesn\'t initialized yet');

        curl_setopt($this->ch, $opt, $val);
    }

    function exec($url=NULL) {
        if (!$this->ch)
            return FALSE;

        if ($this->queryCount == $this->queryMax)
            $this->newIdentity();
        else
            $this->queryCount++;
            
        if ($url)
            $this->setopt(CURLOPT_URL, $url);

        $response = curl_exec($this->ch);
        
        $itr = 0;
        do {
            if ($itr == 4) {
                break;
            } else 
                $itr++;

            if ($itr != 1) {
                $this->newIdentity();
            }
            $response = curl_exec($this->ch);
            
        } while (curl_errno($this->ch) !== 0);

        return $response;
    }

    private function newIdentity() {
        $this->queryCount = 0;

        $fp = fsockopen(
            $this->proxy, 
            $this->aPort[$this->curPort]['cport'],
            $errno, 
            $errstr, 
            30
        );
        if ($fp) {
            fputs($fp, "AUTHENTICATE \"{$this->authCode}\"\r\n");
            $response = fread($fp, 1024);

            list($code, $text) = explode(' ', $response, 2);
            if ($code != '250') {
                throw new CurlOverTorException('The auth code is incorrect');
            }

            fputs($fp, "signal NEWNYM\r\n");
            $response = fread($fp, 1024);
            list($code, $text) = explode(' ', $response, 2);
            if ($code != '250') {
                throw new CurlOverTorException('Signal failed');
            }

            fclose($fp);
        } else
            throw new CurlOverTorException('Can\'t connect to the control port');

        if ($this->curPort < count($this->aPort)-1)
            $this->curPort++;
        else
            $this->curPort = 0;

        $cport = $this->aPort[$this->curPort]['port'];

        $this->setopt(CURLOPT_PROXY, "{$this->proxy}:{$this->aPort[$this->curPort]['port']}");
    }

    function __destruct() {
        unset($this->ch);
    }
}

?>
