<?php
require __DIR__ . '/vendor/autoload.php';

$client = new \GuzzleHttp\Client([
    'timeout' => 10,
    'verify' => true,
]);

$url = 'https://ayd.pinktreehealth.com/api/listDoctorAccountsById/694e75e614bde01415e9bc86';
try {
    $resp = $client->request('GET', $url);
    echo "HTTP/" . $resp->getProtocolVersion() . " " . $resp->getStatusCode() . "\n";
    echo $resp->getBody();
} catch (\GuzzleHttp\Exception\RequestException $e) {
    if ($e->hasResponse()) {
        $r = $e->getResponse();
        echo "ERROR STATUS: " . $r->getStatusCode() . "\n";
        echo $r->getBody();
    } else {
        echo "EXCEPTION: " . $e->getMessage();
    }
}
