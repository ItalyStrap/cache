<?php

declare(strict_types=1);

namespace ItalyStrap\Tests\Unit;

use ItalyStrap\Cache\SimpleCache;
use ItalyStrap\Storage\BinaryCacheDecorator;
use ItalyStrap\Storage\Transient;
use ItalyStrap\Tests\CommonTrait;
use ItalyStrap\Tests\SimpleCacheTestTrait;
use ItalyStrap\Tests\TestCase;
use Psr\SimpleCache\CacheInterface;

class SimpleCacheTransientTest extends TestCase
{
    use CommonTrait, SimpleCacheTestTrait;

    public function makeInstance(): SimpleCache
    {
        $sut = new SimpleCache(new BinaryCacheDecorator(new Transient()));
        $this->assertInstanceOf(CacheInterface::class, $sut, '');
        return $sut;
    }

    public function testBinaryImplementation()
    {
        $data = '';
        for ($i = 0; $i < 256; $i++) {
            $data .= chr($i);
        }

        $key = 'key';
        \add_filter("transient_$key", function ($value) use ($key, $data) {
            $generated_key = \md5(BinaryCacheDecorator::class . $key);
            return [$generated_key => base64_encode($data)];
        });

        $sut = $this->makeInstance();
        if (null === ( $value = $sut->get($key) )) {
            $sut->set($key, $data);
            $this->fail('It should not be reached.');
        }

        $this->assertSame($data, $value, '');
    }

    public function testBasicUsageWithLongKey()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $key = str_repeat('a', 172);

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
    public function testGetMultipleInvalidKeys($key)
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->expectException('Psr\SimpleCache\InvalidArgumentException');
        $result = $this->cache->getMultiple(['key1', $key, 'key2']);
        foreach ($result as $k => $v) {
        }
    }
}
