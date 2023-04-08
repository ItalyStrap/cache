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
}
