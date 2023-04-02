<?php
declare(strict_types=1);

namespace ItalyStrap\Storage;

/**
 * @psalm-api
 */
class Transient implements CacheInterface {

	use KeyLengthValidate, MultipleTrait, SetCacheTrait;

	public function get(string $key, $default = null) {
		$this->assertKeyLength($key);
		return \get_transient($key) ?? $default;
	}

	public function set(string $key, $value, ?int $ttl = 0): bool {
		$this->assertKeyLength($key);
		return (bool)\set_transient(...\func_get_args());
	}

	public function update(string $key, $value, ?int $ttl = 0): bool {
		$this->assertKeyLength($key);
		return $this->set($key, $value, $ttl);
	}

	public function delete(string $key): bool {
		$this->assertKeyLength($key);
		return (bool)\delete_transient($key);
	}
}
