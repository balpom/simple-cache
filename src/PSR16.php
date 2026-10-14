<?php

declare(strict_types=1);

namespace Balpom\SimpleCache;

use Psr\SimpleCache\CacheInterface;
use Balpom\Storage\DatedKeyValueStorage;
use Balpom\Storage\StorageException;

class PSR16 implements CacheInterface
{
    private DatedKeyValueStorage $storage;

    public function __construct(DatedKeyValueStorage $storage)
    {
        $this->storage = $storage;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->checkKey($key);

        if ($this->isExpired($key)) {
            try {
                $this->storage->delete($key);
            } catch (StorageException $e) {
                // Do nothing.
            }
            return $default;
        }

        try {
            $value = $this->storage->value($key);
        } catch (StorageException $e) {
            return $default;
        }

        if (false === $value) {
            $value = $default;
        }

        return $value;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        $this->checkKeyValue($key, $value);
        $expiry = $this->getExpiry($ttl);

        try {
            $result = $this->storage->store($key, $value);
        } catch (StorageException $e) {
            return false;
        }

        if ($result && null !== $expiry) {
            $result = $this->storage->time($key, $expiry);
        }

        return $result;
    }

    public function delete(string $key): bool
    {
        $this->checkKey($key);
        try {
            $result = $this->storage->delete($key);
        } catch (StorageException $e) {
            $result = false;
        }

        return $result;
    }

    public function clear(): bool
    {
        try {
            $result = $this->storage->clear();
        } catch (StorageException $e) {
            $result = false;
        }

        return $result;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $this->isIterable($keys);
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        $this->isIterable($values);
        $result = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $result = false;
            }
        }

        return $result;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $this->isIterable($keys);
        $result = true;
        foreach ($keys as $key) {
            if (!$this->storage->delete($key)) {
                $result = false;
            }
        }

        return $result;
    }

    public function has(string $key): bool
    {
        $this->checkKey($key);
        return $this->storage->exists($key);
    }

    protected function checkKey(string $key): void
    {
        if (!$this->storage->isValidKey($key)) {
            throw new InvalidArgumentException('KEY string is not a legal value.');
        }
    }

    protected function checkValue(mixed $value): void
    {
        if (null !== $value && !$this->storage->isValidValue($value)) {
            throw new InvalidArgumentException('VALUE is not a legal value.');
        }
    }

    protected function checkKeyValue(string $key, mixed $value): void
    {
        $message = '';
        try {
            $this->checkKey($key);
        } catch (InvalidArgumentException $e) {
            $message .= $e->getMessage() . ' ';
        }
        try {
            $this->checkValue($value);
        } catch (InvalidArgumentException $e) {
            $message .= $e->getMessage();
        }
        if (!empty($message)) {
            throw new InvalidArgumentException(trim($message));
        }
    }

    protected function isIterable(mixed $something): void
    {
        if (!is_iterable($something)) {
            throw new InvalidArgumentException('Given value is not iterable.');
        }
    }

    protected function isExpired(string $key): bool
    {
        try {
            $time = $this->storage->time($key);
        } catch (StorageException $e) {
            return true;
        }

        return $time < time() ? true : false;
    }

    protected function getExpiry(null|int $ttl = 0, null|int $from = null): null|int
    {
        if (null === $from) {
            $from = time();
        }
        $expiry = null;
        if ($ttl instanceof \DateInterval) {
            $expiry = (new \DateTime($from))->add($ttl)->getTimestamp();
        } elseif (is_int($ttl)) {
            $expiry = $from + $ttl;
        }

        return $expiry;
    }

    public function __destruct()
    {
        // TODO:
        // Cleaning the cache of expired items, which occurs with a certain probability.
        // It must be launched in a separate process, not tied to the current one.
    }

}