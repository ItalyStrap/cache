<?php
declare(strict_types=1);

namespace ItalyStrap\Storage;

trait KeyLengthValidate
{
    public function assertKeyLength(string $key): void
    {
        if (\strlen($key) > 180) {
            throw new \InvalidArgumentException(\sprintf(
                'The maximum length key "%s" is %d characters',
                $key,
                180
            ));
        }
    }
}
