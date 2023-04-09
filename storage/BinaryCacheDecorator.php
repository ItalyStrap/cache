<?php
declare(strict_types=1);

namespace ItalyStrap\Storage;

/**
 * @psalm-api
 */
class BinaryCacheDecorator implements CacheInterface
{

    use MultipleTrait, SetMultipleCacheTrait;

    private CacheInterface $driver;

    public function __construct(CacheInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        /** @var mixed $data */
        $data = $this->driver->get($key, $default);

        $generated_key = $this->generateKey($key);
        if (\is_array($data) && \array_key_exists($generated_key, $data)) {
            return $this->decode((string)$data[$generated_key]) ?: $data;
        }

        return $data;
    }

    /**
     * To store some binary data I tried some code, but I end up
     * to save the binary in an array with a generated key, so
     * it is more simple to access later with the `BinaryTransient::get()` method.
     * If you have a better solution for this please go on and make a PR.
     */
    public function set(string $key, $value, ?int $ttl = 0): bool
    {
        if (\is_string($value) && !mb_check_encoding($value, 'ASCII')) {
            return $this->driver->set($key, [$this->generateKey($key) => $this->encode($value)], $ttl);
        }

        return $this->driver->set($key, $value, $ttl);
    }

    public function update(string $key, $value, ?int $ttl = 0): bool
    {
        if (\is_string($value) && !mb_check_encoding($value, 'ASCII')) {
            return $this->driver->update($key, [$this->generateKey($key) => $this->encode($value)], $ttl);
        }

        return $this->driver->update($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->driver->delete($key);
    }

    /**
     * @param string $value
     * @return string
     */
    private function encode(string $value): string
    {
        return \base64_encode($value);
    }

    /**
     * @param string $value
     * @return false|string
     */
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
