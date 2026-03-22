<?php
/**
 * Unit Tests for WordPress Hooks Mock
 * 
 * @package ksfraser\MockWordPress\Tests
 * @requirement REQ-TEST-002 - Verify WordPress hooks mock and tracking works correctly
 */

namespace ksfraser\MockWordPress\Tests;

use PHPUnit\Framework\TestCase;
use ksfraser\MockWordPress\Mock\WordPressHooks;

/**
 * Test WordPress Hooks Mock
 * 
 * @covers \ksfraser\MockWordPress\Mock\WordPressHooks
 */
class WordPressHooksTest extends TestCase
{
    /**
     * @before
     */
    public function resetHooks()
    {
        WordPressHooks::reset();
    }

    /**
     * Test add_action registers an action hook
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressHooks::add_action
     * @covers \ksfraser\MockWordPress\Mock\WordPressHooks::hasHook
     */
    public function testAddActionRegistersHook()
    {
        $callback = function () {
            return 'executed';
        };

        WordPressHooks::add_action('test_action', $callback);
        $this->assertTrue(WordPressHooks::hasHook('test_action'));
    }

    /**
     * Test add_filter registers a filter hook
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressHooks::add_filter
     */
    public function testAddFilterRegistersHook()
    {
        $callback = function ($value) {
            return $value . '_filtered';
        };

        WordPressHooks::add_filter('test_filter', $callback);
        $this->assertTrue(WordPressHooks::hasHook('test_filter'));
    }

    /**
     * Test do_action executes registered callbacks
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressHooks::do_action
     */
    public function testDoActionExecutesCallbacks()
    {
        $execution_log = [];

        $callback = function ($value) use (&$execution_log) {
            $execution_log[] = $value;
        };

        WordPressHooks::add_action('test_action', $callback);
        WordPressHooks::do_action('test_action', 'test_value');

        $this->assertContains('test_value', $execution_log);
    }

    /**
     * Test apply_filters passes value through callbacks
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressHooks::apply_filters
     */
    public function testApplyFiltersModifiesValue()
    {
        $callback = function ($value) {
            return $value . '_modified';
        };

        WordPressHooks::add_filter('test_filter', $callback);
        $result = WordPressHooks::apply_filters('test_filter', 'original');

        $this->assertEquals('original_modified', $result);
    }

    /**
     * Test apply_filters with multiple callbacks
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressHooks::apply_filters
     */
    public function testApplyFiltersWithMultipleCallbacks()
    {
        WordPressHooks::add_filter('chain_filter', function ($value) {
            return $value . '1';
        }, 10);

        WordPressHooks::add_filter('chain_filter', function ($value) {
            return $value . '2';
        }, 20);

        $result = WordPressHooks::apply_filters('chain_filter', 'start');

        // Lower priority (10) executes first
        $this->assertEquals('start12', $result);
    }

    /**
     * Test getExecutedActions tracks action calls
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressHooks::getExecutedActions
     */
    public function testGetExecutedActionsTracksCallS()
    {
        $callback = function () {};

        WordPressHooks::add_action('tracked_action', $callback);
        WordPressHooks::do_action('tracked_action', 'arg1', 'arg2');

        $executed = WordPressHooks::getExecutedActions('tracked_action');

        $this->assertCount(1, $executed);
        $this->assertEquals(['arg1', 'arg2'], $executed[0]['args']);
    }

    /**
     * Test getAppliedFilters tracks filter calls
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressHooks::getAppliedFilters
     */
    public function testGetAppliedFiltersTracksCalls()
    {
        $callback = function ($value) {
            return $value;
        };

        WordPressHooks::add_filter('tracked_filter', $callback);
        WordPressHooks::apply_filters('tracked_filter', 'test_value', 'extra_arg');

        $applied = WordPressHooks::getAppliedFilters('tracked_filter');

        $this->assertCount(1, $applied);
        $this->assertEquals('test_value', $applied[0]['initial_value']);
    }

    /**
     * Test hook priority ordering
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressHooks::add_action
     * @covers \ksfraser\MockWordPress\Mock\WordPressHooks::do_action
     */
    public function testHookPriorityOrdering()
    {
        $execution_order = [];

        WordPressHooks::add_action('priority_test', function () use (&$execution_order) {
            $execution_order[] = 'priority_20';
        }, 20);

        WordPressHooks::add_action('priority_test', function () use (&$execution_order) {
            $execution_order[] = 'priority_10';
        }, 10);

        WordPressHooks::do_action('priority_test');

        // Lower priority number should execute first
        $this->assertEquals(['priority_10', 'priority_20'], $execution_order);
    }

    /**
     * Test remove_hook removes a registered hook
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressHooks::removeHook
     */
    public function testRemoveHookUnregistersHook()
    {
        $callback = function () {};

        WordPressHooks::add_action('removal_test', $callback);
        $this->assertTrue(WordPressHooks::hasHook('removal_test'));

        WordPressHooks::removeHook('removal_test', $callback);
        $this->assertFalse(WordPressHooks::hasHook('removal_test'));
    }

    /**
     * Test reset clears all hooks
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressHooks::reset
     */
    public function testResetClearsAllHooks()
    {
        WordPressHooks::add_action('action1', function () {});
        WordPressHooks::add_filter('filter1', function ($v) { return $v; });
        WordPressHooks::do_action('action1');

        WordPressHooks::reset();

        $this->assertFalse(WordPressHooks::hasHook('action1'));
        $this->assertEmpty(WordPressHooks::getExecutedActions('action1'));
    }

    /**
     * Test do_action with no arguments
     * 
     * @test
     */
    public function testDoActionWithNoArguments()
    {
        $executed = false;
        $callback = function () use (&$executed) {
            $executed = true;
        };

        WordPressHooks::add_action('no_args_action', $callback, 10, 0);
        WordPressHooks::do_action('no_args_action');

        $this->assertTrue($executed);
    }

    /**
     * Test multiple do_action calls are all tracked
     * 
     * @test
     */
    public function testMultipleDoActionCallsAreTracked()
    {
        $callback = function () {};

        WordPressHooks::add_action('multi_action', $callback);
        WordPressHooks::do_action('multi_action', 'call1');
        WordPressHooks::do_action('multi_action', 'call2');
        WordPressHooks::do_action('multi_action', 'call3');

        $executed = WordPressHooks::getExecutedActions('multi_action');
        $this->assertCount(3, $executed);
    }
}
