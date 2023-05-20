<?php
declare(strict_types=1);

namespace ItalyStrap\Tests;

use ItalyStrap\Cache\ExpirationInterface;

trait ExpirationTestTrait
{
    abstract public function makeInstance(): ExpirationInterface;

    /**
     * @test
     */
    public function testExpiresIsValid()
    {
        $sut = $this->makeInstance();
        $this->assertTrue($sut->isValid(), 'The time should be valid');
    }

    public static function expireAfterWithSecondsOrDateTimeIntervalProvider(): iterable
    {
        yield 'Null'    => [
            null,
            ExpirationInterface::YEAR_IN_SECONDS,
            true,
        ];

        yield 'Negative int'    => [
            -1,
            -1,
            false,
        ];

        yield 'Zero'    => [
            0,
            -1,
            false,
        ];

        yield 'One' => [
            1,
            1,
            true,
        ];

        yield 'YEAR_IN_SECONDS' => [
            ExpirationInterface::YEAR_IN_SECONDS,
            ExpirationInterface::YEAR_IN_SECONDS,
            true,
        ];

        yield 'YEAR_IN_SECONDS + 1' => [
            ExpirationInterface::YEAR_IN_SECONDS + 1,
            ExpirationInterface::YEAR_IN_SECONDS + 1,
            true,
        ];

        yield 'Day in seconds'  => [
            86400,
            86400,
            true,
        ];

        yield 'DateInterval'    => [
            new \DateInterval('P1Y'),
            ExpirationInterface::YEAR_IN_SECONDS,
            true,
        ];

        yield 'DateInterval + 1'    => [
            new \DateInterval('P1Y1D'),
            ExpirationInterface::YEAR_IN_SECONDS + 86400,
            true,
        ];

        yield 'DateInterval 1 day'  => [
            new \DateInterval('P1D'),
            86400,
            true,
        ];
    }

    /**
     * @dataProvider expireAfterWithSecondsOrDateTimeIntervalProvider
     */
    public function testExpirationIfValueProvidedToExpireAfterIs($actual, $expected, $is_valid)
    {
        $sut = $this->makeInstance();
        $sut->expiresAfter($actual);
        $this->assertSame(
            $expected,
            $sut->expirationInSeconds(),
            'expirationInSeconds Should be the same as expected'
        );
        $this->assertSame($is_valid, $sut->isValid(), 'The time should be valid');
    }

    public static function expireAtDateTimeInterfaceOrNullProvider(): iterable
    {
        yield 'Null'    => [
            null,
            ExpirationInterface::YEAR_IN_SECONDS,
            true,
        ];

        yield '1 year'  => [
            'now +1 year',
            ExpirationInterface::YEAR_IN_SECONDS,
            true,
        ];

        yield '1 second'    => [
            'now +1 second',
            1,
            true,
        ];

        yield 'now' => [
            'now',
            0,
            false,
        ];

        yield 'DateTime with no arguments'  => [
            '',
            0,
            false,
        ];

        yield 'now -1 Second'   => [
            'now -1 Second',
            -1,
            false,
        ];

        yield 'now +0 Second'   => [
            'now +0 Second',
            0,
            false,
        ];
    }

    /**
     * @dataProvider expireAtDateTimeInterfaceOrNullProvider
     */
    public function testExpirationIfValueProvidedToExpireAtIs($actual, $expected, $is_valid)
    {
        $sut = $this->makeInstance();
        $sut->expiresAt($actual === null ? $actual : new \DateTime((string)$actual));
        $this->assertSame(
            $expected,
            $sut->expirationInSeconds(),
            'expirationInSeconds Should be the same as expected'
        );
        $this->assertSame($is_valid, $sut->isValid(), 'The time should be valid');
    }

    public function invalidExpireAtValue(): iterable
    {
        yield 'A string' => [''];
        yield 'An array' => [[]];
        yield 'An object' => [(object)['key' => 'value']];
    }

    /**
     * @dataProvider invalidExpireAtValue()
     * @test
     */
    public function testShouldThrownIfExpirationIs($expiration)
    {
        $this->expectException(\InvalidArgumentException::class);
        $sut = $this->makeInstance();
        $sut->expiresAt($expiration);
    }
}
