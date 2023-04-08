<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\WPUnit\Storage;

use ItalyStrap\Storage\Transient;
use ItalyStrap\Tests\CommonTrait;
use ItalyStrap\Tests\TestCase;

class TransientTest extends TestCase
{

    use CommonTrait;
    // tests
    public function makeInstance(): Transient
    {
        return new Transient();
    }

    // tests
    public function testSomeFeature()
    {
    }
}
