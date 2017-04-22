<?php
use ghopper\CurlOverTor;
use ghopper\CurlOverTorException;

require_once('../vendor/autoload.php');

$proxy = [
    ['port' => 9050, 'cport' => 9051],
    ['port' => 9060, 'cport' => 9061],
    ['port' => 9070, 'cport' => 9071],
    ['port' => 9080, 'cport' => 9081]
];
try {
    $curtor = new CurlOverTor('127.0.0.1', $proxy, '1234');
    $curtor->setopt(CURLOPT_HEADER, 0);
    $curtor->setopt(CURLOPT_RETURNTRANSFER, TRUE);
    $curtor->setopt(CURLOPT_VERBOSE, 0);
    $curtor->setopt(CURLOPT_TIMEOUT, 10);
} catch (CurlOverTorException $ex) {
    echo $ex->getMessage();
    exit;
}

$curtor->setopt(CURLOPT_URL, 'http://pkk5.rosreestr.ru/api/features/1?text=50:11:50602&tolerance=64&skip=480&sqo=50:11:50602&sqot=2');
for ($i=0;$i<100;$i++) {
    $curtor->exec();
}

?>
