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

$driver = new StatelessMemoryDriver();
$storage = new DatedKeyValueStorage($driver);

$cache = new PSR16($storage);

$key = 'test_key';
$value = 'test_value';
$ttl = 2; // 2 sec

$cache->set($key, $value, $ttl);

echo $cache->get($key, 'The cache has gone bad.') . PHP_EOL;
sleep(3); // 3 sec
echo $cache->get($key, 'The cache has gone bad.') . PHP_EOL;

$cache->set($key, $value, 3600);

echo $cache->get($key, 'The cache has gone bad.') . PHP_EOL;
$cache->delete($key);
echo $cache->get($key, 'The cache has gone bad.') . PHP_EOL;

for ($i = 1; $i <= 3; $i++) {
    $cache->set($key . $i, $value . $i, $ttl);
}
print_r($cache);
$cache->clear();
print_r($cache);
