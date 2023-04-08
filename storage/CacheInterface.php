<?php
declare(strict_types=1);

namespace ItalyStrap\Storage;

/**
 * @psalm-api
 */
interface CacheInterface
{
    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @return bool
     */
    public function set(string $key, $value, ?int $ttl = null): bool;

    /**
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @return bool
     */
    public function update(string $key, $value, ?int $ttl = null): bool;

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * @param iterable $keys
     * @param mixed $default
     * @return iterable
     */
    public function getMultiple(iterable $keys, $default = null): iterable;

    /**
     * @param iterable $values
     * @param int|null $ttl
     * @return bool
     */
    public function setMultiple(iterable $values, ?int $ttl = null): bool;

    /**
     * @param iterable $keys
     * @return bool
     */
    public function deleteMultiple(iterable $keys): bool;
}
