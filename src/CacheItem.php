<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface {

	private string $key;
	private bool $isHit;

	/**
	 * @var mixed $value
	 */
	private $value;
	private ExpirationInterface $expiration;

	public function __construct(string $key, ExpirationInterface $expiration) {
		$this->key = $key;
		$this->isHit = false;
		$expiration->withKey($key);
		$this->expiration = $expiration;
	}

	public function getKey(): string {
		return $this->key;
	}

	public function get() {
		if (!$this->isHit || !$this->expiration->isValid($this->getKey())) {
			return null;
		}

		return $this->value;
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
