<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use Fig\Cache\BasicPoolTrait;
use Fig\Cache\KeyValidatorTrait;
use ItalyStrap\Storage\StorageInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheItemPool implements CacheItemPoolInterface {

	use BasicPoolTrait;
	use KeyValidatorTrait;

	/** @var array<string, CacheItemInterface> $saved */
	private array $saved = [];

	/** @var array<string, CacheItemInterface> $deferred */
	protected $deferred = [];
	private StorageInterface $storage;
	private ExpirationInterface $expiration;

	public function __construct(StorageInterface $storage, ExpirationInterface $expiration) {
		$this->storage = $storage;
		$this->expiration = $expiration;
	}

	public function __destruct() {
		$this->commit();
	}

	public function getItem($key): CacheItemInterface {
		$this->validateKey($key);
		if ($this->hasDeferredItem($key)) {
			return clone $this->deferred[$key];
		}

		if (\array_key_exists($key, $this->saved)) {
			return clone $this->saved[$key];
		}

		return new CacheItem($key, $this->expiration);
	}

	public function getItems(array $keys = array()): iterable {
		$collection = [];
		foreach ($keys as $key) {
			$collection[$key] = $this->getItem($key);
		}

		return $collection;
	}

	public function hasItem($key): bool {
		$this->validateKey($key);

		// check deferred items first
		if ($this->hasDeferredItem($key)) {
			return true;
		}

		return \array_key_exists($key, $this->saved) && $this->expiration->isValid($key);
	}

	public function saveDeferred(CacheItemInterface $item): bool {
		$this->deferred[$item->getKey()] = $item;
		return true;
	}

	public function deleteItems(array $keys): bool {
		$has_value = false;
		foreach ($keys as $key) {
			$this->validateKey($key);
			if ($this->hasDeferredItem($key)) {
				unset($this->deferred[$key]);
				continue;
			}

			if (!\array_key_exists($key, $this->saved)) {
				$has_value = true;
				continue;
			}

			$has_value = $this->storage->delete($key);

			if ($has_value) {
				unset($this->saved[$key]);
			}
		}

		return $has_value;
	}

	public function clear(): bool {
		$this->deleteItems(\array_keys($this->saved));
		$this->saved = [];
		$this->deferred = [];

		return true;
	}

	protected function write(array $items): bool {
		foreach ($items as $item) {
			$key = $item->getKey();
			$this->expiration->withKey($key);
			$ttl = $this->expiration->expirationInSeconds();
			$has_value = $this->storage->set($key, $item->get(), $ttl);

			if ($has_value) {
				$this->saved[$key] = $item;
			}
		}

		return true;
	}

	private function hasDeferredItem(string $key): bool {
		return \array_key_exists($key, $this->deferred) && $this->expiration->isValid($key);
	}
}
