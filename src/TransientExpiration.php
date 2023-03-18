<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

/**
 * @psalm-api
 */
class TransientExpiration implements ExpirationInterface {

	public const TRANSIENT_TIMEOUT_KEY = '_transient_timeout_';
	public const YEAR_IN_SECONDS = 31_536_000;

	private ClockInterface $clock;
	private ?\DateTimeInterface $expiration;

	public function __construct(ClockInterface $clock = null) {
		$this->clock = $clock ?? new class implements ClockInterface {
			public function now(): DateTimeImmutable {
				return new \DateTimeImmutable('now');
			}
		};

		$this->expiration = null;
	}

	public function withKey(string $key): void {}

	public function isValid(string $key): bool {
		if ($this->expirationInSeconds() > 0) {
			return true;
		}

		$timeout = (int)\get_option(self::TRANSIENT_TIMEOUT_KEY . $key);
		return $timeout > $this->clock->now()->getTimestamp();
	}

	/**
	 * @param \DateTimeInterface|null $expiration
	 * @return void
	 */
	public function expiresAt($expiration): void {
		if (\is_null($expiration)) {
			$this->expiration = new \DateTimeImmutable('now +1 year');
			return;
		}

		if ($expiration instanceof \DateTimeInterface) {
			$this->expiration = $expiration;
			return;
		}

		throw new \InvalidArgumentException(\sprintf(
			'$expiration must be null or an instance of DateTimeInterface, got %s',
			\gettype($expiration)
		));
	}

	public function expiresAfter($time): void {
		if (\is_null($time)) {
			$this->expiration = new \DateTimeImmutable('now +1 year');
			return;
		}

		// PSR requirement says that 0 means expired value
		if ($time === 0) {
			$time--;
		}

		if (\is_int($time)) {
			$this->expiration = new \DateTimeImmutable('now +' . $time . ' seconds');
			return;
		}

		/** @psalm-suppress RedundantConditionGivenDocblockType */
		if ($time instanceof \DateInterval) {
			$this->expiration = (new \DateTimeImmutable())->add($time);
			return;
		}

		throw new \InvalidArgumentException(\sprintf(
			'$time must be null, integer or an instance of DateInterval, got %s',
			\gettype($time)
		));
	}

	public function expirationInSeconds(): int {
		return $this->expiration ? $this->expiration->getTimestamp() - $this->clock->now()->getTimestamp() : 31_536_000;
	}
}
