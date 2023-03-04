<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\WPUnit;

use ItalyStrap\Cache\SimpleCache;
use ItalyStrap\Storage\BinaryTransient;
use ItalyStrap\Storage\Transient;
use ItalyStrap\Tests\CommonTrait;
use ItalyStrap\Tests\SimpleCacheTestTrait;
use ItalyStrap\Tests\WPTestCase;

class SimpleCacheTest extends WPTestCase {

	use CommonTrait;

	private function makeInstance(): SimpleCache {
		$sut = new SimpleCache(new BinaryTransient(new Transient()));
		return $sut;
	}

	public function createSimpleCache(): SimpleCache {
		return $this->makeInstance();
	}

	/**
	 * @test
	 */
	public function setTransient() {
		$this->makeInstance()->set( 'key', 'value' );
		$this->assertSame('value', \get_transient('key'), '');
	}

	/**
	 * @test
	 */
	public function getTransient() {
		\set_transient( 'key', 'value' );
		$this->assertSame('value', $this->makeInstance()->get('key'), '');
	}

	/**
	 */
	public function deleteTransient() {
		\set_transient( 'key', 'value' );
		$this->assertSame('value', \get_transient('key'), '');
		$this->makeInstance()->delete('key');
		$this->assertFalse(\get_transient('key'), '');
		$this->assertNull($this->makeInstance()->get('key'), '');
	}
}
