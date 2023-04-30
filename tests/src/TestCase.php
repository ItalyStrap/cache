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

    use ProphecyTrait, StubFunctionsDefinitionTrait;

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected string $cache_key;

    protected \Prophecy\Prophecy\ObjectProphecy $cacheProphecy;

    public function makeCache(): CacheInterface
    {
        return $this->cacheProphecy->reveal();
    }

	// phpcs:ignore
	protected function _before() {
        $this->cache_key = 'widget_list';
        $this->setUpStubStorageFunctions();
        $this->cacheProphecy = $this->prophesize(CacheInterface::class);
    }

	// phpcs:ignore
	protected function _after() {
        $this->cache_key = '';
        $this->tearDownStubStorageFunctions();
        $this->prophet->checkPredictions();
    }

    protected function defineFunction(string $func_name, callable $callable): void
    {
		// phpcs:ignore
		\tad\FunctionMockerLe\define($func_name, $callable);
    }
}
