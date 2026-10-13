<?php

namespace Balpom\SimpleCache;

ini_set('max_execution_time', '15');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('html_errors', 'off');
error_reporting(E_ALL);

include dirname(__DIR__) . '/vendor/autoload.php';

use Balpom\Storage\KeyValue\Files\LocalFilesDriver;
use Balpom\Storage\DatedKeyValueStorage;

$cacheDirectory = __DIR__ . '/cache_dir';
$driver = new LocalFilesDriver($cacheDirectory);
$storage = new DatedKeyValueStorage($driver);

$cache = new PSR16($storage);

$key = 'test_key';
$value = 'test_value';
$ttl = 2; // 2 sec
//$ttl = 55555; // 55555 sec

$cache->set($key, $value, $ttl);

//die;

echo $cache->get($key, 'The cache has gone bad.') . PHP_EOL;
sleep(3); // 3 sec
echo $cache->get($key, 'The cache has gone bad.') . PHP_EOL;

//die;

$cache->set($key, $value, 3600);

echo $cache->get($key, 'The cache has gone bad.') . PHP_EOL;
$cache->delete($key);
echo $cache->get($key, 'The cache has gone bad.') . PHP_EOL;

for ($i = 1; $i <= 3; $i++) {
    $cache->set($key . $i, $value . $i, $ttl);
}
sleep(3); // 3 sec
$cache->clear();

