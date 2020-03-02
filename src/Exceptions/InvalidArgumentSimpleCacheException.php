<?php
declare(strict_types=1);

namespace ItalyStrap\SimpleCache\Exceptions;

use InvalidArgumentException;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

class InvalidArgumentSimpleCacheException extends InvalidArgumentException implements PsrInvalidArgumentException {


}
