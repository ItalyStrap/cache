<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use ArrayObject;
use ItalyStrap\Cache\Exceptions\SimpleCacheInvalidArgumentException;
use Traversable;
use function get_class;
use function is_array;
use function is_object;
use function iterator_to_array;
use function sprintf;

trait ToArrayTrait {

	/**
	 * @param iterable $other
	 * @param string $type
	 * @return array
	 * @author Sebastian Bergmann PHPUnit
	 */
	private function toArray(iterable $other, string $type = 'keys'): array {
		if (is_array($other)) {
			return $other;
		}

		if ($other instanceof ArrayObject) {
			return $other->getArrayCopy();
		}

		if ($other instanceof Traversable) {
			return iterator_to_array($other);
		}

		throw new SimpleCacheInvalidArgumentException(
			sprintf(
				'Cache %s must be array or Traversable, "%s" given',
				$type,
				is_object($other) ? get_class($other) : gettype($other)
			)
		);
	}
}
