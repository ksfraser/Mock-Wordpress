<?php
/**
 * WordPress Core Functions Mock
 * 
 * Provides global WordPress function stubs for unit testing
 * @package ksfraser\MockWordPress
 * @requirement REQ-TEST-001 - Provide reusable WordPress core mocks
 */

namespace ksfraser\MockWordPress\Mock;

/**
 * Mock functions for WordPress global functions
 * 
 * This class provides static methods that stub out common WordPress functions.
 * Use in your tests to avoid requiring actual WordPress installation.
 * 
 * @see https://developer.wordpress.org/plugins/hooks/
 */
class WordPressFunctions
{
    /**
     * Global options storage for mocking get_option/update_option/delete_option
     * 
     * @var array
     */
    private static $options = [];

    /**
     * Global meta storage for mocking get_post_meta/update_post_meta
     * 
     * @var array
     */
    private static $post_meta = [];

    /**
     * Global user meta storage for mocking get_user_meta/update_user_meta
     * 
     * @var array
     */
    private static $user_meta = [];

    /**
     * Reset all stored data
     * 
     * @return void
     */
    public static function reset()
    {
        self::$options = [];
        self::$post_meta = [];
        self::$user_meta = [];
    }

    /**
     * Mock get_option function
     * 
     * @param string $option Option name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function get_option($option, $default = false)
    {
        return self::$options[$option] ?? $default;
    }

    /**
     * Mock update_option function
     * 
     * @param string $option Option name
     * @param mixed $value Option value
     * @return bool
     */
    public static function update_option($option, $value)
    {
        self::$options[$option] = $value;
        return true;
    }

    /**
     * Mock delete_option function
     * 
     * @param string $option Option name
     * @return bool
     */
    public static function delete_option($option)
    {
        unset(self::$options[$option]);
        return true;
    }

    /**
     * Mock get_post_meta function
     * 
     * @param int $post_id Post ID
     * @param string $meta_key Meta key
     * @param bool $single Return single value
     * @return mixed
     */
    public static function get_post_meta($post_id, $meta_key, $single = false)
    {
        $key = "{$post_id}:{$meta_key}";
        if (!isset(self::$post_meta[$key])) {
            return $single ? '' : [];
        }
        return $single ? self::$post_meta[$key] : [self::$post_meta[$key]];
    }

    /**
     * Mock update_post_meta function
     * 
     * @param int $post_id Post ID
     * @param string $meta_key Meta key
     * @param mixed $meta_value Meta value
     * @return bool
     */
    public static function update_post_meta($post_id, $meta_key, $meta_value)
    {
        $key = "{$post_id}:{$meta_key}";
        self::$post_meta[$key] = $meta_value;
        return true;
    }

    /**
     * Mock delete_post_meta function
     * 
     * @param int $post_id Post ID
     * @param string $meta_key Meta key
     * @return bool
     */
    public static function delete_post_meta($post_id, $meta_key)
    {
        $key = "{$post_id}:{$meta_key}";
        unset(self::$post_meta[$key]);
        return true;
    }

    /**
     * Mock get_user_meta function
     * 
     * @param int $user_id User ID
     * @param string $meta_key Meta key
     * @param bool $single Return single value
     * @return mixed
     */
    public static function get_user_meta($user_id, $meta_key, $single = false)
    {
        $key = "{$user_id}:{$meta_key}";
        if (!isset(self::$user_meta[$key])) {
            return $single ? '' : [];
        }
        return $single ? self::$user_meta[$key] : [self::$user_meta[$key]];
    }

    /**
     * Mock update_user_meta function
     * 
     * @param int $user_id User ID
     * @param string $meta_key Meta key
     * @param mixed $meta_value Meta value
     * @return bool
     */
    public static function update_user_meta($user_id, $meta_key, $meta_value)
    {
        $key = "{$user_id}:{$meta_key}";
        self::$user_meta[$key] = $meta_value;
        return true;
    }

    /**
     * Mock __() translation function
     * 
     * @param string $text Text to translate
     * @param string $domain Text domain
     * @return string
     */
    public static function __($text, $domain = 'default')
    {
        return $text;
    }

    /**
     * Mock _e() echo translation function
     * 
     * @param string $text Text to translate
     * @param string $domain Text domain
     * @return void
     */
    public static function _e($text, $domain = 'default')
    {
        echo self::__($text, $domain);
    }

    /**
     * Mock esc_html() function
     * 
     * @param string $text Text to escape
     * @return string
     */
    public static function esc_html($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Mock esc_attr() function
     * 
     * @param string $text Text to escape
     * @return string
     */
    public static function esc_attr($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Mock wp_kses_post() function
     * 
     * @param string $text Text to sanitize
     * @return string
     */
    public static function wp_kses_post($text)
    {
        return $text;
    }

    /**
     * Mock sanitize_text_field() function
     * 
     * @param string $text Text to sanitize
     * @return string
     */
    public static function sanitize_text_field($text)
    {
        return trim($text);
    }

    /**
     * Mock current_user_can() function
     * 
     * @param string $capability Capability to check
     * @param mixed $args Additional args
     * @return bool
     */
    public static function current_user_can($capability, ...$args)
    {
        return true;
    }

    /**
     * Mock wp_safe_remote_post() function
     * 
     * @param string $url URL to post to
     * @param array $args Request arguments
     * @return array|mixed
     */
    public static function wp_safe_remote_post($url, $args = array())
    {
        return ['response' => ['code' => 200]];
    }

    /**
     * Mock wp_safe_remote_get() function
     * 
     * @param string $url URL to fetch
     * @param array $args Request arguments
     * @return array|mixed
     */
    public static function wp_safe_remote_get($url, $args = array())
    {
        return ['response' => ['code' => 200], 'body' => ''];
    }
}
