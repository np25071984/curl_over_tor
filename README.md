# CurlOverTor
Wrapper for CURL, which uses TOR server as socks5 proxy. With this software architecture we can send plenty requests to a host and avoid blocking.

## Configuring
All you need is proxy host, proxy ports and proxy control port password.
```
$proxy = [
    ['port' => 9050, 'cport' => 9051],
    ['port' => 9060, 'cport' => 9061],
    ['port' => 9070, 'cport' => 9071],
    ['port' => 9080, 'cport' => 9081]
];
$curtor = new CurlOverTor('127.0.0.1', $proxy, '1234');
```
I recommend work with at least 3 proxy ports for better performance. With only one port we spend much time for connection setup while with many ports we just switch current connection to another one, already prepared for data transfer.

Look at the *examples* folder for more information.

## Available methods
 * setAuthCode($code);
 * setMaxQueryCount($count=10);
 * setopt($opt,$val);
 * exec($url=NULL);

## Requirements
 * TOR server
 * php_curl extension

