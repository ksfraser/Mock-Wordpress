<?php
/**
 * Base Test Case for WordPress Tests
 * 
 * @package ksfraser\MockWordPress
 * @requirement REQ-TEST-001 - Provide reusable WordPress core mocks
 */

namespace ksfraser\MockWordPress;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use ksfraser\MockWordPress\Mock\WordPressFunctions;
use ksfraser\MockWordPress\Mock\WordPressHooks;
use ksfraser\MockWordPress\Mock\WPDB;
use ksfraser\MockWordPress\Assertion\HookAssertions;

/**
 * Base test case class for WordPress plugin tests
 * 
 * Provides setup/teardown of WordPress mocks and helper methods
 * for common test scenarios.
 * 
 * Example:
 * ```php
 * class MyPluginTest extends TestCase
 * {
 *     public function testSomething()
 *     {
 *         $this->assertHookRegistered('save_post');
 *     }
 * }
 * ```
 */
abstract class TestCase extends PHPUnitTestCase
{
    /**
     * Mock WPDB instance
     * 
     * @var WPDB
     */
    protected $wpdb;

    /**
     * Set up test environment
     * 
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Initialize mocks
        WordPressFunctions::reset();
        WordPressHooks::reset();

        $this->wpdb = new WPDB();

        // Make globals available
        $GLOBALS['wpdb'] = $this->wpdb;
    }

    /**
     * Clean up after test
     * 
     * @return void
     */
    protected function tearDown(): void
    {
        WordPressFunctions::reset();
        WordPressHooks::reset();

        if (isset($GLOBALS['wpdb'])) {
            unset($GLOBALS['wpdb']);
        }

        parent::tearDown();
    }

    /**
     * Assert that a hook is registered
     * 
     * @param string $hook_name
     * @param callable|null $callback
     * @param string $message
     * @return void
     */
    public function assertHookRegistered($hook_name, $callback = null, $message = '')
    {
        HookAssertions::assertHookRegistered($hook_name, $callback, $message);
    }

    /**
     * Assert that an action was executed
     * 
     * @param string $hook_name
     * @param array|null $expected_args
     * @param string $message
     * @return void
     */
    public function assertActionExecuted($hook_name, $expected_args = null, $message = '')
    {
        HookAssertions::assertActionExecuted($hook_name, $expected_args, $message);
    }

    /**
     * Assert that an action was NOT executed
     * 
     * @param string $hook_name
     * @param string $message
     * @return void
     */
    public function assertActionNotExecuted($hook_name, $message = '')
    {
        HookAssertions::assertActionNotExecuted($hook_name, $message);
    }

    /**
     * Assert that a filter was applied
     * 
     * @param string $hook_name
     * @param string $message
     * @return void
     */
    public function assertFilterApplied($hook_name, $message = '')
    {
        HookAssertions::assertFilterApplied($hook_name, $message);
    }

    /**
     * Assert that a filter was NOT applied
     * 
     * @param string $hook_name
     * @param string $message
     * @return void
     */
    public function assertFilterNotApplied($hook_name, $message = '')
    {
        HookAssertions::assertFilterNotApplied($hook_name, $message);
    }

    /**
     * Assert action execution count
     * 
     * @param string $hook_name
     * @param int $expected_count
     * @param string $message
     * @return void
     */
    public function assertActionExecutedTimes($hook_name, $expected_count, $message = '')
    {
        HookAssertions::assertActionExecutedTimes($hook_name, $expected_count, $message);
    }

    /**
     * Get WordPress mock functions instance
     * 
     * @return WordPressFunctions
     */
    public function getWordPressFunctions()
    {
        return new WordPressFunctions();
    }

    /**
     * Get WordPress hooks instance
     * 
     * @return WordPressHooks
     */
    public function getWordPressHooks()
    {
        return new WordPressHooks();
    }

    /**
     * Set query results for current test
     * 
     * @param array $results
     * @return void
     */
    public function setWpdbResults($results)
    {
        $this->wpdb->setQueryResults($results);
    }

    /**
     * Get WPDB query log
     * 
     * @return array
     */
    public function getWpdbQueryLog()
    {
        return $this->wpdb->getQueryLog();
    }
}
