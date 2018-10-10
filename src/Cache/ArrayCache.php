<?php

namespace BenTools\Currency\Cache;

use Psr\SimpleCache\CacheInterface;
use Traversable;

class ArrayCache implements CacheInterface
{

    private $defaultTtl;
    private $storage = [];
    private $expiries = [];

    /**
     * ArrayCache constructor.
     * @param int|null $defaultTtl
     */
    public function __construct(int $defaultTtl = null)
    {
        $this->defaultTtl = $defaultTtl;
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->storage[$key];
        }
        if ($this->isExpired($key)) {
            $this->delete($key);
        }
        return $default;
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        $this->storage[$key] = $value;
        $this->expiries[$key] = $this->computeExpiryTime($ttl);
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        unset($this->storage[$key], $this->expiries[$key]);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->storage = [];
        $this->expiries = [];
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        foreach ($keys as $key) {
            yield $key => $this->get($key, $default);
        }
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        if (!$this->isIterable($values)) {
            throw new \InvalidArgumentException("Values must be array or Traversable");
        }
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        return isset($this->storage[$key]) && !$this->isExpired($key);
    }

    /**
     * @param mixed $values
     * @return bool
     */
    private function isIterable($values): bool
    {
        return is_array($values) || $values instanceof Traversable;
    }

    /**
     * @param int|null $ttl
     * @return int|null
     */
    private function computeExpiryTime(int $ttl = null): ?int
    {
        $ttl = $ttl ?? $this->defaultTtl;
        if (null === $ttl) {
            return null;
        }
        return time() + $ttl;
    }

    /**
     * @param string $key
     * @return bool
     */
    private function isExpired($key): bool
    {
        if (isset($this->expiries[$key]) && null !== $this->expiries[$key]) {
            return time() >= $this->expiries[$key];
        }
        return false;
    }
}
