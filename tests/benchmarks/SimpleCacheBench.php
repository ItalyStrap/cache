<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\Benchmark;

use ItalyStrap\Cache\SimpleCache;

/**
 * @BeforeMethods({"init"})
 */
class SimpleCacheBench {

	private $cache;
	private $store = [];

	public function init() {
		// phpcs:ignore
		\tad\FunctionMockerLe\define('set_transient', function ($key, $value, $ttl) {
			$this->store[ $key ] = $value;
			return true;
		});
		// phpcs:ignore
		\tad\FunctionMockerLe\define('get_transient', function ($key) {
			return $this->store[$key] ?? false;
		});
		// phpcs:ignore
		\tad\FunctionMockerLe\define('delete_transient', function ($key) {
			unset($this->store[$key]);
			return true;
		});

		$this->cache = new SimpleCache();
	}

	/**
	 * @Warmup(2)
	 * @Revs(1000)
	 * @Iterations(5)
	 */
	public function benchSet() {
		$this->cache->set('key', 'value', 10);
	}

	/**
	 * @Warmup(2)
	 * @Revs(1000)
	 * @Iterations(5)
	 */
	public function benchSetTransient() {
		set_transient('key', 'value', 10);
	}

	/**
	 * @Warmup(2)
	 * @Revs(1000)
	 * @Iterations(5)
	 */
	public function benchGet() {
		$this->cache->get('key');
	}

	/**
	 * @Warmup(2)
	 * @Revs(1000)
	 * @Iterations(5)
	 */
	public function benchGetTransient() {
		get_transient('key');
	}

	/**
	 * @Warmup(2)
	 * @Revs(1000)
	 * @Iterations(5)
	 */
	public function benchDelete() {
		$this->cache->delete('key');
	}

	/**
	 * @Warmup(2)
	 * @Revs(1000)
	 * @Iterations(5)
	 */
	public function benchDeleteTransient() {
		delete_transient('key');
	}
}
