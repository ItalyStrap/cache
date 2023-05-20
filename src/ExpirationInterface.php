<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

interface ExpirationInterface
{

    public const YEAR_IN_SECONDS = 31_622_400;
	public const MONTH_IN_SECONDS = 2_592_000;
	public const WEEK_IN_SECONDS = 604_800;
	public const DAY_IN_SECONDS = 86_400;
	public const HOUR_IN_SECONDS = 3_600;
	public const MINUTE_IN_SECONDS = 60;

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
