<?php
declare(strict_types=1);

namespace ItalyStrap\Storage;

class BinaryTransient implements CacheInterface {

	private CacheInterface $storage;

	public function __construct(CacheInterface $storage)
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

	/**
	 * To store some binary data I tried some code, but I end up
	 * to save the binary in an array with a generated key, so
	 * it is more simple to access later with the `BinaryTransient::get()` method.
	 * If you have a better solution for this please go on and make a PR.
	 */
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

	/**
	 * The generated key should be almost safe
	 * because we append a class name prefix to the key.
	 */
	private function generateKey(string $key): string
	{
		return \md5(self::class . $key);
	}
}
