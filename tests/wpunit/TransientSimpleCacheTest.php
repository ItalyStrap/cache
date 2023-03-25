<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\WPUnit;

use ItalyStrap\Cache\SimpleCache;
use ItalyStrap\Cache\TransientExpiration;
use ItalyStrap\Storage\BinaryTransient;
use ItalyStrap\Storage\Transient;
use ItalyStrap\Tests\CommonTrait;
use ItalyStrap\Tests\SimpleCacheTestTrait;
use ItalyStrap\Tests\WPTestCase;

class TransientSimpleCacheTest extends WPTestCase {

	use CommonTrait, SimpleCacheTestTrait;

	private array $skippedTests = [
//		'testSet' => 'Not passed test',
//		'testSetTtl' => 'Not passed test',
//		'testSetExpiredTtl' => 'Not passed test',
//		'testGet' => 'Not passed test',
//		'testDelete' => 'Not passed test',
//		'testClear' => 'Not passed test',
//		'testSetMultiple' => 'Not passed test',
//		'testSetMultipleWithIntegerArrayKey' => 'Not passed test',
//		'testSetMultipleTtl' => 'Not passed test',
//		'testSetMultipleExpiredTtl' => 'Not passed test',
//		'testSetMultipleWithGenerator' => 'Not passed test',
//		'testGetMultiple' => 'Not passed test',
//		'testGetMultipleWithGenerator' => 'Not passed test',
//		'testDeleteMultiple' => 'Not passed test',
//		'testDeleteMultipleGenerator' => 'Not passed test',
//		'testHas' => 'Not passed test',
//		'testBasicUsageWithLongKey' => 'Not passed test',
//		'testGetInvalidKeys' => 'Not passed test',
//		'testGetMultipleInvalidKeys' => 'Not passed test',
//		'testGetMultipleNoIterable' => 'Not passed test',
//		'testSetInvalidKeys' => 'Not passed test',
//		'testSetMultipleInvalidKeys' => 'Not passed test',
//		'testSetMultipleNoIterable' => 'Not passed test',
//		'testHasInvalidKeys' => 'Not passed test',
//		'testDeleteInvalidKeys' => 'Not passed test',
//		'testDeleteMultipleInvalidKeys' => 'Not passed test',
//		'testDeleteMultipleNoIterable' => 'Not passed test',
//		'testSetInvalidTtl' => 'Not passed test',
//		'testSetMultipleInvalidTtl' => 'Not passed test',
//		'testNullOverwrite' => 'Not passed test',
//		'testDataTypeString' => 'Not passed test',
//		'testDataTypeInteger' => 'Not passed test',
//		'testDataTypeFloat' => 'Not passed test',
//		'testDataTypeBoolean' => 'Not passed test',
//		'testDataTypeArray' => 'Not passed test',
//		'testDataTypeObject' => 'Not passed test',
//		'testBinaryData' => 'Not passed test',
//		'testSetValidKeys' => 'Not passed test',
//		'testSetMultipleValidKeys' => 'Not passed test',
//		'testSetValidData' => 'Not passed test',
//		'testSetMultipleValidData' => 'Not passed test',
//		'testObjectAsDefaultValue' => 'Not passed test',
//		'testObjectDoesNotChangeInCache' => 'Not passed test',
	];

	private function makeInstance(): SimpleCache {
		$sut = new SimpleCache(new BinaryTransient(new Transient()), new TransientExpiration());
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
	 * @test
	 */
	public function getRealTransient() {
		\set_transient( 'key', false );
		$this->assertSame(false, \get_transient('key'), '');
	}

	/**
	 * @test
	 */
	public function getValueFromNotPrevSetTransient() {
		$sut = $this->makeInstance();
		$this->assertSame(null, $sut->get('some-not-stored-key'), 'Should return null if a value is not set');
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

	public function testImplementation() {
		$data = '';
		for ($i = 0; $i < 256; $i++) {
			$data .= chr($i);
		}

		$key = 'key';
		\add_filter("transient_$key", function ($value) use ($key, $data) {
			$generated_key = \md5(BinaryTransient::class . $key);
			return [$generated_key => base64_encode($data)];
		});

		$sut = $this->makeInstance();
		if ( null === ( $value = $sut->get( $key ) ) ) {
			$sut->set($key, $data);
			$this->fail('It should not be reached.');
		}

		$this->assertSame($data, $value, '');
	}

	public function testBasicUsageWithLongKey() {
		if (isset($this->skippedTests[__FUNCTION__])) {
			$this->markTestSkipped($this->skippedTests[__FUNCTION__]);
		}

		$key = str_repeat('a', 180);

		$this->assertFalse($this->cache->has($key));
		$this->assertTrue($this->cache->set($key, 'value'));

		$this->assertTrue($this->cache->has($key));
		$this->assertSame('value', $this->cache->get($key));

		$this->assertTrue($this->cache->delete($key));

		$this->assertFalse($this->cache->has($key));
	}

	/**
	 * @dataProvider invalidKeys
	 */
	public function testGetMultipleInvalidKeys($key) {
		if (isset($this->skippedTests[__FUNCTION__])) {
			$this->markTestSkipped($this->skippedTests[__FUNCTION__]);
		}

		$this->expectException('Psr\SimpleCache\InvalidArgumentException');
		$result = $this->cache->getMultiple(['key1', $key, 'key2']);
		foreach ($result as $k => $v) {
		}
	}
}
