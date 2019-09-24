<?php
declare(strict_types=1);

namespace App\Cache;

use Psr\SimpleCache\CacheInterface;

class InMemoryCache implements CacheInterface
{
    private $store = [];

    /** @inheritDoc */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->store[$key] : $default;
    }

    /** @inheritDoc */
    public function set($key, $value, $ttl = null)
    {
        $this->store[$key] = $value;
        return true;
    }

    /** @inheritDoc */
    public function delete($key)
    {
        if (!$this->has($key)) {
            return false;
        }
        unset($this->store[$key]);
        return true;
    }

    /** @inheritDoc */
    public function clear()
    {
        $this->store = [];
    }

    /** @inheritDoc */
    public function getMultiple($keys, $default = null)
    {
        foreach ($keys as $key) {
            $this->get($key, $default);
        }
    }

    /** @inheritDoc */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            if (false === $this->set($key, $value, $ttl)) {
                return false;
            }
        }
        return true;
    }

    /** @inheritDoc */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            if (false === $this->delete($key)) {
                return false;
            }
        }
        return true;
    }

    /** @inheritDoc */
    public function has($key)
    {
        return array_key_exists($key, $this->store);
    }
}