<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use DateTimeInterface;
use Psr\Clock\ClockInterface;

trait ExpirationTrait
{

    private ClockInterface $clock;
    private ?DateTimeInterface $dateTime;

    /**
     * @param ClockInterface|null $clock
     * @return void
     */
    private function initClock(?ClockInterface $clock = null): void
    {
        $this->clock = $clock ?? new class implements ClockInterface {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable('now');
            }
        };

        $this->dateTime = null;
    }

    /**
     * @param DateTimeInterface|null $expiration
     * @return void
     * @psalm-suppress UnusedMethod
     */
    private function validateExpiration($expiration): void
    {
        if (\is_null($expiration)) {
            $this->dateTime = new \DateTimeImmutable('now +1 year');
            return;
        }

        if ($expiration instanceof DateTimeInterface) {
            $this->dateTime = $expiration;
            return;
        }

        throw new \InvalidArgumentException(\sprintf(
            '$expiration must be null or an instance of DateTimeInterface, got %s',
            \gettype($expiration)
        ));
    }

    /**
     * @param int|\DateInterval|null $time
     *   The period of time from the present after which the item MUST be considered
     *   expired. An integer parameter is understood to be the time in seconds until
     *   expiration. If null is passed explicitly, a default value MAY be used.
     *   If none is set, the value should be stored permanently or for as long as the
     *   implementation allows.
     * @return void
     * @throws \Exception
     */
    private function validateTime($time): void
    {
        if (\is_null($time)) {
            $this->dateTime = new \DateTimeImmutable('now +1 year');
            return;
        }

        // PSR requirement says that 0 means expired value
        if ($time === 0) {
            $time--;
        }

        if (\is_int($time)) {
            $this->dateTime = new \DateTimeImmutable('now +' . $time . ' seconds');
            return;
        }

        /** @psalm-suppress RedundantConditionGivenDocblockType */
        if ($time instanceof \DateInterval) {
            $this->dateTime = (new \DateTimeImmutable())->add($time);
            return;
        }

        throw new \InvalidArgumentException(\sprintf(
            '$time must be null, integer or an instance of DateInterval, got %s',
            \gettype($time)
        ));
    }

    private function expirationInSeconds(): int
    {
        return $this->dateTime ? $this->dateTime->getTimestamp() - $this->clock->now()->getTimestamp() : 31_536_000;
    }
}
