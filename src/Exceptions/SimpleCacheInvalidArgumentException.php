<?php
declare(strict_types=1);

namespace ItalyStrap\Cache\Exceptions;

use InvalidArgumentException;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

use function sprintf;

class SimpleCacheInvalidArgumentException extends InvalidArgumentException implements PsrInvalidArgumentException {

	public static function maximumKeyLengthExceeded(string $key, int $maximumKeyLength): self {
		return new self(sprintf(
			'Invalid key "%s" provided; key is too long. Must be no more than %d characters',
			$key,
			$maximumKeyLength
		));
	}
}
