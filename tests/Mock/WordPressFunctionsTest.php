<?php
/**
 * Unit Tests for WordPress Functions Mock
 * 
 * @package ksfraser\MockWordPress\Tests
 * @requirement REQ-TEST-001 - Verify WordPress functions mock works correctly
 */

namespace ksfraser\MockWordPress\Tests;

use PHPUnit\Framework\TestCase;
use ksfraser\MockWordPress\Mock\WordPressFunctions;

/**
 * Test WordPress Functions Mock
 * 
 * @covers \ksfraser\MockWordPress\Mock\WordPressFunctions
 */
class WordPressFunctionsTest extends TestCase
{
    /**
     * @before
     */
    public function resetMocks()
    {
        WordPressFunctions::reset();
    }

    /**
     * Test get_option returns default value when not set
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressFunctions::get_option
     */
    public function testGetOptionReturnsDefaultWhenNotSet()
    {
        $result = WordPressFunctions::get_option('nonexistent', 'default_value');
        $this->assertEquals('default_value', $result);
    }

    /**
     * Test update_option and get_option work together
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressFunctions::update_option
     * @covers \ksfraser\MockWordPress\Mock\WordPressFunctions::get_option
     */
    public function testUpdateAndGetOption()
    {
        WordPressFunctions::update_option('test_key', 'test_value');
        $result = WordPressFunctions::get_option('test_key');
        $this->assertEquals('test_value', $result);
    }

    /**
     * Test delete_option removes option
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressFunctions::delete_option
     */
    public function testDeleteOptionRemovesOption()
    {
        WordPressFunctions::update_option('test_key', 'test_value');
        WordPressFunctions::delete_option('test_key');
        $result = WordPressFunctions::get_option('test_key', 'default');
        $this->assertEquals('default', $result);
    }

    /**
     * Test post meta operations
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressFunctions::update_post_meta
     * @covers \ksfraser\MockWordPress\Mock\WordPressFunctions::get_post_meta
     */
    public function testPostMetaOperations()
    {
        $post_id = 1;
        $meta_key = '_test_meta';
        $meta_value = 'test_value';

        WordPressFunctions::update_post_meta($post_id, $meta_key, $meta_value);
        $result = WordPressFunctions::get_post_meta($post_id, $meta_key, true);

        $this->assertEquals($meta_value, $result);
    }

    /**
     * Test user meta operations
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressFunctions::update_user_meta
     * @covers \ksfraser\MockWordPress\Mock\WordPressFunctions::get_user_meta
     */
    public function testUserMetaOperations()
    {
        $user_id = 1;
        $meta_key = '_user_setting';
        $meta_value = ['key' => 'value'];

        WordPressFunctions::update_user_meta($user_id, $meta_key, $meta_value);
        $result = WordPressFunctions::get_user_meta($user_id, $meta_key, true);

        $this->assertEquals($meta_value, $result);
    }

    /**
     * Test escaping functions
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressFunctions::esc_html
     * @covers \ksfraser\MockWordPress\Mock\WordPressFunctions::esc_attr
     */
    public function testEscapingFunctions()
    {
        $dirty = '<script>alert("xss")</script>';
        $clean = WordPressFunctions::esc_html($dirty);
        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringContainsString('&lt;', $clean);
    }

    /**
     * Test sanitization functions
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressFunctions::sanitize_text_field
     */
    public function testSanitizeTextField()
    {
        $text = '  test text with spaces  ';
        $result = WordPressFunctions::sanitize_text_field($text);
        $this->assertEquals('test text with spaces', $result);
    }

    /**
     * Test current_user_can always returns true (for basic testing)
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressFunctions::current_user_can
     */
    public function testCurrentUserCan()
    {
        $result = WordPressFunctions::current_user_can('manage_options');
        $this->assertTrue($result);
    }

    /**
     * Test translation functions
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressFunctions::__
     */
    public function testTranslationFunction()
    {
        $text = 'Hello World';
        $result = WordPressFunctions::__($text);
        $this->assertEquals($text, $result);
    }

    /**
     * Test reset clears all stored data
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WordPressFunctions::reset
     */
    public function testResetClearsAllData()
    {
        WordPressFunctions::update_option('key1', 'value1');
        WordPressFunctions::update_post_meta(1, 'meta', 'value');

        WordPressFunctions::reset();

        $this->assertEquals('default', WordPressFunctions::get_option('key1', 'default'));
        $this->assertEquals('', WordPressFunctions::get_post_meta(1, 'meta', true));
    }
}
