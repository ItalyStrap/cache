<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

/**
 * @psalm-api
 */
class InMemoryExpiration implements ExpirationInterface {

	private ClockInterface $clock;
	private int $expirationTime = 0;
	private DateTimeImmutable $freezeTime;

	public function __construct(ClockInterface $clock = null) {
		$this->clock = $clock ?? new class implements ClockInterface {

			public function now(): DateTimeImmutable {
				return new \DateTimeImmutable('now');
			}
		};

		$this->freezeTime = $this->clock->now();
	}

	public function withKey(string $key): void {
	}

	public function isValid(string $key): bool {
		// If the expiration time is 0 Transient consider it like a no expiration at all.
		if ($this->expirationTime === 0) {
			return true;
		}

		$timeout = $this->freezeTime->getTimestamp() + $this->expirationTime;
		return $timeout > $this->clock->now()->getTimestamp();
	}

	/**
	 * @param \DateTimeInterface|null $expiration
	 * @return void
	 */
	public function expiresAt($expiration): void {
		if (is_null($expiration)) {
			$this->expirationTime = (new \DateTimeImmutable('now +1 year'))->getTimestamp() - \time();
			return;
		}

		assert($expiration instanceof \DateTimeInterface);
		$this->expirationTime = $expiration->getTimestamp() - \time();
	}

	/**
	 * @param int|\DateInterval|null $time
	 * @return void
	 */
	public function expiresAfter($time): void {
		if ($time instanceof \DateInterval) {
			$this->expirationTime = (new \DateTimeImmutable())->add($time)->getTimestamp();
			return;
		}

		$this->expirationTime = (int)$time;
	}

	public function expirationInSeconds(): int {
		return $this->expirationTime;
	}
}
