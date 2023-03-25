<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

interface ExpirationInterface {

	public const YEAR_IN_SECONDS = 31_622_400;

	public function withKey(string $key): void;

	public function isValid(): bool;

	/**
	 * @param \DateTimeInterface|null $expiration
	 * @return void
	 */
	public function expiresAt($expiration): void;

	/**
	 * @param int|\DateInterval|null $time
	 * @return void
	 */
	public function expiresAfter($time): void;

	public function expirationInSeconds(): int;
}
