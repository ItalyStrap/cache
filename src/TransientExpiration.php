<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

class TransientExpiration implements ExpirationInterface {

	public const TRANSIENT_TIMEOUT_KEY = '_transient_timeout_';

	private ClockInterface $clock;
	private string $key;
	private int $expirationTime = 0;

	public function __construct(ClockInterface $clock = null) {
		$this->clock = $clock ?? new class implements ClockInterface {

			public function now(): DateTimeImmutable {
				return new \DateTimeImmutable('now');
			}
		};
	}

	public function withKey(string $key) {
		$this->key = $key;
	}

	public function isValid(string $key): bool {
		// If the expiration time is 0 Transient consider it like a no expiration at all.
		if ($this->expirationTime === 0) {
			return true;
		}

		$timeout = \get_option(self::TRANSIENT_TIMEOUT_KEY . $this->key);
		return (int)$timeout > $this->clock->now()->getTimestamp();
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

		assert('$expiration instanceof \DateTimeInterface');
		$this->expirationTime = $expiration->getTimestamp() - \time();
	}

	/**
	 * @param int|\DateInterval|null $time
	 * @return void
	 */
	public function expiresAfter($time): void {
		$this->expirationTime = (int)$time;
	}

	public function expirationInSeconds(): int {
		return $this->expirationTime;
	}
}
