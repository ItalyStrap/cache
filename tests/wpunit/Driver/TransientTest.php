<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\WPUnit\Driver;

use ItalyStrap\Storage\Transient;
use ItalyStrap\Tests\CommonTrait;
use ItalyStrap\Tests\WPTestCase;

class TransientTest extends WPTestCase {

	use CommonTrait;

	private function makeInstance(): Transient {
		$sut = new Transient();
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
	public function updateTransient() {
		$this->makeInstance()->update( 'key', 'value' );
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
		$this->assertFalse($this->makeInstance()->get('key'), '');
	}
}
