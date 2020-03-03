<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use ArrayObject;
use DateInterval;
use DateTime;
use Exception;
use ItalyStrap\Cache\Exceptions\InvalidArgumentSimpleCacheException;
use Psr\SimpleCache\CacheInterface as PsrSimpleCacheInterface;
use Traversable;
use function array_keys;
use function array_map;
use function boolval;
use function delete_transient;
use function get_class;
use function get_transient;
use function gettype;
use function intval;
use function is_array;
use function is_object;
use function is_string;
use function iterator_to_array;
use function set_transient;
use function sprintf;

class SimpleCache implements PsrSimpleCacheInterface {


	/**
	 * Data value of the transient
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 * @inheritDoc
	 */
	public function has( $key ): bool {
		return boolval( $this->get( $key ) );
	}

	/**
	 * @inheritDoc
	 * If you need to store booleans use 0 or 1 because
	 * get_transient() return false if value is not set or is expired
	 * @see get_transient()
	 */
	public function get( $key, $default = null ) {
		$this->assertKeyIsValid( $key );

		$value = get_transient( $key );
		if ( false === $value ) {
			return $default;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function set( $key, $value, $ttl = null ): bool {
		$this->assertKeyIsValid( $key );
		$this->data[$key] = $value;

		if ($ttl instanceof DateInterval) {
			$ttl = $this->convertDateIntervalToInteger($ttl);
		}

		return set_transient( $key, $value, intval($ttl) );
	}

	/**
	 * @inheritDoc
	 */
	public function delete( $key ): bool {
		$this->assertKeyIsValid( $key );
		unset($this->data[$key]);
		return delete_transient( $key );
	}

	/**
	 * @inheritDoc
	 */
	public function getMultiple( $keys, $default = null ) {
		$keys = $this->assertKeysAreValid( $keys );

		$data = [];

		foreach ( $keys as $key ) {
			$data[ $key ] = $this->get( $key, $default );
		}

		return $data;
	}

	/**
	 * @inheritDoc
	 */
	public function setMultiple( $values, $ttl = null ): bool {
		$values = $this->toArray($values, 'values');

		$success = true;
		foreach ( $values as $key => $value ) {
			if ( $this->set( $key, $value, $ttl ) ) {
				continue;
			}
			$success = false;
		}

		return $success;
	}

	/**
	 * @inheritDoc
	 */
	public function deleteMultiple( $keys ): bool {
		$keys = $this->assertKeysAreValid( $keys );
		$success = true;
		/** @var string $key */
		foreach ( $keys as $key ) {
			if ( $this->delete( $key ) ) {
				continue;
			}
			$success = false;
		}

		return $success;
	}

	/**
	 * @psalm-suppress InvalidThrow
	 * @inheritDoc
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function clear(): bool {
		return $this->deleteMultiple( array_keys( $this->data ) );
	}

	/**
	 * @psalm-suppress ReservedWord
	 * @param mixed $key
	 */
	private function assertKeyIsValid( $key ): void {
		if ( ! is_string( $key ) ) {
			throw new InvalidArgumentSimpleCacheException( sprintf(
				'The $key must be a string, %s given',
				gettype( $key )
			) );
		}

		if ( empty( $key ) ) {
			throw new InvalidArgumentSimpleCacheException( 'The $key must be not empty' );
		}
	}

	/**
	 * @param DateInterval $ttl
	 * @return int
	 * @throws Exception
	 * @author Roave\DoctrineSimpleCache;
	 */
	private function convertDateIntervalToInteger( DateInterval $ttl ) : int {
		// Timestamp has 2038 year limitation, but it's unlikely to set TTL that long.
		return (new DateTime())
			->setTimestamp(0)
			->add($ttl)
			->getTimestamp();
	}

	/**
	 * @param array|iterable $keys
	 * @return array<string>
	 */
	private function assertKeysAreValid( $keys ): array {
		$keys = $this->toArray($keys, 'keys');
		return $keys;
	}

	/**
	 * @param iterable $other
	 * @param string $type
	 * @return array
	 * @author Sebastian Bergmann PHPUnit
	 */
	private function toArray($other, $type = 'keys'): array {
		if ( is_array($other)) {
			return $other;
		}

		if ($other instanceof ArrayObject) {
			return $other->getArrayCopy();
		}

		if ($other instanceof Traversable) {
			return iterator_to_array($other);
		}

		throw new InvalidArgumentSimpleCacheException(
			sprintf(
				'Cache %s must be array or Traversable, "%s" given',
				$type,
				is_object( $other ) ? get_class( $other ) : gettype( $other )
			)
		);
	}
}
