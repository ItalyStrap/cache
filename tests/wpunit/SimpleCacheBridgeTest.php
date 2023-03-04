<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\WPUnit;

use ItalyStrap\Cache\SimpleCacheBridge;
use ItalyStrap\Tests\CommonTrait;
use ItalyStrap\Tests\SimpleCacheTestTrait;
use ItalyStrap\Tests\WPTestCase;
use Psr\SimpleCache\CacheInterface;

class SimpleCacheBridgeTest extends WPTestCase {

	use CommonTrait, SimpleCacheTestTrait;

	private array $skippedTests = [
//		'testSet' => 'Not passed test',
//		'testSetTtl' => 'Not passed test',
//		'testSetExpiredTtl' => 'Not passed test',
//		'testGet' => 'Not passed test',
//		'testDelete' => 'Not passed test',
//		'testClear' => 'Not passed test',
//		'testSetMultiple' => 'Not passed test',
		'testSetMultipleWithIntegerArrayKey' => 'Not passed test',
//		'testSetMultipleTtl' => 'Not passed test',
//		'testSetMultipleExpiredTtl' => 'Not passed test',
//		'testSetMultipleWithGenerator' => 'Not passed test',
//		'testGetMultiple' => 'Not passed test',
		'testGetMultipleWithGenerator' => 'Not passed test',
//		'testDeleteMultiple' => 'Not passed test',
		'testDeleteMultipleGenerator' => 'Not passed test',
//		'testHas' => 'Not passed test',
		'testBasicUsageWithLongKey' => 'Not passed test',
//		'testGetInvalidKeys' => 'Not passed test',
//		'testGetMultipleInvalidKeys' => 'Not passed test',
		'testGetMultipleNoIterable' => 'Not passed test',
		'testSetInvalidKeys' => 'Not passed test',
		'testSetMultipleInvalidKeys' => 'Not passed test',
		'testSetMultipleNoIterable' => 'Not passed test',
		'testHasInvalidKeys' => 'Not passed test',
		'testDeleteInvalidKeys' => 'Not passed test',
		'testDeleteMultipleInvalidKeys' => 'Not passed test',
		'testDeleteMultipleNoIterable' => 'Not passed test',
		'testSetInvalidTtl' => 'Not passed test',
		'testSetMultipleInvalidTtl' => 'Not passed test',
		'testNullOverwrite' => 'Not passed test',
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
		'testObjectDoesNotChangeInCache' => 'Not passed test',
	];

	private function makeInstance(): CacheInterface {
		$sut = new SimpleCacheBridge((new TransientCachePoolTest())->makeInstance());
		return $sut;
	}

	public function createSimpleCache(): CacheInterface {
		return $this->makeInstance();
	}
}
