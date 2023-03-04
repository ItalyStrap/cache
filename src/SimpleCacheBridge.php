<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use Fig\Cache\KeyValidatorTrait;
use ItalyStrap\Cache\Exceptions\SimpleCacheInvalidArgumentException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\SimpleCache\CacheInterface as PsrSimpleCacheInterface;

/**
 * @psalm-api
 */
class SimpleCacheBridge implements PsrSimpleCacheInterface {

	use ToArrayTrait, KeyValidatorTrait;

	private CacheItemPoolInterface $pool;

	public function __construct(CacheItemPoolInterface $pool) {
		$this->pool = $pool;
	}

	public function get($key, $default = null) {
		try {
			return $this->pool->getItem($key)->get() ?? $default;
		} catch (InvalidArgumentException $e) {
			throw new SimpleCacheInvalidArgumentException($e->getMessage(), $e->getCode());
		}
	}

	public function set($key, $value, $ttl = null) {
		$item = $this->pool->getItem($key);
		$item->set($value);
		$item->expiresAfter($ttl);

		return $this->pool->save($item);
	}

	public function delete($key) {
		return $this->deleteMultiple([$key]);
	}

	public function clear() {
		return $this->pool->clear();
	}

	public function getMultiple($keys, $default = null): iterable {
		$keys = $this->toArray($keys);
		$this->assertKeysAreValid($keys);
		return $this->generateMultipleResultForGetItems($keys, $default);
	}

	public function setMultiple($values, $ttl = null) {
		$values = $this->toArray($values, 'values');

		$success = true;
		foreach ( $values as $key => $value ) {
			if ( $this->set((string)$key, $value, $ttl ) ) {
				continue;
			}
			$success = false;
		}

		return $success;
	}

	public function deleteMultiple($keys) {
		$keys = $this->toArray($keys);
		return $this->pool->deleteItems($keys);
	}

	public function has($key) {
		$item = $this->pool->getItem($key);
		return $item->isHit();
	}

	private function generateMultipleResultForGetItems(array $keys, $default): iterable {
		/**
		 * @var string $key
		 * @var CacheItemInterface $item
		 */
		foreach ($this->pool->getItems($keys) as $key => $item) {
			yield $key => $item->get() ?? $default;
		}
	}

	private function assertKeysAreValid( iterable $keys ): void {
		try {
			foreach ($keys as $key) {
				$this->validateKey($key);
			}
		} catch (InvalidArgumentException $e) {
			throw new SimpleCacheInvalidArgumentException($e->getMessage(), $e->getCode());
		}
	}
}
