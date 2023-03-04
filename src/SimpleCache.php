<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use ArrayObject;
use DateInterval;
use DateTime;
use Exception;
use Fig\Cache\KeyValidatorTrait;
use ItalyStrap\Cache\Exceptions\SimpleCacheInvalidArgumentException;
use ItalyStrap\Storage\StorageInterface;
use ItalyStrap\Storage\Transient;
use ItalyStrap\Tests\ConvertDateIntervalToIntegerTrait;
use Psr\SimpleCache\CacheInterface as PsrSimpleCacheInterface;
use Traversable;
use function array_keys;
use function get_class;
use function gettype;
use function intval;
use function is_array;
use function is_object;
use function is_string;
use function iterator_to_array;
use function sprintf;

/**
 * @psalm-api
 */
class SimpleCache implements PsrSimpleCacheInterface {

//	use KeyValidatorTrait;
	use ConvertDateIntervalToIntegerTrait, ToArrayTrait;

	private StorageInterface $storage;
	private array $used_keys = [];

	public function __construct(StorageInterface $storage = null) {
		$this->storage = $storage;
	}

	/**
	 * @inheritDoc
	 */
	public function has( $key ): bool {
		return $this->get( $key, false ) !== false;
	}

	/**
	 * @inheritDoc
	 * If you need to store booleans use 0 or 1 because
	 * get_transient() return false if value is not set or is expired
	 * @see \get_transient()
	 */
	public function get( $key, $default = null ) {
		$this->assertKeyIsValid( $key );
		$this->addUsedKey( $key );

		/** @var mixed $value */
		$value = $this->storage->get( $key );
		if ( false === $value ) {
			$value = $default;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function set( $key, $value, $ttl = null ): bool {
		$this->assertKeyIsValid( $key );
		$this->addUsedKey( $key );

		if ($ttl instanceof DateInterval) {
			$ttl = $this->convertDateIntervalToInteger($ttl);
		}

		return $this->storage->set( $key, $value, intval($ttl) );
	}

	/**
	 * @inheritDoc
	 */
	public function delete( $key ): bool {
		$this->assertKeyIsValid( $key );
		$this->deleteUsedKey( $key );
		return $this->storage->delete( $key );
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
		return $this->deleteMultiple( $this->usedKeys() );
	}

	/**
	 * @psalm-suppress ReservedWord
	 * @param mixed $key
	 */
	private function assertKeyIsValid( $key ): void {
		if ( ! is_string( $key ) ) {
			throw new SimpleCacheInvalidArgumentException( sprintf(
				'The $key must be a string, %s given',
				gettype( $key )
			) );
		}

		if ( empty( $key ) ) {
			throw new SimpleCacheInvalidArgumentException( 'The $key must be not empty' );
		}
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
	 * @param string $key
	 */
	private function addUsedKey( $key ): void {
		$this->used_keys[ $key ] = $key;
	}

	/**
	 * @param string $key
	 */
	private function deleteUsedKey( $key ): void {
		unset( $this->used_keys[ $key ] );
	}

	/**
	 * @return array
	 */
	private function usedKeys(): array {
		return $this->used_keys;
	}
}
