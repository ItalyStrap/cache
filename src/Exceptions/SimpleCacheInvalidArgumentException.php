<?php
declare(strict_types=1);

namespace ItalyStrap\Cache\Exceptions;

use InvalidArgumentException;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

use function sprintf;

class SimpleCacheInvalidArgumentException extends InvalidArgumentException implements PsrInvalidArgumentException {
}
