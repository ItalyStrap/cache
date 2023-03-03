<?php
declare(strict_types=1);

namespace ItalyStrap\Storage;

class Transient implements StorageInterface {

	public function get(string $key, $default = null) {
		$this->assertKeyLength($key);
		return \get_transient($key) ?? $default;
	}

	public function set(string $key, $value, $ttl = 0): bool {
		$this->assertKeyLength($key);
		return (bool)\set_transient(...\func_get_args());
	}

	public function update(string $key, $value, $ttl = 0): bool {
		$this->assertKeyLength($key);
		return $this->set(...\func_get_args());
	}

	public function delete(string $key): bool {
		$this->assertKeyLength($key);
		return (bool)\delete_transient($key);
	}

	private function assertKeyLength(string $key): void {
		if (\strlen($key) > 180) {
			throw new \InvalidArgumentException(\sprintf(
				'The maximum length key "%s" is %d characters',
				$key,
				180
			));
		}
	}
}
