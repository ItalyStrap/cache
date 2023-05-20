<?php

declare(strict_types=1);

namespace ItalyStrap\Cache;

use ItalyStrap\Storage\BinaryCacheDecorator;
use ItalyStrap\Storage\Cache;
use ItalyStrap\Storage\CacheInterface;
use ItalyStrap\Storage\Transient;

/**
 * @psalm-api
 */
final class Factory
{
    public function makePoolTransient(): Pool
    {
        return $this->makeItemPool(new BinaryCacheDecorator(new Transient()));
    }

    public function makePool(): Pool
    {
        return $this->makeItemPool(new Cache());
    }

    public function makeSimpleCacheTransient(): SimpleCache
    {
        return $this->makeCache(new BinaryCacheDecorator(new Transient()));
    }

    public function makeSimpleCache(): SimpleCache
    {
        return $this->makeCache(new Cache());
    }

    public function makeSimpleCacheBridgeTransient(): SimpleCacheBridge
    {
        return new SimpleCacheBridge($this->makePoolTransient());
    }

    public function makeSimpleCacheBridge(): SimpleCacheBridge
    {
        return new SimpleCacheBridge($this->makePool());
    }

    private function makeItemPool(CacheInterface $driver): Pool
    {
        return new Pool(
            $driver,
            new Expiration()
        );
    }

    private function makeCache(CacheInterface $driver): SimpleCache
    {
        return new SimpleCache(
            $driver,
            new Expiration()
        );
    }
}
