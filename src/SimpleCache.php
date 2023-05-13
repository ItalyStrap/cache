<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use ItalyStrap\Cache\Exceptions\SimpleCacheInvalidArgumentException;
use ItalyStrap\Storage\CacheInterface;
use ItalyStrap\Storage\ClearableInterface;
use Psr\Clock\ClockInterface;
use Psr\SimpleCache\CacheInterface as PsrSimpleCacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * @psalm-api
 */
class SimpleCache implements PsrSimpleCacheInterface
{

    use KeyValidatorTrait;

    private CacheInterface $driver;
    private ExpirationInterface $expiration;
    private array $used_keys = [];
    private array $type = [];

    public function __construct(CacheInterface $driver, ExpirationInterface $expiration = null)
    {
        $this->driver = $driver;
        $this->expiration = $expiration ?? new Expiration();
    }

    public function has($key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * @param mixed $key
     * @param mixed $default
     * @return bool|mixed|null
     */
    public function get($key, $default = null)
    {
        $this->assertKeyIsValid($key);
        $this->addUsedKey((string)$key);

        /**
         * This is a bit tricky because transient return false not as value but
         * as if no value is stored, as usual...
         * If no value is stored the array key does not exist, so it will return $default = null as value
         * This should be almost safe.
         * Normally you do something like this: `false === get_transient('some-key')`
         * With this you simply call SimpleCache::has('some-key');
         * @var mixed $value
         */
        $value = $this->driver->get((string)$key);
        if (\array_key_exists((string)$key, $this->type) && $this->type[(string)$key] === 'boolean') {
            return (bool)$value;
        }

        if ($value === 0) {
            return 0;
        }

        return $value ?: $default;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @param \DateInterval|int|null $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null): bool
    {
        $this->assertKeyIsValid($key);
        $this->addUsedKey((string)$key);
        $this->addValueType((string)$key, $value);

        try {
            $this->expiration->expiresAfter($ttl);
        } catch (\InvalidArgumentException $e) {
            throw new SimpleCacheInvalidArgumentException($e->getMessage(), $e->getCode());
        }

        return $this->driver->set(
            (string)$key,
            \is_object($value) ? clone $value : $value,
            $this->expiration->expirationInSeconds()
        );
    }

    public function delete($key): bool
    {
        $this->assertKeyIsValid($key);

        if (!$this->has($key)) {
            $this->deleteUsedKey($key);
            return true;
        }

        $this->deleteUsedKey($key);
        return $this->driver->delete($key);
    }

    /**
     * @param mixed $keys
     * @param mixed $default
     * @psalm-return \Generator<mixed, mixed|null, mixed, never>
     */
    public function getMultiple($keys, $default = null): iterable
    {
        if (!\is_iterable($keys)) {
            throw new SimpleCacheInvalidArgumentException('Cache keys must be array or Traversable');
        }

        $gen =
            /**
             * @psalm-return \Generator<mixed, mixed|null, mixed, never>
             */
        function () use ($keys, $default): \Generator {
            /** @var string[] $keys */
            foreach ($keys as $key) {
                /** @psalm-suppress InvalidCatch */
                try {
                    yield $key => $this->get($key, $default);
                } catch (InvalidArgumentException $e) {
                    throw new SimpleCacheInvalidArgumentException($e->getMessage(), (int)$e->getCode());
                }
            }
        };

        return $gen();
    }

    /**
     * @param mixed $values
     * @param \DateInterval|int|null $ttl
     * @return bool
     */
    public function setMultiple($values, $ttl = null): bool
    {
        if (!\is_iterable($values)) {
            throw new SimpleCacheInvalidArgumentException('Cache values must be array or Traversable');
        }

        /**
         * @var string $key
         * @var mixed $value
         */
        foreach ($values as $key => $value) {
            if ($this->set($key, $value, $ttl)) {
                continue;
            }
            return false;
        }

        return true;
    }

    /**
     * @param iterable|mixed $keys
     * @return bool
     */
    public function deleteMultiple($keys): bool
    {
        if (!\is_iterable($keys)) {
            throw new SimpleCacheInvalidArgumentException('Cache keys must be array or Traversable');
        }

        /** @var string[] $keys */
        foreach ($keys as $key) {
            if ($this->delete($key)) {
                continue;
            }
            return false;
        }

        return true;
    }

    public function clear(): bool
    {
        $cleared = true;
        if ($this->driver instanceof ClearableInterface) {
            $cleared = $this->driver->clear();
        }

        return $cleared && $this->deleteMultiple($this->usedKeys());
    }

    /**
     * @param mixed $key
     * @return void
     */
    private function assertKeyIsValid($key): void
    {
        try {
            $this->validateKey($key);
        } catch (\InvalidArgumentException $e) {
            throw new SimpleCacheInvalidArgumentException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param string|int $key
     * @return void
     */
    private function addUsedKey($key): void
    {
        $this->used_keys[ $key ] = $key;
    }

    /**
     * @param string|int $key
     * @return void
     */
    private function deleteUsedKey($key): void
    {
        unset($this->used_keys[ $key ]);
    }

    private function usedKeys(): array
    {
        return $this->used_keys;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    private function addValueType(string $key, $value): void
    {
        $this->type[$key] = \gettype($value);
    }
}
