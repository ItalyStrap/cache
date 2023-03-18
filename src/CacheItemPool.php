<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use Fig\Cache\BasicPoolTrait;
use ItalyStrap\Storage\CacheInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

/**
 * @psalm-api
 */
class CacheItemPool implements CacheItemPoolInterface {

	use BasicPoolTrait, KeyValidatorTrait;

	/** @var array<string, CacheItemInterface> $saved */
	private array $saved = [];

	/** @var array<string, CacheItemInterface> $deferred */
	protected $deferred = [];
	private CacheInterface $storage;
	private ExpirationInterface $expiration;

	/**
	 * @param CacheInterface $storage
	 * @param ExpirationInterface $expiration
	 * @psalm-suppress PossiblyUnusedMethod
	 */
	public function __construct(CacheInterface $storage, ExpirationInterface $expiration) {
		$this->storage = $storage;
		$this->expiration = $expiration;
	}

	public function __destruct() {
		$this->commit();
	}

	public function getItem($key): CacheItemInterface {
		$this->validateKey($key);
		/** @psalm-suppress RedundantCastGivenDocblockType */
		if ($this->hasDeferredItem((string)$key)) {
			return clone $this->deferred[$key];
		}

		if (\array_key_exists($key, $this->saved) && $this->saved[$key]->isHit()) {
			return clone $this->saved[$key];
		}

		/** @psalm-suppress RedundantCastGivenDocblockType */
		return new CacheItem((string)$key, $this->storage, $this->expiration);
	}

	public function getItems(array $keys = []): iterable {
		foreach ($keys as $key) {
			yield $key => $this->getItem($key);
		}
	}

	public function hasItem($key): bool {
		$this->validateKey($key);

		// check deferred items first
		/** @psalm-suppress RedundantCastGivenDocblockType */
		if ($this->hasDeferredItem((string)$key)) {
			return true;
		}

		return \array_key_exists($key, $this->saved) && $this->saved[$key]->isHit();
	}

	public function saveDeferred(CacheItemInterface $item): bool {
		$this->deferred[$item->getKey()] = $item;
		return true;
	}

	/**
	 * @param array<array-key, mixed> $items
	 * @return bool
	 */
	public function deleteItems(array $items): bool {
		$has_value = false;

		if (empty($items)) {
			return true;
		}

		/** @var mixed $item */
		foreach ($items as $item) {
			$this->validateKey($item);
			if ($this->hasDeferredItem((string)$item)) {
				unset($this->deferred[(string)$item]);
				continue;
			}

			if (!\array_key_exists((string)$item, $this->saved)) {
				$has_value = true;
				continue;
			}

			$has_value = $this->storage->delete((string)$item);

			if ($has_value) {
				unset($this->saved[(string)$item]);
			}
		}

		return $has_value;
	}

	public function clear(): bool {
		/** @psalm-suppress InvalidCatch */
		try {
			$this->deleteItems(\array_keys($this->saved));
		} catch (InvalidArgumentException $e) {
			return false;
		}
		$this->saved = [];
		$this->deferred = [];

		return true;
	}

	protected function write(array $items): bool {
		foreach ($items as $item) {
			$key = $item->getKey();
			$this->expiration->withKey($key);
			$ttl = $this->expiration->expirationInSeconds();
			// @todo May add \InvalidArgumentException
			$has_value = $this->storage->set($key, $item->get(), $ttl);

			if ($has_value) {
				$this->saved[$key] = $item;
			}
		}

		return true;
	}

	private function hasDeferredItem(string $key): bool {
		return \array_key_exists($key, $this->deferred) && $this->deferred[$key]->isHit();
	}
}
