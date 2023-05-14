<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\Unit;

use ItalyStrap\Cache\Expiration;
use ItalyStrap\Cache\ExpirationInterface;
use ItalyStrap\Tests\CommonTrait;
use ItalyStrap\Tests\ExpirationTestTrait;
use ItalyStrap\Tests\TestCase;

class ExpirationTest extends TestCase
{
    use CommonTrait, ExpirationTestTrait;

    private function makeInstance(): ExpirationInterface
    {
        return new Expiration();
    }
}
