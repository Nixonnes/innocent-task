<?php

// Примечание: Оба эндпоинта возвращают 404, предполагаю, что цель — показать обработку запросов и ошибок

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

require 'vendor/autoload.php';

$client = new \GuzzleHttp\Client(
    ['timeout' => 30]
);
// Первый promise(медленный но хороший)
$promise1 = $client->postAsync('https://api.xn--80ajbekothchmme5j.xn--p1ai/gis/moneta?XDEBUG_SESSION_START=PHPSTORM&priority=8&accrualTypeId=gibdd',[
    'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ItgDjzHIWDxU1a0QTLKHnXT4nO12T0XJ',
        'Google-Captcha-Token' => 'm53YUELHkejMjLEzQ_SXDYLB-LM_cirt',
        'Cookie' => '_csrf-frontend=9af9dca865c71da1dbfb4258c97d51c6e9dee5881f7fe39a78abe6fe6f366483a%3A2%3A%7Bi%3A0%3Bs%3A14%3A%22_csrf-frontend%22%3Bi%3A1%3Bs%3A32%3A%22nt-f2czH6TDupMEaZLVCK9-FChclqQKU%22%3B%7D'
    ],
    // Поскольку это POST-запрос размещение параметров priority и accrualTypeId не имело смысла
    'json' => [['document_type' => 'ctc', 'document_value' => '9940368866', 'priority' => 8, 'accrualTypeId' => 'gibdd']]
]);
$promise1->then(
    function(ResponseInterface $response) {
        echo "Успех! Код: " . $response->getStatusCode() . "\n";
        echo "Ответ: " . $response->getBody() . "\n";
    },
    function(RequestException $requestException) {
        echo "Ошибка: " . $requestException->getMessage() . "\n";
        if ($requestException->hasResponse()) {
            echo "Ответ сервера: " . $requestException->getResponse()->getBody() . "\n";
        }
    }
);
// Второй promise(быстрый но не так хорош)
$promise2 = $client->postAsync('https://api.xn--80ajbekothchmme5j.xn--p1ai/gis/a3?XDEBUG_SESSION_START=PHPSTORM&priority=8&accrualTypeId=gibdd', [
    'headers' => [
        'Content-Type' => 'application/json',
        'Google-Captcha-Token' => 'm53YUELHkejMjLEzQ_SXDYLB-LM_cirt',
        'Cookie' => '_csrf-frontend=9af9dca865c71da1dbfb4258c97d51c6e9dee5881f7fe39a78abe6fe6f366483a%3A2%3A%7Bi%3A0%3Bs%3A14%3A%22_csrf-frontend%22%3Bi%3A1%3Bs%3A32%3A%22nt-f2czH6TDupMEaZLVCK9-FChclqQKU%22%3B%7D'
    ],
    'json' => [['document_type' => 'ctc', 'document_value' => '9940368866']]
]);
$promise2->then(
    function(ResponseInterface $response) {
        echo "Успех! Код: " . $response->getStatusCode() . "\n";
        echo "Ответ: " . $response->getBody() . "\n";
    },
    function(RequestException $requestException) {
        echo "Ошибка: " . $requestException->getMessage() . "\n";
        if ($requestException->hasResponse()) {
            echo "Ответ сервера: " . $requestException->getResponse()->getBody() . "\n";
        }
    }
);
$promises = [$promise1, $promise2];
// Время начала
$startTime = microtime(true);

$results = \GuzzleHttp\Promise\Utils::settle($promises)->wait(); // Оба процесса запускаются параллельно
$elapsedTime = microtime(true) - $startTime; // Время выполнения промисов

if($results[0]['state'] === 'fulfilled' && $elapsedTime <= 30)
{
    // Первый эндпоинт ответил за 30 секунд
    echo "Первый эндпоинт ответил вовремя за" . round($elapsedTime, 2) .  "сек):\n";
    echo $results[0]['value']->getBody() . "\n";
} else if($results[1]['state'] === 'fulfilled') {
    // Первый эндпоинт не успел или провалился,нужно взять второй
    echo "Первый не ответил вовремя или провалился (время: " . round($elapsedTime, 2) . " сек), берём второй:\n";
    echo $results[1]['value']->getBody() . "\n";
}
else {
    // Оба провалились
    echo "Оба эндпоинта провалились:\n";
    echo "Первый: " . $results[0]['reason']->getMessage() . "\n";
    echo "Второй: " . $results[1]['reason']->getMessage() . "\n";
}
