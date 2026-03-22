<?php
/**
 * WordPress Hook Assertions for Testing
 * 
 * @package ksfraser\MockWordPress
 * @requirement REQ-TEST-002 - Provide hook tracking and assertions for event-driven code
 */

namespace ksfraser\MockWordPress\Assertion;

use ksfraser\MockWordPress\Mock\WordPressHooks;

/**
 * Assertion helpers for testing WordPress hooks
 * 
 * Use these assertions in your PHPUnit tests to verify how hooks are registered
 * and executed.
 * 
 * Example:
 * ```php
 * $assertions = new HookAssertions();
 * $assertions->assertHookRegistered('save_post');
 * $assertions->assertActionExecuted('publish_post', ['post_id' => 123]);
 * ```
 */
class HookAssertions
{
    /**
     * Assert that a hook is registered
     * 
     * @param string $hook_name Hook name
     * @param callable|null $callback Specific callback (optional)
     * @param string $message Error message
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @return void
     */
    public static function assertHookRegistered($hook_name, $callback = null, $message = '')
    {
        $is_registered = WordPressHooks::hasHook($hook_name, $callback);

        if (!$is_registered) {
            $callback_str = $callback ? ": " . self::callableToString($callback) : '';
            $default = "Hook '{$hook_name}'{$callback_str} was not registered";
            throw new \PHPUnit\Framework\AssertionFailedError(
                $message ?: $default
            );
        }
    }

    /**
     * Assert that an action hook was executed
     * 
     * @param string $hook_name Action name
     * @param array $expected_args Expected arguments (optional)
     * @param string $message Error message
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @return void
     */
    public static function assertActionExecuted($hook_name, $expected_args = null, $message = '')
    {
        $executed = WordPressHooks::getExecutedActions($hook_name);

        if (empty($executed)) {
            $default = "Action '{$hook_name}' was not executed";
            throw new \PHPUnit\Framework\AssertionFailedError(
                $message ?: $default
            );
        }

        if ($expected_args !== null) {
            $found = false;
            foreach ($executed as $execution) {
                if (self::argsMatch($execution['args'], $expected_args)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $expected_str = json_encode($expected_args);
                $default = "Action '{$hook_name}' was executed but not with expected args: {$expected_str}";
                throw new \PHPUnit\Framework\AssertionFailedError(
                    $message ?: $default
                );
            }
        }
    }

    /**
     * Assert that an action hook was NOT executed
     * 
     * @param string $hook_name Action name
     * @param string $message Error message
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @return void
     */
    public static function assertActionNotExecuted($hook_name, $message = '')
    {
        $executed = WordPressHooks::getExecutedActions($hook_name);

        if (!empty($executed)) {
            $default = "Action '{$hook_name}' was executed but should not have been";
            throw new \PHPUnit\Framework\AssertionFailedError(
                $message ?: $default
            );
        }
    }

    /**
     * Assert that a filter hook was applied
     * 
     * @param string $hook_name Filter name
     * @param string $message Error message
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @return void
     */
    public static function assertFilterApplied($hook_name, $message = '')
    {
        $applied = WordPressHooks::getAppliedFilters($hook_name);

        if (empty($applied)) {
            $default = "Filter '{$hook_name}' was not applied";
            throw new \PHPUnit\Framework\AssertionFailedError(
                $message ?: $default
            );
        }
    }

    /**
     * Assert that a filter hook was NOT applied
     * 
     * @param string $hook_name Filter name
     * @param string $message Error message
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @return void
     */
    public static function assertFilterNotApplied($hook_name, $message = '')
    {
        $applied = WordPressHooks::getAppliedFilters($hook_name);

        if (!empty($applied)) {
            $default = "Filter '{$hook_name}' was applied but should not have been";
            throw new \PHPUnit\Framework\AssertionFailedError(
                $message ?: $default
            );
        }
    }

    /**
     * Assert action execution count
     * 
     * @param string $hook_name Action name
     * @param int $expected_count Expected execution count
     * @param string $message Error message
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @return void
     */
    public static function assertActionExecutedTimes($hook_name, $expected_count, $message = '')
    {
        $executed = WordPressHooks::getExecutedActions($hook_name);
        $actual_count = count($executed);

        if ($actual_count !== $expected_count) {
            $default = "Action '{$hook_name}' executed {$actual_count} times, expected {$expected_count}";
            throw new \PHPUnit\Framework\AssertionFailedError(
                $message ?: $default
            );
        }
    }

    /**
     * Get execution history for debugging
     * 
     * @param string $hook_name Action name
     * @return array
     */
    public static function getActionExecutionHistory($hook_name)
    {
        return WordPressHooks::getExecutedActions($hook_name);
    }

    /**
     * Get filter application history for debugging
     * 
     * @param string $hook_name Filter name
     * @return array
     */
    public static function getFilterApplicationHistory($hook_name)
    {
        return WordPressHooks::getAppliedFilters($hook_name);
    }

    /**
     * Helper: Convert callable to string representation
     * 
     * @param callable $callback
     * @return string
     */
    private static function callableToString($callback)
    {
        if (is_array($callback)) {
            $class = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
            return "{$class}::{$callback[1]}";
        } elseif ($callback instanceof \Closure) {
            return 'Closure';
        }
        return (string)$callback;
    }

    /**
     * Helper: Check if args match expected
     * 
     * @param array $actual
     * @param array $expected
     * @return bool
     */
    private static function argsMatch($actual, $expected)
    {
        if (is_array($expected) && !is_array($actual)) {
            return false;
        }

        if (is_array($expected)) {
            foreach ($expected as $key => $value) {
                if (!isset($actual[$key]) || $actual[$key] !== $value) {
                    if (!is_numeric($key)) {
                        return false;
                    }
                    // For numeric keys, try matching by position
                    if (($actual[$key] ?? null) !== $value) {
                        return false;
                    }
                }
            }
            return true;
        }

        return $actual === $expected;
    }
}
