<?php
declare(strict_types=1);

namespace ItalyStrap\Tests;

use Codeception\Test\Unit;
use ItalyStrap\Storage\CacheInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use function tad\FunctionMockerLe\defineWithMap;
use function tad\FunctionMockerLe\undefineAll;

class TestCase extends Unit
{

    use ProphecyTrait;

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected array $store = [];
    protected bool $set_return_value = true;
    protected bool $delete_return_value = true;
    protected ?int $ttl = 0;

    /**
     * @var \Closure[]
     */
    private array $mockFunctionDefinitions;

    protected \Prophecy\Prophecy\ObjectProphecy $cache;

    public function makeCache(): CacheInterface
    {
        return $this->cache->reveal();
    }

	// phpcs:ignore
	protected function _before() {
        $this->mockFunctionDefinitions = [
            'get_transient' => function (string $key) {
                if ($this->ttl && $this->ttl < 0) {
                    return false;
                }

                return $this->store[ $key ] ?? false;
            },
            'set_transient' => function (string $key, $value, $ttl = 0): bool {
                $this->ttl = $ttl;
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
        ];

        defineWithMap($this->mockFunctionDefinitions);

        $this->cache = $this->prophesize(CacheInterface::class);
    }

	// phpcs:ignore
	protected function _after() {
        undefineAll(\array_keys($this->mockFunctionDefinitions));
        $this->store = [];
        $this->set_return_value = true;
        $this->delete_return_value = true;
        $this->ttl = 0;
        $this->prophet->checkPredictions();
    }

    protected function defineFunction(string $func_name, callable $callable): void
    {
		// phpcs:ignore
		\tad\FunctionMockerLe\define($func_name, $callable);
    }
}
