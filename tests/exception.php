<?php

namespace Balpom\SimpleCache;

ini_set('max_execution_time', '15');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('html_errors', 'off');
error_reporting(E_ALL);

include dirname(__DIR__) . '/vendor/autoload.php';

use Balpom\Storage\KeyValue\Memory\StatelessMemoryDriver;
use Balpom\Storage\DatedKeyValueStorage;
use Psr\SimpleCache\InvalidArgumentException;

$driver = new StatelessMemoryDriver();
$storage = new DatedKeyValueStorage($driver);

$cache = new PSR16($storage);

$key = 'test_key';
$value = 'test_value';
$emptyString = '';
$emptyArray = [];

try {
    $cache->get($emptyString);
} catch (InvalidArgumentException $e) {
    echo 'EXCEPTION: ' . $e->getMessage() . PHP_EOL;
}

try {
    $cache->set($key, $emptyArray);
} catch (InvalidArgumentException $e) {
    echo 'EXCEPTION: ' . $e->getMessage() . PHP_EOL;
}

try {
    $cache->set($emptyString, $emptyArray);
} catch (InvalidArgumentException $e) {
    echo 'EXCEPTION: ' . $e->getMessage() . PHP_EOL;
}