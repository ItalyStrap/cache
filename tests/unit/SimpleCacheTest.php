<?php
declare(strict_types=1);

namespace ItalyStrap\Tests;

use Codeception\Test\Unit;
use ItalyStrap\SimpleCache\Exceptions\InvalidArgumentSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class SimpleCacheTest extends Unit {

	/**
	 * @var \UnitTester
	 */
	protected $tester;

	private $store = [];
	private $set_transient_return = true;
	private $delete_transient_return = true;

	protected function _before() {
		\tad\FunctionMockerLe\define('get_transient', function ( string $key ) {
			return $this->store[ $key ] ?? false;
		});
		\tad\FunctionMockerLe\define('set_transient', function ( string $key, $value, $ttl = 0 ) {
			$ttl = \intval($ttl);
			$this->store[ $key ] = $value;
			return $this->set_transient_return;
		});
		\tad\FunctionMockerLe\define('delete_transient', function ( string $key ) {
			unset($this->store[ $key ]);
			return $this->delete_transient_return;
		});
	}

	protected function _after() {
		\tad\FunctionMockerLe\undefineAll([
			'get_transient',
		]);
		$this->store = [];
	}

	public function getInstance() {
		$sut = new \ItalyStrap\SimpleCache\Cache();
		$this->assertInstanceOf(\Psr\SimpleCache\CacheInterface::class, $sut, '' );
		$this->assertInstanceOf(\ItalyStrap\SimpleCache\Cache::class, $sut, '' );
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
	public function itShouldGetTransientValueToFalse() {

		$this->store['key'] = 0;

		$sut = $this->getInstance();
		$value = $sut->get('key');
		$this->assertTrue(0 === $value, '');
	}

	/**
	 * @test
	 */
	public function itShouldGetDefaultValue() {
		$sut = $this->getInstance();
		$value = $sut->get('not-value-stored', 'default-value');
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

	public function getMultipleKeys() {
		return [
			'empty key'	=> [
				''
			],
			'string key'	=> [
				'key'
			],
			'int key'	=> [
				'key'
			],
		];
	}

	/**
	 * @test
	 * @dataProvider getMultipleKeys()
	 */
	public function itShouldThrownErrorOnGetMultipleValuesWith($key) {
		$sut = $this->getInstance();
		$this->expectException(InvalidArgumentSimpleCacheException::class);
		$multiple = $sut->getMultiple($key);
	}

	/**
	 * @test
	 * @dataProvider getMultipleKeys()
	 */
	public function itShouldThrownErrorOnSetMultipleValuesWith($key) {
		$sut = $this->getInstance();
		$this->expectException(InvalidArgumentSimpleCacheException::class);
		$multiple = $sut->setMultiple($key);
	}

	/**
	 * @test
	 * @dataProvider getMultipleKeys()
	 */
	public function itShouldThrownErrorOnDeleteMultipleValuesWith($key) {
		$sut = $this->getInstance();
		$this->expectException(InvalidArgumentSimpleCacheException::class);
		$multiple = $sut->deleteMultiple($key);
	}

	/**
	 * @test
	 */
	public function itShouldGetMultipleValuesIfKeyIsTraversable() {
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
	public function itShouldSetMultipleValues() {
		$values = [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		];
		$sut = $this->getInstance();
		$sut->setMultiple($values);
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
		$this->assertSame($this->store, [
			'key'	=> 'some-other-value',
			'key2'	=> 'value 2',
		], '');

		$sut->clear();

		$this->assertSame([], $this->store, '');
	}

	/**
	 * @test
	 */
	public function itShouldClearCachegsdg() {
		$sut = $this->getInstance();

		try {
			$sut->setMultiple( [
				'key' => 'some-other-value',
				'key2' => 'value 2',
			] );
		} catch (InvalidArgumentException $e) {
		}

		$return = $sut->clear();
		$this->assertTrue($return, '');
	}
}
