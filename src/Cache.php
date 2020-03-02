<?php
declare(strict_types=1);

namespace ItalyStrap\SimpleCache;

use DateInterval;
use DateTime;
use Exception;
use ItalyStrap\SimpleCache\Exceptions\InvalidArgumentSimpleCacheException;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Traversable;
use function array_keys;
use function array_map;
use function boolval;
use function delete_transient;
use function get_class;
use function get_transient;
use function gettype;
use function is_array;
use function is_object;
use function is_string;
use function iterator_to_array;
use function set_transient;
use function sprintf;

class Cache implements CacheInterface {

	/**
	 * Data value of the transient
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 * @inheritDoc
	 */
	public function has( $key ) {
		$this->assertKeyIsValid( $key );
		return boolval( $this->get( $key ) );
	}

	/**
	 * @inheritDoc
	 */
	public function get( $key, $default = null ) {
		$this->assertKeyIsValid( $key );

		$value = get_transient( $key );
		// If you need to store booleans use 0 or 1 because
		// get_transient() return false if value is not set or is expired
		if ( 0 === $value ) {
			return $value;
		}

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
		$this->data[$key] = $key;

		if ($ttl instanceof DateInterval) {
			$ttl = $this->convertDateIntervalToInteger($ttl);
		}

		return set_transient( $key, $value, $ttl );
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

		if (!$values instanceof Traversable && !is_array($values)) {
			throw new InvalidArgumentSimpleCacheException( sprintf(
				'The $values must be a Traversable, %s given',
				gettype( $values )
			), 0 );
		}

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
		foreach ( $keys as $key ) {
			if ( $this->delete( $key ) ) {
				continue;
			}
			$success = false;
		}

		return $success;
	}

	/**
	 * @inheritDoc
	 * @throws InvalidArgumentException
	 */
	public function clear(): bool {
		return $this->deleteMultiple( array_keys( $this->data ) );
	}

	/**
	 * @param string $key
	 */
	private function assertKeyIsValid( $key ): void {
		if ( ! is_string( $key ) ) {
			throw new InvalidArgumentSimpleCacheException( sprintf(
				'The $key must be a string, %s given',
				gettype( $key )
			), 0 );
		}

		if ( empty( $key ) ) {
			throw new InvalidArgumentSimpleCacheException( 'The $key must be not empty', 0 );
		}
	}

	/**
	 * @param DateInterval $ttl
	 * @return int
	 * @throws Exception
	 * @author Roave\DoctrineSimpleCache;
	 */
	private function convertDateIntervalToInteger( DateInterval $ttl) : int
	{
		// Timestamp has 2038 year limitation, but it's unlikely to set TTL that long.
		return (new DateTime())
			->setTimestamp(0)
			->add($ttl)
			->getTimestamp();
	}

	/**
	 * @param array $keys
	 * @return array
	 */
	private function assertKeysAreValid( $keys ): array {
		if ( $keys instanceof Traversable ) {
			$keys = iterator_to_array( $keys, false );
		}

		if ( ! is_array( $keys ) ) {
			throw new InvalidArgumentSimpleCacheException(
				sprintf(
					'Cache keys must be array or Traversable, "%s" given',
					is_object( $keys ) ? get_class( $keys ) : gettype( $keys )
				)
			);
		}

		array_map( [$this, 'assertKeyIsValid'], $keys );
		return $keys;
	}
}
