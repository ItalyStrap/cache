<?php
declare(strict_types=1);

namespace ItalyStrap\Storage;

interface StorageInterface {

	/**
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get(string $key, $default = null);

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param \DateInterval|int $ttl
	 * @return bool
	 */
	public function set(string $key, $value, $ttl = null): bool;

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param \DateInterval|int $ttl
	 * @return bool
	 */
	public function update(string $key, $value, $ttl = null): bool;

	public function delete(string $key): bool;
}
