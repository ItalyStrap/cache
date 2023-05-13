<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use ItalyStrap\Storage\CacheInterface;
use Psr\Cache\CacheItemInterface;

class Item implements CacheItemInterface
{

    private string $key;
    private bool $isHit;

    /**
     * @var mixed $value
     */
    private $value;
    private ExpirationInterface $expiration;
    private CacheInterface $driver;

    public function __construct(string $key, CacheInterface $driver, ExpirationInterface $expiration)
    {
        $this->key = $key;
        $this->driver = $driver;
        $this->value = $driver->get($key);
        $this->isHit = (bool)$this->value;
        $this->expiration = $expiration;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get()
    {
        if ($this->isHit()) {
            return $this->value;
        }

        return null;
    }

    public function isHit(): bool
    {
        /**
         * To remember:
         * $this->isHit is set in the constructor by the driver::get() method
         * if driver::get() return false then $this->isHit is false
         * and $this->expiration->isValid() could be true if no ::expiresAt() or ::expiresAfter() are called.
         * This should prevent to check the expiration time using \get_option(self::TRANSIENT_TIMEOUT_KEY . $this->key)
         * because driver::get() will always return false if the transient is expired
         * For example:
         * $this->isHit = false because the value is expired
         * and $this->expiration->isValid() could return true as value
         * In any case the value is expired, so it's not a hit even if $this->expiration->isValid() return true.
         */
        return $this->isHit && $this->expiration->isValid();
    }

    public function set($value): self
    {
        $this->value = $value;
        $this->isHit = true;
        return $this;
    }

    public function expiresAt($expiration): self
    {
        $this->expiration->expiresAt($expiration);
        return $this;
    }

    public function expiresAfter($time): self
    {
        $this->expiration->expiresAfter($time);
        return $this;
    }

    public function __invoke(): bool
    {
        $ttl = $this->expiration->expirationInSeconds();
        // @todo May add \InvalidArgumentException
        return $this->driver->set($this->key, $this->value, $ttl);
    }
}
