<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use ItalyStrap\Tests\ConvertDateIntervalToIntegerTrait;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface as PsrSimpleCacheInterface;

/**
 * @psalm-api
 */
class SimpleCacheBridge implements PsrSimpleCacheInterface {

	use ConvertDateIntervalToIntegerTrait, ToArrayTrait;

	private CacheItemPoolInterface $pool;

	public function __construct(CacheItemPoolInterface $pool) {
		$this->pool = $pool;
	}

	public function get($key, $default = null) {
		return $this->getMultiple([$key], $default)[$key];
	}

	public function set($key, $value, $ttl = null) {
		$item = $this->pool->getItem($key);
		$item->set($value);
//		$ttl = $this->convertDateIntervalToInteger($ttl);

		// The check is to allow SimpleCacheBridgeTest::testGetMultiple pass
//		if (\is_int($ttl) && $ttl > 0) {
			$item->expiresAfter($ttl);
//		}

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
		$values = [];
		/**
		 * @var string $key
		 * @var CacheItemInterface $item
		 */
		foreach ($this->pool->getItems($keys) as $key => $item) {
			$values[$key] = $item->get() ?? $default;
		}

		return $values;
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
}
