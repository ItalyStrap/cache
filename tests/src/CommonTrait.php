<?php
declare(strict_types=1);

namespace ItalyStrap\Tests;

trait CommonTrait
{
    /**
     * @test
     */
    public function instanceOk(): void
    {
        $sut = $this->makeInstance();
    }

    public function createCachePool(): object
    {
        return $this->makeInstance();
    }

    public function createSimpleCache(): object
    {
        return $this->makeInstance();
    }
}
