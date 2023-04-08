<?php
declare(strict_types=1);

namespace ItalyStrap\Storage;

/**
 * @psalm-api
 */
interface IncrDecrInterface
{
    /**
     * Increment the numeric cache item's value.
     *
     * @param string $key    The key for the cache contents that should be incremented.
     * @param int        $offset Optional. The amount by which to increment the item's value. Default 1.
     * @return int|false The item's new value on success, false on failure.
     */
    public function increment(string $key, int $offset = 1);

    /**
     * Decrement the numeric cache item's value.
     *
     * @param string $key    The cache key to decrement.
     * @param int        $offset Optional. The amount by which to decrement the item's value. Default 1.
     * @return int|false The item's new value on success, false on failure.
     */
    public function decrement(string $key, int $offset = 1);
}
