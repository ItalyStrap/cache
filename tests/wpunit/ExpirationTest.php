<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\Unit;

use ItalyStrap\Cache\Expiration;
use ItalyStrap\Cache\ExpirationInterface;
use ItalyStrap\Tests\CommonTrait;
use ItalyStrap\Tests\ExpirationTestTrait;
use ItalyStrap\Tests\WPTestCase;

class ExpirationTest extends WPTestCase
{
    use CommonTrait, ExpirationTestTrait;

    private function makeInstance(): ExpirationInterface
    {
        return new Expiration();
    }
}
