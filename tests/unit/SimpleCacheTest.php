<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\Unit;

use ItalyStrap\Cache\Exceptions\SimpleCacheInvalidArgumentException;
use ItalyStrap\Cache\SimpleCache;
use ItalyStrap\Cache\Expiration;
use ItalyStrap\Tests\CommonTrait;
use ItalyStrap\Tests\TestCase;
use Prophecy\Argument;
use Psr\SimpleCache\CacheInterface;

class SimpleCacheTest extends TestCase {

	use CommonTrait;

	public function makeInstance(): SimpleCache {
		$sut = new SimpleCache($this->makeCache());
		$this->assertInstanceOf( CacheInterface::class, $sut, '' );
		return $sut;
	}

	public function invalidKeys(): iterable {

		yield 'null key'	=> [
			1
		];

		yield 'integer key'	=> [
			1
		];

		yield 'empty key'	=> [
			''
		];

//		return [
//			['bar{Foo'],
//			['bar}Foo'],
//			['bar(Foo'],
//			['bar)Foo'],
//			['bar/Foo'],
//			['bar\Foo'],
//			['bar@Foo'],
//			['bar:Foo']
//		];
	}

	/**
	 * @test
	 * @dataProvider invalidKeys()
	 */
	public function itShouldThrownExceptionIfGetKeyIs($key) {
		$sut = $this->makeInstance();
		$this->expectException(SimpleCacheInvalidArgumentException::class);
		$value = $sut->get($key);
	}

	/**
	 * @test
	 * @dataProvider invalidKeys()
	 */
	public function itShouldThrownExceptionIfSetKeyIs($key) {
		$sut = $this->makeInstance();
		$this->expectException( SimpleCacheInvalidArgumentException::class);
		$value = $sut->set($key, 'val');
	}

	/**
	 * @test
	 * @dataProvider invalidKeys()
	 */
	public function itShouldThrownExceptionIfHasKeyIs($key) {
		$sut = $this->makeInstance();
		$this->expectException( SimpleCacheInvalidArgumentException::class);
		$value = $sut->has($key);
	}

	/**
	 * @test
	 * @dataProvider invalidKeys()
	 */
	public function itShouldThrownExceptionIfDeleteKeyIs($key) {
		$sut = $this->makeInstance();
		$this->expectException( SimpleCacheInvalidArgumentException::class);
		$value = $sut->delete($key);
	}

	/**
	 * @test
	 */
	public function itShouldGetTransientValue() {

		$this->cache
			->get('key')
			->willReturn('value');

		$sut = $this->makeInstance();
		$value = $sut->get('key');
		$this->assertSame('value', $value, '');
	}

	/**
	 * @test
	 */
	public function itShouldGetZeroAsValue() {

		$this->cache
			->get('key')
			->willReturn(0);

		$sut = $this->makeInstance();
		$value = $sut->get('key');
		$this->assertSame(0, $value, '');
	}

	/**
	 * @test
	 */
	public function itShouldGetOneAsValue() {

		$this->cache
			->get('key')
			->willReturn(1);

		$sut = $this->makeInstance();
		$value = $sut->get('key');
		$this->assertSame(1, $value, '');
	}

	/**
	 * @test
	 */
	public function itShouldGetTransientValueReturnNullWhenNoValueIsStoredBecauseNullIsDefaultValue() {
		$sut = $this->makeInstance();
		$value = $sut->get('key');
		$this->assertNull($value, '');
	}

	/**
	 * @test
	 */
	public function itShouldGetCustomDefaultValue() {

		$this->cache
			->get('not-a-value-stored')
			->willReturn('default-value');

		$sut = $this->makeInstance();
		$value = $sut->get('not-a-value-stored', 'default-value');
		$this->assertSame('default-value', $value, '');
	}

	public function valueProviderForSet() {
		return [
			'custom value'	=>	[
				'custom-value'
			],
			'zero value'	=>	[
				0
			],
			'one value'	=>	[
				1
			],
			'negative value'	=>	[
				-1
			],
			'serialized obj'	=>	[
				\serialize( ( new \stdClass() ) )
			],
			'serialized array'	=>	[
				\serialize( ['key'=>'value'] )
			],
		];
	}

	/**
	 * @test
	 * @dataProvider valueProviderForSet()
	 */
	public function itShouldSetValue($value) {

		$this->cache
			->set('key', $value, Argument::type('int'))
			->willReturn(true);

		$this->cache
			->get('key')
			->willReturn($value);

		$sut = $this->makeInstance();
		$this->assertTrue($sut->set('key', $value), '');
		$this->assertSame($value, $sut->get('key'), '');
	}

	/**
	 * @test
	 */
	public function itShouldSetValueWithDateintervalForTTL() {

		$date_interval = new \DateInterval('PT2S');

		$this->cache
			->set('key', 'value', Argument::type('int'))
			->willReturn(true);

		$this->cache
			->get('key')
			->willReturn('value');

		$sut = $this->makeInstance();
		$sut->set('key', 'value', $date_interval);
		$this->assertSame('value', $sut->get('key'), '');
	}

	/**
	 * @test
	 */
	public function setCouldReturnFalse() {

		$this->cache
			->set('key', 'value', Argument::type('int'))
			->willReturn(false);

		$sut = $this->makeInstance();
		$has_set = $sut->set('key', 'value');
		$this->assertFalse($has_set, '');
	}

	/**
	 * @test
	 */
	public function itShouldHasValue() {

		$this->cache
			->set('key', 'some-value', Argument::type('int'))
			->willReturn(true);

		$this->cache
			->get( 'key' )
			->willReturn(true);

		$sut = $this->makeInstance();
		$sut->set('key', 'some-value');
		$this->assertTrue($sut->has('key'), '');
	}

	/**
	 * @test
	 */
	public function itShouldNotHasValue() {

		$this->cache
			->get( 'key' )
			->willReturn(false);

		$sut = $this->makeInstance();
		$this->assertFalse($sut->has('key'), '');
	}

	/**
	 * @t
	 */
	public function itShouldDeleteValue() {

		$this->cache
			->delete('key')
			->shouldBeCalledOnce();

		$this->cache
			->set('key', 'value')
			->shouldBeCalledOnce()
			->willReturn(true);

//		$this->storage
//			->get( 'key' )
//			->willReturn(false);

		$sut = $this->makeInstance();
		$sut->set('key', 'value');
//		$sut->delete('key');
//		$this->assertFalse($sut->has('key'), '');
	}

	public function multipleInvalidKeys() {
		return [
			'empty key'	=> [
				''
			],
			'string key'	=> [
				'key'
			],
			'int key'	=> [
				0
			],
			'bool key'	=> [
				true
			],
		];
	}

	/**
	 * @t
	 * @dataProvider multipleInvalidKeys()
	 */
	public function itShouldThrownErrorOnGetMultipleValuesWith($key) {
		$sut = $this->makeInstance();
		$this->expectException(SimpleCacheInvalidArgumentException::class);
		$this->expectExceptionMessageMatches('#Cache keys must be array or Traversable#');
		$multiple = $sut->getMultiple($key);
	}

	/**
	 * @t
	 * @dataProvider multipleInvalidKeys()
	 */
	public function itShouldThrownErrorOnSetMultipleValuesWith($key) {
		$sut = $this->makeInstance();
		$this->expectException(SimpleCacheInvalidArgumentException::class);
		$this->expectExceptionMessageMatches('#Cache values must be array or Traversable#');
		$multiple = $sut->setMultiple($key);
	}

	/**
	 * @t
	 * @dataProvider multipleInvalidKeys()
	 */
	public function itShouldThrownErrorOnDeleteMultipleValuesWith($key) {
		$sut = $this->makeInstance();
		$this->expectException(SimpleCacheInvalidArgumentException::class);
		$this->expectExceptionMessageMatches('#Cache keys must be array or Traversable#');
		$multiple = $sut->deleteMultiple($key);
	}

	public function multipleInvalidArrayKeys() {
		return [
			'empty key'	=> [
				['' => '']
			],
			'int key'	=> [
				[0 => 0]
			],
			'bool key'	=> [
				[true => 0]
			],
		];
	}

	/**
	 * @t
	 * @dataProvider multipleInvalidArrayKeys()
	 */
	public function itShouldThrownErrorOnGetMultipleValuesIfTheArrayKeysHas($key) {
		$sut = $this->makeInstance();
		$this->expectException(SimpleCacheInvalidArgumentException::class);
		$this->expectExceptionMessageMatches('#The \$key must be#');
		$multiple = $sut->getMultiple($key);
	}

	/**
	 * @t
	 * @dataProvider multipleInvalidArrayKeys()
	 */
	public function itShouldThrownErrorOnSetMultipleValuesIfTheArrayKeysHas($key) {
		$sut = $this->makeInstance();
		$this->expectException(SimpleCacheInvalidArgumentException::class);
		$this->expectExceptionMessageMatches('#The \$key must be#');
		$multiple = $sut->setMultiple($key);
	}

	/**
	 * @t
	 * @dataProvider multipleInvalidArrayKeys()
	 */
	public function itShouldThrownErrorOnDeleteMultipleValuesIfTheArrayKeysHas($key) {
		$sut = $this->makeInstance();
		$this->expectException(SimpleCacheInvalidArgumentException::class);
		$this->expectExceptionMessageMatches('#The \$key must be#');
		$multiple = $sut->deleteMultiple($key);
	}

	/**
	 * @t
	 */
	public function itShouldGetMultipleValuesIfKeyIsTraversable() {
		$traversable = new class implements \IteratorAggregate {
			public $key = 'key';
			public $key2 = 'key2';

			/**
			 * @inheritDoc
			 */
			public function getIterator() {
				return new \ArrayIterator($this);
			}
		};

		$values = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];

		$this->cache
			->get('key')
			->willReturn('some-other-value');

		$this->cache
			->get('key2')
			->willReturn('value 2');

		$sut = $this->makeInstance();
		$multiple = $sut->getMultiple($traversable);
		$this->assertSame($values, $multiple, '');
	}

	/**
	 * @t
	 */
	public function itShouldGetMultipleValuesIfKeyIsArrayObject() {
		$this->store = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];

		$this->cache
			->get('key')
			->willReturn('some-other-value');

		$this->cache
			->get('key2')
			->willReturn('value 2');

		$sut = $this->makeInstance();
		$multiple = $sut->getMultiple(new \ArrayObject(['key', 'key2']));
		$this->assertSame($this->store, $multiple, '');
	}

	/**
	 * @t
	 */
	public function itShouldGetMultipleValues() {
		$this->store = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];

		$this->cache
			->get('key')
			->willReturn('some-other-value');

		$this->cache
			->get('key2')
			->willReturn('value 2');

		$sut = $this->makeInstance();
		$multiple = $sut->getMultiple(\array_keys( $this->store ));
		$this->assertSame($this->store, $multiple, '');
	}

	/**
	 * @t
	 */
	public function itShouldReturnDefaultIfCacheKeysThatDoNotExistOrAreStaleWillHaveDefaultAsValue() {
		$this->store = [
			'key'	=> 'some-other-value',
			'key2'	=> false,
		];

		$this->cache
			->get('key')
			->willReturn('some-other-value');

		$this->cache
			->get('key2')
			->willReturn('default');

		$sut = $this->makeInstance();
		$multiple = $sut->getMultiple(\array_keys( $this->store ), 'default');
		$this->assertTrue('default' === $multiple['key2'], '');
	}

	/**
	 * @t
	 */
	public function setMultipleShouldReturnTrue() {
		$values = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];

		$this->cache
			->set('key', 'some-other-value', 0)
			->willReturn(true);

		$this->cache
			->set('key2', 'value 2', 0)
			->willReturn(true);

		$sut = $this->makeInstance();
		$has_set_multiple = $sut->setMultiple($values);
		$this->assertTrue($has_set_multiple, '');
	}

	/**
	 * @t
	 */
	public function setMultipleCouldReturnFalse() {
		$values = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];

		$this->cache
			->set('key', 'some-other-value', 0)
			->willReturn(false);

		$this->cache
			->set('key2', 'value 2', 0)
			->willReturn(false);

		$sut = $this->makeInstance();
		$has_set_multiple = $sut->setMultiple($values);
		$this->assertFalse($has_set_multiple, '');
	}

	/**
	 * @t
	 */
	public function itShouldSetMultipleValues() {
		$values = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];

		$this->cache
			->set('key', 'some-other-value', 0)
			->willReturn(true);

		$this->cache
			->set('key2', 'value 2', 0)
			->willReturn(true);

		$this->cache
			->get('key')
			->willReturn('some-other-value');

		$this->cache
			->get('key2')
			->willReturn('value 2');

		$sut = $this->makeInstance();
		$has_set_multiple = $sut->setMultiple($values);
		$this->assertTrue($has_set_multiple, '');
		$this->assertSame($values, $sut->getMultiple(\array_keys($values)), '');
	}

	/**
	 * @t
	 */
	public function itShouldSetMultipleReturnFalse() {

		$values = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];

		$this->cache
			->set('key', 'some-other-value', 0)
			->willReturn(false);

		$this->cache
			->set('key2', 'value 2', 0)
			->willReturn(false);

		$sut = $this->makeInstance();
		$return = $sut->setMultiple($values);
		$this->assertFalse($return, '');
	}

	/**
	 * @t
	 */
	public function itShouldDeleteMultipleValues() {
		$values = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];

		$this->cache
			->delete( 'key' )
			->willReturn(true);

		$this->cache
			->delete( 'key2' )
			->willReturn(true);

		$sut = $this->makeInstance();
		$return = $sut->deleteMultiple(\array_keys($values));
		$this->assertTrue($return, '');
	}

	/**
	 * @t
	 */
	public function itShouldDeleteMultipleReturnFalse() {

		$values = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];

		$this->cache
			->delete( 'key' )
			->willReturn(false);

		$this->cache
			->delete( 'key2' )
			->willReturn(false);

		$sut = $this->makeInstance();
		$return = $sut->deleteMultiple(\array_keys($values));
		$this->assertFalse($return, '');
	}

	/**
	 * @t
	 */
	public function itShouldDeleteMultipleValuesWithArrayAccess() {
		$this->store = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];

		$this->cache
			->delete( 'key' )
			->willReturn(true);

		$this->cache
			->delete( 'key2' )
			->willReturn(true);

		$sut = $this->makeInstance();
		$return = $sut->deleteMultiple(new \ArrayObject(\array_keys($this->store)));
		$this->assertTrue($return, '');
	}

	/**
	 * @t
	 */
	public function itShouldClearCache() {

		$this->cache
			->set('key', 'some-other-value', 0)
			->willReturn(true);

		$this->cache
			->set('key2', 'value 2', 0)
			->willReturn(true);

		$this->cache
			->delete( 'key' )
			->willReturn(true);

		$this->cache
			->delete( 'key2' )
			->willReturn(true);

		$sut = $this->makeInstance();
		$sut->setMultiple([
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		]);

		$result = $sut->clear();
		$this->assertTrue($result, '');
	}

	/**
	 * @t
	 */
	public function itShouldBeTenSecondTTL() {
		$date = new \DateInterval('PT10S');

		$this->cache
			->set('key', 'value', 10)
			->willReturn(true);

		$sut = $this->makeInstance();
		$isSet = $sut->set('key', 'value', $date);

		$this->assertTrue($isSet, '');
	}
}
