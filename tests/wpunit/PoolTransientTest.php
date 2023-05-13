<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\WPUnit;

use ItalyStrap\Cache\Pool;
use ItalyStrap\Cache\Expiration;
use ItalyStrap\Storage\BinaryCacheDecorator;
use ItalyStrap\Storage\Transient;
use ItalyStrap\Tests\CachePoolTestTrait;
use ItalyStrap\Tests\CommonTrait;
use ItalyStrap\Tests\WPTestCase;
use Psr\Cache\CacheItemPoolInterface;

class PoolTransientTest extends WPTestCase
{

    use CommonTrait;
    use CachePoolTestTrait {
        CachePoolTestTrait::testBasicUsageWithLongKey as basicUsageWithLongKeyTrait;
    }

    /**
     * @type array with functionName => reason.
     */
    private array $skippedTests = [
//      'testBasicUsageWithLongKey' => 'Max length accepted for the key is 180 chars.',

//      'testItemModifiersReturnsStatic' => 'Void adapter does not save items.',
//      'testGetItem' => 'Void adapter does not save items.',
//      'testGetItems' => 'Void adapter does not save items.',
//      'testGetItemsEmpty' => 'Void adapter does not save items.',
//      'testHasItem' => 'Void adapter does not save items.',
//      'testClear' => 'Void adapter does not save items.',
//      'testClearWithDeferredItems' => 'Void adapter does not save items.',
//      'testDeleteItem' => 'Void adapter does not save items.',
//      'testDeleteItems' => 'Void adapter does not save items.',
//      'testSave' => 'Void adapter does not save items.',
//      'testSaveExpired' => 'Void adapter does not save items.',
//      'testSaveWithoutExpire' => 'Cache should have retrieved the items',
//      'testDeferredSave' => 'Void adapter does not save items.',
//      'testDeferredExpired' => 'Void adapter does not save items.',
//      'testDeleteDeferredItem' => 'Void adapter does not save items.',

//      'testDeferredSaveWithoutCommit' =>
//          'A deferred item should automatically be committed on CachePool::__destruct().',

//      'testCommit' => 'Void adapter does not save items.',
//      'testExpiration' => 'Void adapter does not save items.',
//      'testExpiresAt' => 'Void adapter does not save items.',
//      'testExpiresAtWithNull' => 'Void adapter does not save items.',
//      'testExpiresAfterWithNull' => 'Void adapter does not save items.',
//      'testKeyLength' => 'Void adapter does not save items.',

//      'testGetItemInvalidKeys' => 'Void adapter does not save items.',
//      'testGetItemsInvalidKeys' => 'Void adapter does not save items.',
//      'testHasItemInvalidKeys' => 'Void adapter does not save items.',

//      'testDeleteItemInvalidKeys' => 'Void adapter does not save items.',
//      'testDeleteItemsInvalidKeys' => 'Void adapter does not save items.',
//      'testDataTypeString' => 'Void adapter does not save items.',
//      'testDataTypeInteger' => 'Void adapter does not save items.',
//      'testDataTypeNull' => 'Void adapter does not save items.',
//      'testDataTypeFloat' => 'Void adapter does not save items.',
//      'testDataTypeBoolean' => 'Void adapter does not save items.',
//      'testDataTypeArray' => 'Void adapter does not save items.',
//      'testDataTypeObject' => 'Void adapter does not save items.',
//      'testBinaryData' => 'Void adapter does not save items.',
//      'testIsHit' => 'Void adapter does not save items.',
//      'testIsHitDeferred' => 'Void adapter does not save items.',
//      'testSaveDeferredWhenChangingValues' => 'Void adapter does not save items.',
//      'testSaveDeferredOverwrite' => 'Void adapter does not save items.',
//      'testSavingObject' => 'Void adapter does not save items.',
//      'testHasItemReturnsFalseWhenDeferredItemIsExpired' => 'Void adapter does not save items.',
    ];

    public function makeInstance(): CacheItemPoolInterface
    {
        $sut = new Pool(new BinaryCacheDecorator(new Transient()), new Expiration());
        return $sut;
    }

    /**
     * @test
     */
    public function basicImplementation(): void
    {
        $expected = \array_fill(0, 2, 'Some value ');
        $pool_was_called = false;

        $pool = $this->makeInstance();
        $item = $pool->getItem($this->cache_key);
        $this->assertFalse($item->isHit(), '');

        if (!$item->isHit()) {
            $item->set($expected);
            $pool_was_called = $pool->save($item);
        }

        $this->assertSame($expected, $item->get(), '');
        $this->assertTrue($pool_was_called, '');
        $this->assertSame($this->cache_key, $item->getKey(), '');
        $this->assertSame(\get_transient($this->cache_key), $item->get(), '');

        $pool->clear();
        $this->assertFalse(\get_transient($this->cache_key), '');
    }

    public function testVerifyValueIsInTransient()
    {
        $data = 'Some data';
        $key = 'key';

        \set_transient($key, $data, -10);

        $sut = $this->makeInstance();
        $item = $sut->getItem($key);
        if (null === ( $value = $item->get() )) {
        }

        $this->assertSame($value, null, 'Value should be expired');
    }

    public function testBasicUsageWithLongKey()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->basicUsageWithLongKeyTrait();
    }

    /**
     * @dataProvider invalidKeys
     */
    public function testGetItemsInvalidKeys($key)
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->expectException('Psr\Cache\InvalidArgumentException');
        $items = $this->cache->getItems(['key1', $key, 'key2']);
        foreach ($items as $item) {
        }
    }

    /**
     * @test
     */
    public function expirationToZero(): void
    {
        $expected = \array_fill(0, 2, 'Some value ');
        $pool_was_called = false;

        $pool = $this->makeInstance();
        $item = $pool->getItem($this->cache_key);
        $item->set($expected);
        $item->expiresAfter(0);
        $pool_was_called = $pool->save($item);

        $this->assertSame(null, $item->get(), '');
        $this->assertTrue($pool_was_called, '');
        $this->assertSame($this->cache_key, $item->getKey(), '');
        $this->assertSame(\get_transient($this->cache_key), false, '');
    }
}
