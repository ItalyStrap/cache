<?php
declare(strict_types=1);

namespace ItalyStrap\Storage;

trait SetStoreTrait
{
    /**
     * @param $values
     * @param $ttl
     * @return bool
     */
    public function setMultiple(iterable $values): bool
    {
        /**
         * @var string $key
         * @var mixed $value
         */
        foreach ($values as $key => $value) {
            if ($this->set($key, $value)) {
                continue;
            }
            return false;
        }

        return true;
    }
}
