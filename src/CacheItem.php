<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use ItalyStrap\Storage\CacheInterface;
use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface {

	private string $key;
	private bool $isHit;

	/**
	 * @var mixed $value
	 */
	private $value;
	private ExpirationInterface $expiration;

	public function __construct(string $key, CacheInterface $storage, ExpirationInterface $expiration) {
		$this->key = $key;
		$this->value = $storage->get($key);
		$this->isHit = (bool)$this->value;
		$expiration->withKey($key);
		$this->expiration = $expiration;
	}

	public function getKey(): string {
		return $this->key;
	}

	public function get() {
		if ($this->isHit()) {
			return $this->value;
		}

		return null;
	}

	public function isHit(): bool {
		return $this->isHit && $this->expiration->isValid($this->getKey());
	}

	public function set($value): self {
		$this->value = $value;
		$this->isHit = true;
		return $this;
	}

	public function expiresAt($expiration): self {
		$this->expiration->expiresAt($expiration);
		return $this;
	}

	public function expiresAfter($time): self {
		$this->expiration->expiresAfter($time);
		return $this;
	}
}
