<?php

declare(strict_types=1);

namespace ItalyStrap\Storage;

/**
 * @psalm-api
 */
interface ClearableInterface
{
    /**
     * Removes all cache items.
     *
     * @return bool True on success, false on failure.
     */
    public function clear(): bool;
}
