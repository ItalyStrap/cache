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

	public function set($key, $value, $ttl = null): bool {
		$item = $this->setCommonItem($key, $value, $ttl);
		return $this->pool->save($item);
	}

	public function delete($key): bool {
		try {
			return $this->pool->deleteItem($key);
		} catch (InvalidArgumentException $e) {
			throw new SimpleCacheInvalidArgumentException($e->getMessage(), $e->getCode());
		}
	}

	public function clear(): bool {
		return $this->pool->clear();
	}

	public function getMultiple($keys, $default = null): iterable {
		if (!\is_iterable($keys)) {
			throw new SimpleCacheInvalidArgumentException( 'Cache keys must be array or Traversable' );
		}

		$gen = function () use ($keys, $default) {
			foreach ($keys as $key) {
				try {
					yield $key => $this->pool->getItem($key)->get() ?? $default;
				} catch (InvalidArgumentException $e) {
					throw new SimpleCacheInvalidArgumentException($e->getMessage(), $e->getCode());
				}
			}
		};

		return $gen();
	}

	public function setMultiple($values, $ttl = null): bool {
		if (!\is_iterable($values)) {
			throw new SimpleCacheInvalidArgumentException( 'Cache values must be array or Traversable' );
		}

		foreach ( $values as $key => $value ) {
			$item = $this->setCommonItem($key, $value, $ttl);
			$this->pool->saveDeferred($item);
		}

		return $this->pool->commit();
	}

	public function deleteMultiple($keys): bool {
		if (!\is_iterable($keys)) {
			throw new SimpleCacheInvalidArgumentException( 'Cache keys must be array or Traversable' );
		}

		$deleted = true;
		foreach ($keys as $key) {
			try {
				$deleted = $this->pool->deleteItem($key);
			} catch (InvalidArgumentException $e) {
				throw new SimpleCacheInvalidArgumentException($e->getMessage(), $e->getCode());
			}

			if (!$deleted) {
				return false;
			}
		}

		return $deleted;
	}

	public function has($key): bool {
		try {
			$item = $this->pool->getItem($key);
		} catch (InvalidArgumentException $e) {
			throw new SimpleCacheInvalidArgumentException($e->getMessage(), $e->getCode());
		}
		return $item->isHit();
	}

	private function setCommonItem($key, $value, $ttl): CacheItemInterface {
		try {
			$item = $this->pool->getItem($key);
		} catch (InvalidArgumentException $e) {
			throw new SimpleCacheInvalidArgumentException($e->getMessage(), $e->getCode());
		}
		$item->set(\is_object($value) ? clone $value : $value);
		try {
			$item->expiresAfter($ttl);
		} catch (\InvalidArgumentException $e) {
			throw new SimpleCacheInvalidArgumentException($e->getMessage(), $e->getCode());
		}
		return $item;
	}
}
