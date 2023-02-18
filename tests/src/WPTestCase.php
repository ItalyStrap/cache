<?php
declare(strict_types=1);

namespace ItalyStrap\Tests;

use Codeception\TestCase\WPTestCase as WPUnit;
use PHPUnit\Util\Test as TestUtil;

class WPTestCase extends WPUnit {

	/**
	 * @var \WPUnitTester
	 */
	protected $tester;

	public function setUp(): void {
		// Before...
		parent::setUp();

		$this->assertTrue(\is_plugin_active('cache/index.php'), '');
		$this->assertTrue((bool)\did_action('plugins_loaded'), '');
		$this->assertFalse((bool)\did_action('not_valid_event_name'), '');

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
	public function canCreatePost(): void {
		$post = static::factory()->post->create_and_get(['post_excerpt' => 'Lorem Ipsum']);
		$this->assertInstanceOf(\WP_Post::class, $post);
	}
}
