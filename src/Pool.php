<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

use Fig\Cache\BasicPoolTrait;
use ItalyStrap\Storage\CacheInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

/**
 * @psalm-api
 */
class Pool implements CacheItemPoolInterface
{

    use BasicPoolTrait, KeyValidatorTrait;

    /** @var array<string, CacheItemInterface> $saved */
    private array $saved = [];

    /** @var array<string, CacheItemInterface> $deferred */
    protected $deferred = [];
    private CacheInterface $driver;
    private ExpirationInterface $expiration;

    /**
     * @param CacheInterface $driver
     * @param ExpirationInterface $expiration
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __construct(CacheInterface $driver, ExpirationInterface $expiration)
    {
        $this->driver = $driver;
        $this->expiration = $expiration;
    }

    public function __destruct()
    {
        $this->commit();
    }

    public function getItem($key): CacheItemInterface
    {
        $this->validateKey($key);
        /** @psalm-suppress RedundantCastGivenDocblockType */
        if ($this->assertItemExists((string)$key, $this->deferred)) {
            return clone $this->deferred[$key];
        }

        /** @psalm-suppress RedundantCastGivenDocblockType */
        if ($this->assertItemExists((string)$key, $this->saved)) {
            return clone $this->saved[$key];
        }

        /** @psalm-suppress RedundantCastGivenDocblockType */
        return new Item((string)$key, $this->driver, $this->expiration);
    }

    public function getItems(iterable $keys = []): iterable
    {
        foreach ($keys as $key) {
            yield $key => $this->getItem($key);
        }
    }

    public function hasItem($key): bool
    {
        $this->validateKey($key);

        // check deferred items first
        /** @psalm-suppress RedundantCastGivenDocblockType */
        return $this->assertItemExists((string)$key, $this->deferred)
            || $this->assertItemExists((string)$key, $this->saved);
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[$item->getKey()] = $item;
        return true;
    }

    /**
     * @param array<array-key, mixed> $items
     * @return bool
     */
    public function deleteItems(iterable $items): bool
    {
        $has_value = false;

        if (empty($items)) {
            return true;
        }

        /** @var mixed $item */
        foreach ($items as $item) {
            $this->validateKey($item);
            if (\array_key_exists((string)$item, $this->deferred)) {
                unset($this->deferred[(string)$item]);
                continue;
            }

            if (!\array_key_exists((string)$item, $this->saved)) {
                $has_value = true;
                continue;
            }

            $has_value = $this->driver->delete((string)$item);

            if ($has_value) {
                unset($this->saved[(string)$item]);
            }
        }

        return $has_value;
    }

    public function clear(): bool
    {
        /** @psalm-suppress InvalidCatch */
        try {
            $this->deleteItems(\array_keys($this->saved));
        } catch (InvalidArgumentException $e) {
            return false;
        }
        $this->saved = [];
        $this->deferred = [];

        return true;
    }

    protected function write(array $items): bool
    {
        $has_value = true;
        foreach ($items as $item) {
            /** @psalm-suppress InvalidFunctionCall */
            if ($has_value = (bool)$item()) {
                $this->saved[$item->getKey()] = $item;
            }
        }

        return $has_value;
    }

    /**
     * @param string $key
     * @param CacheItemInterface[] $property
     * @return bool
     */
    private function assertItemExists(string $key, array $property): bool
    {
        return \array_key_exists($key, $property) && $property[$key]->isHit();
    }
}
