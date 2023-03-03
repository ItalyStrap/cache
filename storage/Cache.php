<?php
declare(strict_types=1);

namespace ItalyStrap\Storage;

//* @see \wp_cache_add()
//* @see \wp_cache_get()
//* @see \wp_cache_set()
//* @see \wp_cache_delete()
//* @see \wp_cache_flush()
class Cache implements StorageInterface {

	public function get(string $key, $default = null) {
		return \wp_cache_get($key, 'default') ?? $default;
	}

	public function set(string $key, $value, $ttl = 0): bool {
		return (bool)\wp_cache_set(...\func_get_args());
	}

	public function update(string $key, $value, $ttl = 0): bool {
		return $this->set(...\func_get_args());
	}

	public function delete(string $key): bool {
		return (bool)\wp_cache_delete($key, 'default');
	}
}
