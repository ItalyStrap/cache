<?php

class IntegrationTest extends \Codeception\TestCase\WPTestCase
{
    /**
     * @var \WpunitTester
     */
    protected $tester;

    public function setUp(): void
    {
        // Before...
        parent::setUp();

        // Your set up methods here.
    }

    public function tearDown(): void
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    // Tests
    public function test_it_works()
    {
        $transient = \set_transient( 'key', false );

        codecept_debug( \json_encode(\get_transient('key')) );
        codecept_debug( \json_encode(\get_transient('key-2')) );
    }

    // Tests
    public function test_it_workspo()
    {
        $transient = \set_transient( 'key', 0 );

        codecept_debug( \json_encode(\get_transient('key')) );
        codecept_debug( \json_encode(\get_transient('key-2')) );
    }

    // Tests
    public function test_it_workspofgdf()
    {
        $transient = \set_transient( 'key', '' );

        codecept_debug( \json_encode(\get_transient('key')) );
        codecept_debug( \json_encode(\get_transient('key-2')) );
    }
}
