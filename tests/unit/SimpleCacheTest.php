<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\Unit;

use ItalyStrap\Cache\Exceptions\SimpleCacheInvalidArgumentException;
use ItalyStrap\Cache\SimpleCache;
use ItalyStrap\Tests\CommonTrait;
use ItalyStrap\Tests\TestCase;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Traversable;

class SimpleCacheTest extends TestCase {

	use CommonTrait, SimpleCacheTrait;
}
