<?php
/**
 * WordPress Hooks Mock System
 * 
 * Tracks and manages WordPress hooks for testing
 * @package ksfraser\MockWordPress
 * @requirement REQ-TEST-002 - Provide hook tracking and assertions for event-driven code
 */

namespace ksfraser\MockWordPress\Mock;

/**
 * Mock WordPress hook system
 * 
 * Tracks all add_action, add_filter, do_action, apply_filters calls
 * Enables assertions on hook behavior in tests.
 * 
 * UML Class:
 * ```
 * class WordPressHooks
 * {
 *   -hooks: array[HookData]
 *   -actions: array[string]
 *   -filters: array[string]
 *   +addAction(hook, func, priority, argc)
 *   +addFilter(hook, func, priority, argc)
 *   +doAction(hook, ...args)
 *   +applyFilters(hook, value, ...args)
 *   +getHooks(): array
 *   +reset(): void
 * }
 * ```
 */
class WordPressHooks
{
    /**
     * Registered hooks
     * 
     * Format: hook_name => [
     *   'callback' => callable,
     *   'priority' => int,
     *   'accepted_args' => int,
     *   'type' => 'action'|'filter'
     * ]
     * 
     * @var array
     */
    private static $hooks = [];

    /**
     * Executed actions tracking
     * 
     * Format: action_name => [
     *   ['args' => [...], 'timestamp' => float],
     *   ...
     * ]
     * 
     * @var array
     */
    private static $executed_actions = [];

    /**
     * Applied filters tracking
     * 
     * @var array
     */
    private static $applied_filters = [];

    /**
     * Register an action hook
     * 
     * Mimics WordPress add_action()
     * 
     * @param string $hook_name Hook name
     * @param callable $callback Callback function
     * @param int $priority Hook priority (lower = earlier execution)
     * @param int $accepted_args Number of arguments to pass
     * @return true
     */
    public static function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        $hook_id = self::generateHookId($hook_name, $callback, $priority);
        self::$hooks[$hook_id] = [
            'name' => $hook_name,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
            'type' => 'action'
        ];
        return true;
    }

    /**
     * Register a filter hook
     * 
     * Mimics WordPress add_filter()
     * 
     * @param string $hook_name Hook name
     * @param callable $callback Callback function
     * @param int $priority Hook priority
     * @param int $accepted_args Number of arguments to pass
     * @return true
     */
    public static function add_filter($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        $hook_id = self::generateHookId($hook_name, $callback, $priority);
        self::$hooks[$hook_id] = [
            'name' => $hook_name,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
            'type' => 'filter'
        ];
        return true;
    }

    /**
     * Execute an action hook
     * 
     * Mimics WordPress do_action()
     * Calls all registered callbacks in priority order
     * 
     * @param string $hook_name Hook name
     * @param mixed ...$args Arguments to pass
     * @return void
     */
    public static function do_action($hook_name, ...$args)
    {
        self::$executed_actions[$hook_name][] = [
            'args' => $args,
            'timestamp' => microtime(true)
        ];

        $callbacks = self::getHooksByName($hook_name, 'action');
        foreach ($callbacks as $hook_id => $hook) {
            $callback = $hook['callback'];
            $argc = min($hook['accepted_args'], count($args));
            call_user_func_array($callback, array_slice($args, 0, $argc));
        }
    }

    /**
     * Apply a filter hook
     * 
     * Mimics WordPress apply_filters()
     * Calls all registered callbacks in priority order, passing value through each
     * 
     * @param string $hook_name Hook name
     * @param mixed $value Value to filter
     * @param mixed ...$args Additional arguments
     * @return mixed Filtered value
     */
    public static function apply_filters($hook_name, $value, ...$args)
    {
        self::$applied_filters[$hook_name][] = [
            'initial_value' => $value,
            'args' => $args,
            'timestamp' => microtime(true)
        ];

        $callbacks = self::getHooksByName($hook_name, 'filter');
        foreach ($callbacks as $hook_id => $hook) {
            $callback = $hook['callback'];
            $argc = min($hook['accepted_args'], count($args) + 1);
            $call_args = array_merge([$value], $args);
            $value = call_user_func_array($callback, array_slice($call_args, 0, $argc));
        }

        return $value;
    }

    /**
     * Get all hooks
     * 
     * @return array
     */
    public static function getHooks()
    {
        return self::$hooks;
    }

    /**
     * Get hooks by name and type
     * 
     * @param string $hook_name Hook name to filter
     * @param string $type 'action' or 'filter'
     * @return array
     */
    private static function getHooksByName($hook_name, $type = null)
    {
        $filtered = [];
        foreach (self::$hooks as $hook_id => $hook) {
            if ($hook['name'] === $hook_name) {
                if ($type === null || $hook['type'] === $type) {
                    $filtered[$hook_id] = $hook;
                }
            }
        }

        // Sort by priority (lower priority = higher execution order)
        uasort($filtered, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $filtered;
    }

    /**
     * Get executed actions
     * 
     * @param string|null $hook_name Filter by hook name
     * @return array
     */
    public static function getExecutedActions($hook_name = null)
    {
        if ($hook_name === null) {
            return self::$executed_actions;
        }
        return self::$executed_actions[$hook_name] ?? [];
    }

    /**
     * Get applied filters
     * 
     * @param string|null $hook_name Filter by hook name
     * @return array
     */
    public static function getAppliedFilters($hook_name = null)
    {
        if ($hook_name === null) {
            return self::$applied_filters;
        }
        return self::$applied_filters[$hook_name] ?? [];
    }

    /**
     * Reset all hooks and tracking
     * 
     * @return void
     */
    public static function reset()
    {
        self::$hooks = [];
        self::$executed_actions = [];
        self::$applied_filters = [];
    }

    /**
     * Generate unique hook ID
     * 
     * @param string $hook_name
     * @param callable $callback
     * @param int $priority
     * @return string
     */
    private static function generateHookId($hook_name, $callback, $priority)
    {
        $callback_str = is_callable($callback) 
            ? (is_array($callback) ? implode('::', $callback) : (string)$callback)
            : 'unknown';
        return "{$hook_name}:{$callback_str}:{$priority}";
    }

    /**
     * Check if a specific hook is registered
     * 
     * @param string $hook_name Hook name
     * @param callable|null $callback Specific callback to check
     * @return bool
     */
    public static function hasHook($hook_name, $callback = null)
    {
        $hooks = self::getHooksByName($hook_name);
        if ($callback === null) {
            return !empty($hooks);
        }

        foreach ($hooks as $hook) {
            if ($hook['callback'] === $callback) {
                return true;
            }
        }
        return false;
    }

    /**
     * Remove a hook
     * 
     * @param string $hook_name Hook name
     * @param callable $callback Callback to remove
     * @param int $priority Priority of the hook
     * @return bool
     */
    public static function removeHook($hook_name, $callback, $priority = 10)
    {
        $hook_id = self::generateHookId($hook_name, $callback, $priority);
        if (isset(self::$hooks[$hook_id])) {
            unset(self::$hooks[$hook_id]);
            return true;
        }
        return false;
    }
}
