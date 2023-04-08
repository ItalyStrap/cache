<?php
declare(strict_types=1);

namespace ItalyStrap\Storage;

trait SetCacheTrait
{
    /**
     * @param $values
     * @param $ttl
     * @return bool
     */
    public function setMultiple(iterable $values, ?int $ttl = null): bool
    {
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
}
