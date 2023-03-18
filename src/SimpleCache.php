<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use ItalyStrap\Cache\Exceptions\SimpleCacheInvalidArgumentException;
use ItalyStrap\Storage\CacheInterface;
use Psr\SimpleCache\CacheInterface as PsrSimpleCacheInterface;

class SimpleCache implements PsrSimpleCacheInterface {

	use KeyValidatorTrait;

	private CacheInterface $storage;
	private array $used_keys = [];
	private array $type = [];
	private ExpirationInterface $expiration;

	public function __construct(CacheInterface $storage, ExpirationInterface $expiration) {
		$this->storage = $storage;
		$this->expiration = $expiration;
	}

	public function has( $key ): bool {
		return $this->get( $key ) !== null;
	}

	public function get( $key, $default = null ) {
		$this->assertKeyIsValid( $key );
		$this->addUsedKey( $key );

		/**
		 * This is a bit tricky because transient return false not as value but
		 * as if no value is stored, as usual...
		 * If no value is stored the array key does not exist, so it will return $default = null as value
		 * This should be almost safe.
		 * Normally you do something like this: `false === get_transient('some-key')`
		 * With this you simply call SimpleCache::has('some-key');
		 */
		$value = $this->storage->get( $key );
		if (\array_key_exists($key, $this->type) && $this->type[$key] === 'boolean') {
			return (bool)$value;
		}

		return $value ?: $default;
	}

	public function set( $key, $value, $ttl = null ): bool {
		$this->assertKeyIsValid( $key );
		$this->addUsedKey( $key );
		$this->addValueType( (string)$key, $value );

		try {
			$this->expiration->expiresAfter($ttl);
		} catch (\InvalidArgumentException $e) {
			throw new SimpleCacheInvalidArgumentException($e->getMessage(), $e->getCode());
		}

		$ttl = $this->expiration->expirationInSeconds();

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

		foreach ( $values as $key => $value ) {
			if ( $this->set($key, $value, $ttl ) ) {
				continue;
			}
			return false;
		}

		return true;
	}

	public function deleteMultiple( $keys ): bool {
		if (!\is_iterable($keys)) {
			throw new SimpleCacheInvalidArgumentException( 'Cache keys must be array or Traversable' );
		}

		/** @var string $key */
		foreach ( $keys as $key ) {
			if ( $this->delete( $key ) ) {
				continue;
			}
			return false;
		}

		return true;
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

	private function addValueType(string $key, $value): void {
		$this->type[$key] = \gettype($value);
	}
}
