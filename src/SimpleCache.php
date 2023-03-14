<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use DateInterval;
use Exception;
use ItalyStrap\Cache\Exceptions\SimpleCacheInvalidArgumentException;
use ItalyStrap\Storage\StorageInterface;
use ItalyStrap\Tests\ConvertDateIntervalToIntegerTrait;
use Psr\SimpleCache\CacheInterface as PsrSimpleCacheInterface;

class SimpleCache implements PsrSimpleCacheInterface {

	use ConvertDateIntervalToIntegerTrait, ToArrayTrait, KeyValidatorTrait;

	private StorageInterface $storage;
	private array $used_keys = [];

	public function __construct(StorageInterface $storage = null) {
		$this->storage = $storage;
	}

	public function has( $key ): bool {
		return $this->get( $key, false ) !== false;
	}

	public function get( $key, $default = null ) {
		$this->assertKeyIsValid( $key );
		$this->addUsedKey( $key );

		$value = $this->storage->get( $key );
		return $value ?: $default;
	}

	public function set( $key, $value, $ttl = null ): bool {
		$this->assertKeyIsValid( $key );
		$this->addUsedKey( $key );

		if ($ttl instanceof DateInterval) {
			$ttl = $this->convertDateIntervalToInteger($ttl);
		}

		return $this->storage->set( (string)$key, \is_object($value) ? clone $value : $value, (int)$ttl );
	}

	public function delete( $key ): bool {
		$this->assertKeyIsValid( $key );

		if (!\array_key_exists($key, $this->usedKeys())) {
			return true;
		}

		$this->deleteUsedKey( $key );
		return $this->storage->delete( $key );
	}

	public function getMultiple( $keys, $default = null ): iterable {
		if (!\is_iterable($keys)) {
			throw new SimpleCacheInvalidArgumentException( 'Cache keys must be array or Traversable' );
		}

		$gen = function () use ($keys, $default) {
			foreach ($keys as $key) {
				yield $key => $this->get( $key, $default );
			}
		};

		return $gen();
	}

	public function setMultiple( $values, $ttl = null ): bool {
		if (!\is_iterable($values)) {
			throw new SimpleCacheInvalidArgumentException( 'Cache values must be array or Traversable' );
		}

		$success = true;
		foreach ( $values as $key => $value ) {
			if ( $this->set($key, $value, $ttl ) ) {
				continue;
			}
			$success = false;
		}

		return $success;
	}

	public function deleteMultiple( $keys ): bool {
		if (!\is_iterable($keys)) {
			throw new SimpleCacheInvalidArgumentException( 'Cache keys must be array or Traversable' );
		}

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

	public function clear(): bool {
		return $this->deleteMultiple( $this->usedKeys() );
	}

	private function assertKeyIsValid( $key ): void {
		try {
			$this->validateKey($key);
		} catch (\InvalidArgumentException $e) {
			throw new SimpleCacheInvalidArgumentException($e->getMessage(), $e->getCode());
		}
	}

	private function addUsedKey( $key ): void {
		$this->used_keys[ $key ] = $key;
	}

	private function deleteUsedKey( $key ): void {
		unset( $this->used_keys[ $key ] );
	}

	private function usedKeys(): array {
		return $this->used_keys;
	}
}
