<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

class TransientExpiration implements ExpirationInterface {

	public const TRANSIENT_TIMEOUT_KEY = '_transient_timeout_';
	public const YEAR_IN_SECONDS = 31536000;

	private ClockInterface $clock;
	private string $key;
	private int $expirationTime;
	private bool $expireMethodWasCalled = false;
	private ?int $defaultExpiration;
	/**
	 * @var DateTimeImmutable|\DateTimeInterface
	 */
	private $expiration;

	public function __construct(ClockInterface $clock = null, int $defaultExpiration = null) {
		$this->clock = $clock ?? new class implements ClockInterface {

			public function now(): DateTimeImmutable {
				return new \DateTimeImmutable('now');
			}
		};

		$this->defaultExpiration = $defaultExpiration;
	}

	public function withKey(string $key): void {
		$this->key = $key;
	}

	public function isValid(string $key): bool {
		if (!$this->expireMethodWasCalled) {
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
		$this->expireMethodWasCalled = true;
		if (\is_null($expiration)) {
			$this->expiration = new \DateTimeImmutable('now +1 year');
			return;
		}

		assert('$expiration instanceof \DateTimeInterface');
		$this->expiration = $expiration;
	}

	/**
	 * @param int|\DateInterval|null $time
	 * @return void
	 */
	public function expiresAfter($time): void {
		$this->expireMethodWasCalled = true;
		if (\is_null($time)) {
			$this->expiration = new \DateTime('now +1 year');
			return;
		}

		// PSR requirement says that 0 means expired value
		if ($time === 0) {
			$time--;
		}

		if (\is_int($time)) {
			$this->expiration = new \DateTime('now +' . $time . ' seconds');
			return;
		}

		assert('$time instanceof DateInterval');
		$expiration = new \DateTime();
		$expiration->add($time);
		$this->expiration = $expiration;
	}

	public function expirationInSeconds(): int {
		return $this->expiration ? $this->calcExpirationRemainingInSeconds($this->expiration) : 31536000;
	}

	/**
	 * @param \DateTimeInterface $expiration
	 * @return int
	 */
	private function calcExpirationRemainingInSeconds(\DateTimeInterface $expiration): int
	{
		return $expiration->getTimestamp() - \time();
	}

	/**
	 * @return \DateTime
	 */
	private function buildDateTimeObject(): \DateTime
	{
		return new \DateTime();
	}
}
