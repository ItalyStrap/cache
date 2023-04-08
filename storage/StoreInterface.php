<?php
declare(strict_types=1);

namespace ItalyStrap\Storage;

/**
 * @psalm-api
 */
interface StoreInterface
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
     * @return bool
     */
    public function set(string $key, $value): bool;

    /**
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function update(string $key, $value): bool;

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
     * @return bool
     */
    public function setMultiple(iterable $values): bool;

    /**
     * @param iterable $keys
     * @return bool
     */
    public function deleteMultiple(iterable $keys): bool;
}
