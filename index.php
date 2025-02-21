<?php
// Примечание: Обновлена логика — второй результат берётся, если первый превышает 30 секунд.

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise;

require 'vendor/autoload.php';

$client = new Client([
    'timeout' => 30,
]);

$startTime = microtime(true);

// Первый запрос: "медленный, но хороший"
$promise1 = $client->postAsync('https://api.xn--80ajbekothchmme5j.xn--p1ai/gis-gate/api/v3/gis/search', [
    'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Basic YWRtaW46c2RmakpLRkhzZGYyMzRkZHNzdw=='
    ],
    'json' => [
        'only_active' => true,
        'priority' => 8,
        'timeout' => 25,
        'page' => 1,
        'allow_pagination' => true,
        'accrual_type' => 'gibdd',
        'gate' => 'moneta',
        'give_raw' => false,
        'use_cache' => false,
        'requisites' => [
            ['document_type' => 'ctc', 'document_value' => '9949041957']
        ]
    ]
]);

// Второй запрос: "быстрый, но плохой"
$promise2 = $client->postAsync('https://api.xn--80ajbekothchmme5j.xn--p1ai/gis-gate/api/v1/gis/search', [
    'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Basic YWRtaW46c2RmakpLRkhzZGYyMzRkZHNzdw=='
    ],
    'json' => [
        'only_active' => false,
        'priority' => 8,
        'timeout' => 25,
        'page' => 1,
        'allow_pagination' => false,
        'accrual_type' => 'gibdd',
        'gate' => 'a3',
        'requisites' => [
            ['document_type' => 'ctc', 'document_value' => '9945585636']
        ]
    ]
]);

$results = Promise\Utils::settle([$promise1, $promise2])->wait();
$elapsedTime = microtime(true) - $startTime;

// Проверяем время первого запроса
$firstTime = $results[0]['state'] === 'fulfilled' ? $elapsedTime : INF; // INF если провалился

if ($results[0]['state'] === 'fulfilled' && $firstTime <= 30) {
    echo "Первый эндпоинт ('медленный, но хороший') ответил за " . round($firstTime, 2) . " сек:\n";
    echo $results[0]['value']->getBody() . "\n";
} elseif ($results[1]['state'] === 'fulfilled') {
    echo "Первый превысил 30 сек или провалился, второй ('быстрый, но плохой') ответил за " . round($elapsedTime, 2) . " сек:\n";
    echo $results[1]['value']->getBody() . "\n";
} else {
    echo "Ошибка: оба эндпоинта провалились:\n";
    echo "Первый: " . ($results[0]['state'] === 'rejected' ? $results[0]['reason']->getMessage() : 'timeout') . "\n";
    echo "Второй: " . ($results[1]['state'] === 'rejected' ? $results[1]['reason']->getMessage() : 'timeout') . "\n";
}
