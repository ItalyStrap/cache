<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

interface ExpirationInterface {

	public function withKey(string $key);

	public function isValid(string $key): bool;

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
