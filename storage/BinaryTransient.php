<?php
declare(strict_types=1);

namespace ItalyStrap\Storage;

class BinaryTransient implements StorageInterface {

	private StorageInterface $storage;

	public function __construct(StorageInterface $storage)
	{
		$this->storage = $storage;
	}

	public function get(string $key, $default = null) {
		$data = $this->storage->get(...\func_get_args());

		$generated_key = $this->generateKey($key);
		if (\is_array($data) && \array_key_exists($generated_key, $data)) {
			return $this->decode($data[$generated_key]) ?? $data;
		}

		return $data;
	}

	public function set(string $key, $value, $ttl = 0): bool {
		if (\is_string($value) && !mb_check_encoding($value, 'ASCII')) {
			return $this->storage->set($key, [$this->generateKey($key) => $this->encode($value)], $ttl);
		}

		return $this->storage->set(...\func_get_args());
	}

	public function update(string $key, $value, $ttl = 0): bool {
		if (\is_string($value) && !mb_check_encoding($value, 'ASCII')) {
			return $this->storage->update($key, [$this->generateKey($key) => $this->encode($value)], $ttl);
		}

		return $this->storage->update(...\func_get_args());
	}

	public function delete(string $key): bool {
		return $this->storage->delete(...\func_get_args());
	}

	private function encode(string $value): string
	{
		return \base64_encode($value);
	}

	private function decode(string $value)
	{
		return \base64_decode($value, true);
	}

	private function generateKey(string $key): string
	{
		return \md5($key . self::class);
	}
}
