<?php
declare(strict_types=1);

namespace ItalyStrap\Tests;

use Codeception\TestCase\WPTestCase;
use ItalyStrap\Cache\SimpleCache;
use WpunitTester;

class IntegrationTest extends WPTestCase {

	/**
	 * @var WpunitTester
	 */
	protected $tester;
	/**
	 * @var SimpleCache
	 */
	private $simple_cache;

	public function setUp(): void {
		// Before...
		parent::setUp();
		$this->simple_cache = new SimpleCache();

		// Your set up methods here.
	}

	public function tearDown(): void {
		// Your tear down methods here.

		// Then...
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function setTransient() {
		$this->simple_cache->set( 'key', 'value' );
		$this->assertSame('value', \get_transient('key'), '');
	}

	/**
	 * @test
	 */
	public function getTransient() {
		\set_transient( 'key', 'value' );
		$this->assertSame('value', $this->simple_cache->get('key'), '');
	}

	/**
	 * @test
	 */
	public function deleteTransient() {
		\set_transient( 'key', 'value' );
		$this->simple_cache->delete('key');
		$this->assertFalse(\get_transient('key'), '');
	}
}
