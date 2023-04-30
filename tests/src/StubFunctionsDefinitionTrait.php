<?php

declare(strict_types=1);

namespace ItalyStrap\Tests;

use function tad\FunctionMockerLe\defineWithMap;
use function tad\FunctionMockerLe\undefineAll;

trait StubFunctionsDefinitionTrait
{
    protected array $store = [];
    private bool $set_return_value = true;
    private bool $delete_return_value = true;
    private ?int $ttl = 0;
    private array $filters = [];

    /**
     * @var \Closure[]
     */
    private array $mockFunctionDefinitions;

    private function setUpStubStorageFunctions()
    {

        $this->mockFunctionDefinitions = [
            'get_transient' => function (string $key) {
                if ($this->ttl && $this->ttl < \time()) {
                    return false;
                }

                return \apply_filters("transient_{$key}", $this->store[ $key ] ?? false, $key);
            },
            'set_transient' => function (string $key, $value, $ttl = 0): bool {
                $this->ttl = \time()  + $ttl;
                $this->store[ $key ] = $value;
                return $this->set_return_value;
            },
            'delete_transient' => function (string $key): bool {
                if (!\array_key_exists($key, $this->store)) {
                    return false;
                }

                unset($this->store[ $key ]);
                return $this->delete_return_value;
            },
            'add_option' => function ($key, $value, $deprecated = '', $autoload = 'yes'): bool {
                $this->store[ $key ] = $value;
                return $this->set_return_value;
            },
            'update_option' => function ($key, $value, $deprecated = ''): bool {
                $this->store[ $key ] = $value;
                return $this->set_return_value;
            },
            'delete_option' => function ($key): bool {
                if (!\array_key_exists($key, $this->store)) {
                    return false;
                }

                unset($this->store[ $key ]);
                return $this->delete_return_value;
            },
            'get_option' => function ($key, $default = false) {
                return $this->store[ $key ] ?? $default;
            },
            'wp_cache_get' => function ($key, $group = '', $force = false, &$found = null) {
                if ($this->ttl && $this->ttl < \time()) {
                    return false;
                }

                $found = isset($this->store[ $key ]);
                return $this->store[ $key ] ?? false;
            },
            'wp_cache_set' => function ($key, $data, $group = '', $expire = 0): bool {
                $this->ttl = \time()  + $expire;
                $this->store[ $key ] = $data;
                return $this->set_return_value;
            },
            'wp_cache_delete' => function ($key, $group = ''): bool {
                unset($this->store[ $key ]);
                return $this->delete_return_value;
            },
            'wp_cache_add' => function ($key, $data, $group = '', $expire = 0): bool {
                if ($this->ttl && $this->ttl < 0) {
                    return false;
                }

                $this->store[ $key ] = $data;
                return $this->set_return_value;
            },
            'wp_cache_replace' => function ($key, $data, $group = '', $expire = 0): bool {
                if ($this->ttl && $this->ttl < 0) {
                    return false;
                }

                $this->store[ $key ] = $data;
                return $this->set_return_value;
            },
            'wp_cache_incr' => function ($key, $offset = 1, $group = ''): int {
                $this->store[ $key ] += $offset;
                return $this->store[ $key ];
            },
            'wp_cache_decr' => function ($key, $offset = 1, $group = ''): int {
                $this->store[ $key ] -= $offset;
                return $this->store[ $key ];
            },
            'wp_cache_flush' => function (): bool {
                $this->store = [];
                return true;
            },
            'wp_cache_set_multiple' => function ($keys, $group = '', $expire = 0): array {
                if ($this->ttl && $this->ttl < 0) {
                    return [false];
                }

                foreach ($keys as $key => $value) {
                    $this->store[ $key ] = $value;
                }
                return [$this->set_return_value];
            },
            'wp_cache_get_multiple' => function ($keys, $group = '', $force = false): array {
                $result = [];
                foreach ($keys as $key) {
                    $result[ $key ] = $this->store[ $key ] ?? false;
                }
                return $result;
            },
            'wp_cache_delete_multiple' => function ($keys, $group = ''): array {
                $result = [];
                foreach ($keys as $key) {
                    if (!\array_key_exists($key, $this->store)) {
                        $result[ $key ] = false;
                    }
                    unset($this->store[ $key ]);
                }
                return $result;
            },

            'get_theme_mod' => function ($key, $default = false) {
                return $this->store[ $key ] ?? $default;
            },

            'set_theme_mod' => function ($key, $value): bool {
                $this->store[ $key ] = $value;
                return $this->set_return_value;
            },

            'remove_theme_mod' => function ($key): bool {
                if (!\array_key_exists($key, $this->store)) {
                    return false;
                }

                unset($this->store[ $key ]);
                return $this->delete_return_value;
            },

            'remove_theme_mods' => function (): bool {
                $this->store = [];
                return true;
            },
            'add_filter' => function ($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
                $this->filters[ $tag ][] = [
                    'function' => $function_to_add,
                    'priority' => $priority,
                    'accepted_args' => $accepted_args,
                ];
                return true;
            },
            'has_filter' => function ($tag, $function_to_check = false) {
                return isset($this->filters[ $tag ]);
            },
            'remove_filter' => function ($tag, $function_to_remove, $priority = 10) {
                if (!isset($this->filters[ $tag ])) {
                    return false;
                }

                foreach ($this->filters[ $tag ] as $index => $filter) {
                    if ($filter['function'] === $function_to_remove && $filter['priority'] === $priority) {
                        unset($this->filters[ $tag ][ $index ]);
                        return true;
                    }
                }

                return false;
            },
            'apply_filters' => function ($tag, $value, ...$args) {
                if (!isset($this->filters[ $tag ])) {
                    return $value;
                }

                foreach ($this->filters[ $tag ] as $filter) {
                    $value = \call_user_func_array($filter['function'], $args);
                }

                return $value;
            },
        ];

        defineWithMap($this->mockFunctionDefinitions);
    }

    private function tearDownStubStorageFunctions()
    {
        $this->store = [];
        $this->set_return_value = true;
        $this->delete_return_value = true;
        $this->ttl = 0;
    }

    private function prepareSetReturnValue(bool $newState)
    {
        $this->set_return_value = $newState;
    }

    private function prepareDeleteReturnValue(bool $newState)
    {
        $this->delete_return_value = $newState;
    }
}
