<?php
declare(strict_types=1);

namespace ItalyStrap\Storage;

/**
 * @see \wp_cache_add()
 * @see \wp_cache_get()
 * @see \wp_cache_set()
 * @see \wp_cache_replace()
 * @see \wp_cache_delete()
 * @see \wp_cache_flush()
 *
 * @psalm-api
 */
class Cache implements CacheInterface, ClearableInterface, IncrDecrInterface
{

    public function get(string $key, $default = null)
    {
        return \wp_cache_get($key, 'default') ?? $default;
    }

    public function set(string $key, $value, ?int $ttl = 0): bool
    {
        return (bool)\wp_cache_set($key, $value, 'default', $ttl);
    }

    public function update(string $key, $value, ?int $ttl = 0): bool
    {
        return (bool)\wp_cache_replace($key, $value, 'default', $ttl);
    }

    public function delete(string $key): bool
    {
        return (bool)\wp_cache_delete($key, 'default');
    }

    /**
     * @param string $key
     * @param int $offset
     * @return false|int
     * @psalm-suppress MixedInferredReturnType
     */
    public function increment(string $key, int $offset = 1)
    {
        /** @psalm-suppress MixedReturnStatement */
        return \wp_cache_incr($key, $offset, 'default');
    }

    /**
     * @param string $key
     * @param int $offset
     * @return false|int
     * @psalm-suppress MixedInferredReturnType
     */
    public function decrement(string $key, int $offset = 1)
    {
        /** @psalm-suppress MixedReturnStatement */
        return \wp_cache_decr($key, $offset, 'default');
    }

    public function clear(): bool
    {
        return (bool)\wp_cache_flush();
    }

    public function setMultiple(iterable $values, ?int $ttl = null): bool
    {
        $newValues = $this->convertArray($values);
        return (bool)\wp_cache_set_multiple((array)$newValues, 'default', $ttl);
    }

    /**
     * @param iterable $keys
     * @param mixed $default
     * @return iterable<mixed, mixed|null>
     */
    public function getMultiple(iterable $keys, $default = null): iterable
    {
        $newValues = $this->convertArray($keys);
        /**
         * @var mixed $value
         */
        foreach ((array)\wp_cache_get_multiple($newValues, 'default') as $key => $value) {
            yield $key => $value ?? $default;
        }
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $newValues = $this->convertArray($keys);
        return (bool)\wp_cache_delete_multiple((array)$newValues, 'default');
    }

    /**
     * @param iterable $values
     * @return iterable
     */
    private function convertArray(iterable $values): iterable
    {
        return $values instanceof \Traversable ? \iterator_to_array($values) : $values;
    }
}
