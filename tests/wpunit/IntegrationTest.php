<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\WPUnit;

use ItalyStrap\Cache\SimpleCache;
use ItalyStrap\Tests\WPTestCase;

class IntegrationTest extends WPTestCase {

	private function makeInstance(): SimpleCache
	{
		$sut = new SimpleCache();
		return $sut;
	}

	/**
	 * @test
	 */
	public function instanceOk(): void
	{
//		$sut = $this->makeInstance();
	}

	/**
	 * @test
	 */
//	public function setTransient() {
//		$this->simple_cache->set( 'key', 'value' );
//		$this->assertSame('value', \get_transient('key'), '');
//	}

	/**
	 * @test
	 */
//	public function getTransient() {
//		\set_transient( 'key', 'value' );
//		$this->assertSame('value', $this->simple_cache->get('key'), '');
//	}

	/**
	 * @test
	 */
//	public function deleteTransient() {
//		\set_transient( 'key', 'value' );
//		$this->simple_cache->delete('key');
//		$this->assertFalse(\get_transient('key'), '');
//	}
}
