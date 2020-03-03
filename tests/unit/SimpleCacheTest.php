<?php
declare(strict_types=1);

namespace ItalyStrap\Tests;

use Codeception\Test\Unit;
use ItalyStrap\Cache\Exceptions\InvalidArgumentSimpleCacheException;
use ItalyStrap\Cache\SimpleCache;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Traversable;

class SimpleCacheTest extends Unit {

	/**
	 * @var \UnitTester
	 */
	protected $tester;

	private $store = [];
	private $set_transient_return = true;
	private $delete_transient_return = true;
	private $ttl = 0;

	// phpcs:ignore
	protected function _before() {
		// phpcs:ignore
		\tad\FunctionMockerLe\define('get_transient', function ( string $key ) {
			return $this->store[ $key ] ?? false;
		});
		// phpcs:ignore
		\tad\FunctionMockerLe\define('set_transient', function ( string $key, $value, $ttl = 0 ) {
			$this->ttl = $ttl;
			$this->store[ $key ] = $value;
			return $this->set_transient_return;
		});
		// phpcs:ignore
		\tad\FunctionMockerLe\define('delete_transient', function ( string $key ) {
			unset($this->store[ $key ]);
			return $this->delete_transient_return;
		});
	}

	// phpcs:ignore
	protected function _after() {
		\tad\FunctionMockerLe\undefineAll([
			'get_transient',
			'set_transient',
			'delete_transient',
		]);
		$this->store = [];
		$this->set_transient_return = true;
		$this->delete_transient_return = true;
	}

	public function getInstance() {
		$sut = new SimpleCache();
		$this->assertInstanceOf( CacheInterface::class, $sut, '' );
		$this->assertInstanceOf( SimpleCache::class, $sut, '' );
		return $sut;
	}

	/**
	 * @test
	 */
	public function instanceOk() {
		$sut = $this->getInstance();
	}

	public function invalidKeys() {
		return [
			'integer key'	=> [
				1
			],
			'empty key'	=> [
				''
			],
		];
	}

	/**
	 * @test
	 * @dataProvider invalidKeys()
	 */
	public function itShouldThrownExceptionIfGetKeyIs($key) {
		$sut = $this->getInstance();
		$this->expectException(InvalidArgumentSimpleCacheException::class);
		$value = $sut->get($key);
	}

	/**
	 * @test
	 * @dataProvider invalidKeys()
	 */
	public function itShouldThrownExceptionIfSetKeyIs($key) {
		$sut = $this->getInstance();
		$this->expectException( InvalidArgumentSimpleCacheException::class);
		$value = $sut->set($key, 'val');
	}

	/**
	 * @test
	 * @dataProvider invalidKeys()
	 */
	public function itShouldThrownExceptionIfHasKeyIs($key) {
		$sut = $this->getInstance();
		$this->expectException( InvalidArgumentSimpleCacheException::class);
		$value = $sut->has($key);
	}

	/**
	 * @test
	 * @dataProvider invalidKeys()
	 */
	public function itShouldThrownExceptionIfDeleteKeyIs($key) {
		$sut = $this->getInstance();
		$this->expectException( InvalidArgumentSimpleCacheException::class);
		$value = $sut->delete($key);
	}

	/**
	 * @test
	 */
	public function itShouldGetTransientValue() {

		$this->store['key'] = 'value';

		$sut = $this->getInstance();
		$value = $sut->get('key');
		$this->assertSame('value', $value, '');
	}

	/**
	 * @test
	 */
	public function itShouldGetZeroValue() {

		$this->store['key'] = 0;

		$sut = $this->getInstance();
		$value = $sut->get('key');
		$this->assertSame(0, $value, '');
		$this->assertNotSame(false, $value, '');
	}

	/**
	 * @test
	 */
	public function itShouldGetTransientValueReturnNullWhenNoValueIsStoredBecauseNullIsDefaultValue() {
		$sut = $this->getInstance();
		$value = $sut->get('key');
		$this->assertNull($value, '');
	}

	/**
	 * @test
	 */
	public function itShouldGetCustomDefaultValue() {
		$sut = $this->getInstance();
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
		$this->store = [];
		$sut = $this->getInstance();
		$sut->set('key', $value);
		$this->assertSame($value, $sut->get('key'), '');
	}

	/**
	 * @test
	 */
	public function itShouldSetValueWithDateintervalForTTL() {
		$this->store = [];
		$sut = $this->getInstance();
		$sut->set('key', '$value', new \DateInterval('PT2S'));
		$this->assertSame('$value', $sut->get('key'), '');
	}

	/**
	 * @test
	 */
	public function setCouldReturnFalse() {
		$this->store = [];
		$this->set_transient_return = false;
		$sut = $this->getInstance();
		$has_set = $sut->set('key', 'value');
		$this->assertFalse($has_set, '');
	}

	/**
	 * @test
	 */
	public function itShouldHasValue() {
		$this->store = [];
		$sut = $this->getInstance();
		$sut->set('key', 'some-value');
		$this->assertTrue($sut->has('key'), '');
	}

	/**
	 * @test
	 */
	public function itShouldNotHasValue() {
		$this->store = [];
		$sut = $this->getInstance();
		$this->assertFalse($sut->has('key'), '');
	}

	/**
	 * @test
	 */
	public function itShouldDeleteValue() {
		$this->store = [
			'key'	=> 'some-other-value'
		];
		$sut = $this->getInstance();
		$sut->delete('key');
		$this->assertFalse($sut->has('key'), '');
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
	 * @test
	 * @dataProvider multipleInvalidKeys()
	 */
	public function itShouldThrownErrorOnGetMultipleValuesWith($key) {
		$sut = $this->getInstance();
		$this->expectException(InvalidArgumentSimpleCacheException::class);
		$this->expectExceptionMessageMatches('#Cache keys must be array or Traversable#');
		$multiple = $sut->getMultiple($key);
	}

	/**
	 * @test
	 * @dataProvider multipleInvalidKeys()
	 */
	public function itShouldThrownErrorOnSetMultipleValuesWith($key) {
		$sut = $this->getInstance();
		$this->expectException(InvalidArgumentSimpleCacheException::class);
		$this->expectExceptionMessageMatches('#Cache values must be array or Traversable#');
		$multiple = $sut->setMultiple($key);
	}

	/**
	 * @test
	 * @dataProvider multipleInvalidKeys()
	 */
	public function itShouldThrownErrorOnDeleteMultipleValuesWith($key) {
		$sut = $this->getInstance();
		$this->expectException(InvalidArgumentSimpleCacheException::class);
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
	 * @test
	 * @dataProvider multipleInvalidArrayKeys()
	 */
	public function itShouldThrownErrorOnGetMultipleValuesIfTheArrayKeysHas($key) {
		$sut = $this->getInstance();
		$this->expectException(InvalidArgumentSimpleCacheException::class);
		$this->expectExceptionMessageMatches('#The \$key must be#');
		$multiple = $sut->getMultiple($key);
	}

	/**
	 * @test
	 * @dataProvider multipleInvalidArrayKeys()
	 */
	public function itShouldThrownErrorOnSetMultipleValuesIfTheArrayKeysHas($key) {
		$sut = $this->getInstance();
		$this->expectException(InvalidArgumentSimpleCacheException::class);
		$this->expectExceptionMessageMatches('#The \$key must be#');
		$multiple = $sut->setMultiple($key);
	}

	/**
	 * @test
	 * @dataProvider multipleInvalidArrayKeys()
	 */
	public function itShouldThrownErrorOnDeleteMultipleValuesIfTheArrayKeysHas($key) {
		$sut = $this->getInstance();
		$this->expectException(InvalidArgumentSimpleCacheException::class);
		$this->expectExceptionMessageMatches('#The \$key must be#');
		$multiple = $sut->deleteMultiple($key);
	}

	/**
	 * @test
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


		$this->store = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];
		$sut = $this->getInstance();
		$multiple = $sut->getMultiple($traversable);
		$this->assertSame($this->store, $multiple, '');
	}

	/**
	 * @test
	 */
	public function itShouldGetMultipleValuesIfKeyIsArrayObject() {
		$this->store = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];
		$sut = $this->getInstance();
		$multiple = $sut->getMultiple(new \ArrayObject(['key', 'key2']));
		$this->assertSame($this->store, $multiple, '');
	}

	/**
	 * @test
	 */
	public function itShouldGetMultipleValues() {
		$this->store = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];
		$sut = $this->getInstance();
		$multiple = $sut->getMultiple(\array_keys( $this->store ));
		$this->assertSame($this->store, $multiple, '');
	}

	/**
	 * @test
	 */
	public function itShouldReturnDefaultIfCacheKeysThatDoNotExistOrAreStaleWillHaveDefaultAsValue() {
		$this->store = [
			'key'	=> 'some-other-value',
			'key2'	=> false,
		];
		$sut = $this->getInstance();
		$multiple = $sut->getMultiple(\array_keys( $this->store ), 'default');
		$this->assertTrue('default' === $multiple['key2'], '');
	}

	/**
	 * @test
	 */
	public function setMultipleShouldReturnTrue() {
		$values = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];
		$sut = $this->getInstance();
		$has_set_multiple = $sut->setMultiple($values);
		$this->assertTrue($has_set_multiple, '');
	}

	/**
	 * @test
	 */
	public function setMultipleCouldReturnFalse() {
		$values = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];
		$this->set_transient_return = false;
		$sut = $this->getInstance();
		$has_set_multiple = $sut->setMultiple($values);
		$this->assertFalse($has_set_multiple, '');
	}

	/**
	 * @test
	 */
	public function itShouldSetMultipleValues() {
		$values = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];
		$sut = $this->getInstance();
		$has_set_multiple = $sut->setMultiple($values);
		$this->assertSame($this->store, $sut->getMultiple(\array_keys($values)), '');
	}

	/**
	 * @test
	 */
	public function itShouldSetMultipleReturnFalse() {
		$this->set_transient_return = false;
		$values = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];
		$sut = $this->getInstance();
		$return = $sut->setMultiple($values);
		$this->assertFalse($return, '');
	}

	/**
	 * @test
	 */
	public function itShouldDeleteMultipleValues() {
		$this->store = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];
		$sut = $this->getInstance();
		$sut->deleteMultiple(\array_keys($this->store));
		$this->assertSame($this->store, [], '');
	}

	/**
	 * @test
	 */
	public function itShouldDeleteMultipleReturnFalse() {
		$this->delete_transient_return = false;
		$this->store = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];
		$sut = $this->getInstance();
		$return = $sut->deleteMultiple(\array_keys($this->store));
		$this->assertFalse($return, '');
	}

	/**
	 * @test
	 */
	public function itShouldDeleteMultipleReturnTrue() {
		$this->store = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];
		$sut = $this->getInstance();
		$return = $sut->deleteMultiple(\array_keys($this->store));
		$this->assertTrue($return, '');
	}

	/**
	 * @test
	 */
	public function itShouldDeleteMultipleValuesWithArrayAccess() {
		$this->store = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];
		$sut = $this->getInstance();
		$sut->deleteMultiple(new \ArrayObject(\array_keys($this->store)));
		$this->assertSame($this->store, [], '');
	}

	/**
	 * @test
	 */
	public function itShouldClearCache() {
		$sut = $this->getInstance();
		$sut->setMultiple([
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		]);

		$sut->clear();

		$this->assertSame([], $this->store, '');
	}

	/**
	 * @test
	 */
	public function itShouldBeTenSecondTTL() {
		$date = new \DateInterval('PT10S');

		$sut = $this->getInstance();
		$sut->set('key', 'value', $date);

		$this->assertSame($date->s, $this->ttl, '');
	}
}
