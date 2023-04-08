<?php
declare(strict_types=1);

namespace ItalyStrap\Storage;

trait MultipleTrait
{
    /**
     * @param iterable $keys
     * @param mixed $default
     * @return iterable
     */
    public function getMultiple(iterable $keys, $default = null): iterable
    {
        $gen =
            /**
             * @psalm-return \Generator<string, false|mixed|null, mixed, void>
             */
            function () use ($keys, $default): \Generator {
                /** @var string[] $keys */
                foreach ($keys as $key) {
                    yield $key => $this->get($key, $default);
                }
            };

        return $gen();
    }

    /**
     * @param iterable $keys
     * @return bool
     */
    public function deleteMultiple(iterable $keys): bool
    {
        /** @var string[] $keys */
        foreach ($keys as $key) {
            if ($this->delete($key)) {
                continue;
            }
            return false;
        }

        return true;
    }
}
