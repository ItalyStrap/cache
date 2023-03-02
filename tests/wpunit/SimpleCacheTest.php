<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\WPUnit;

use ItalyStrap\Cache\SimpleCache;
use ItalyStrap\Tests\CommonTrait;
use ItalyStrap\Tests\WPTestCase;

class SimpleCacheTest extends WPTestCase {

	use CommonTrait;

	private function makeInstance(): SimpleCache {
		$sut = new SimpleCache();
		return $sut;
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
	 * @test
	 */
	public function deleteTransient() {
		\set_transient( 'key', 'value' );
		$this->assertSame('value', \get_transient('key'), '');
		$this->makeInstance()->delete('key');
		$this->assertFalse(\get_transient('key'), '');
		$this->assertNull($this->makeInstance()->get('key'), '');
	}
}
