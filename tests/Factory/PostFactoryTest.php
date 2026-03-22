<?php
/**
 * Unit Tests for Post Factory
 * 
 * @package ksfraser\MockWordPress\Tests
 * @requirement REQ-TEST-004 - Verify fluent post builder works correctly
 */

namespace ksfraser\MockWordPress\Tests;

use PHPUnit\Framework\TestCase;
use ksfraser\MockWordPress\Factory\PostFactory;

/**
 * Test Post Factory
 * 
 * @covers \ksfraser\MockWordPress\Factory\PostFactory
 */
class PostFactoryTest extends TestCase
{
    /**
     * @before
     */
    public function resetFactory()
    {
        PostFactory::resetIdCounter();
    }

    /**
     * Test basic build creates post object
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Factory\PostFactory::build
     */
    public function testBasicBuildCreatesPost()
    {
        $post = (new PostFactory())->build();

        $this->assertIsObject($post);
        $this->assertObjectHasAttribute('ID', $post);
        $this->assertObjectHasAttribute('post_title', $post);
    }

    /**
     * Test title fluent method
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Factory\PostFactory::title
     */
    public function testTitleFluentMethod()
    {
        $post = (new PostFactory())
            ->title('My Test Post')
            ->build();

        $this->assertEquals('My Test Post', $post->post_title);
    }

    /**
     * Test type fluent method
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Factory\PostFactory::type
     */
    public function testTypeFluentMethod()
    {
        $post = (new PostFactory())
            ->type('page')
            ->build();

        $this->assertEquals('page', $post->post_type);
    }

    /**
     * Test status fluent methods
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Factory\PostFactory::status
     * @covers \ksfraser\MockWordPress\Factory\PostFactory::published
     * @covers \ksfraser\MockWordPress\Factory\PostFactory::draft
     */
    public function testStatusFluentMethods()
    {
        $published = (new PostFactory())->published()->build();
        $this->assertEquals('publish', $published->post_status);

        PostFactory::resetIdCounter();

        $draft = (new PostFactory())->draft()->build();
        $this->assertEquals('draft', $draft->post_status);
    }

    /**
     * Test author fluent method
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Factory\PostFactory::author
     */
    public function testAuthorFluentMethod()
    {
        $post = (new PostFactory())
            ->author(42)
            ->build();

        $this->assertEquals(42, $post->post_author);
    }

    /**
     * Test content fluent method
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Factory\PostFactory::content
     */
    public function testContentFluentMethod()
    {
        $content = 'This is the post content';
        $post = (new PostFactory())
            ->content($content)
            ->build();

        $this->assertEquals($content, $post->post_content);
    }

    /**
     * Test excerpt fluent method
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Factory\PostFactory::excerpt
     */
    public function testExcerptFluentMethod()
    {
        $excerpt = 'This is the excerpt';
        $post = (new PostFactory())
            ->excerpt($excerpt)
            ->build();

        $this->assertEquals($excerpt, $post->post_excerpt);
    }

    /**
     * Test date fluent method
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Factory\PostFactory::date
     */
    public function testDateFluentMethod()
    {
        $date = '2026-06-15 10:30:00';
        $post = (new PostFactory())
            ->date($date)
            ->build();

        $this->assertEquals($date, $post->post_date);
        $this->assertEquals($date, $post->post_date_gmt);
    }

    /**
     * Test auto-incrementing IDs
     * 
     * @test
     */
    public function testAutoIncrementingIds()
    {
        PostFactory::resetIdCounter();

        $post1 = (new PostFactory())->build();
        $post2 = (new PostFactory())->build();
        $post3 = (new PostFactory())->build();

        $this->assertEquals(1, $post1->ID);
        $this->assertEquals(2, $post2->ID);
        $this->assertEquals(3, $post3->ID);
    }

    /**
     * Test buildArray returns array
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Factory\PostFactory::buildArray
     */
    public function testBuildArrayReturnsArray()
    {
        $post_array = (new PostFactory())
            ->title('Array Post')
            ->buildArray();

        $this->assertIsArray($post_array);
        $this->assertEquals('Array Post', $post_array['post_title']);
    }

    /**
     * Test method chaining
     * 
     * @test
     */
    public function testMethodChaining()
    {
        $post = (new PostFactory())
            ->title('Chained Post')
            ->type('post')
            ->status('publish')
            ->author(1)
            ->content('Some content')
            ->excerpt('Some excerpt')
            ->build();

        $this->assertEquals('Chained Post', $post->post_title);
        $this->assertEquals('post', $post->post_type);
        $this->assertEquals('publish', $post->post_status);
        $this->assertEquals(1, $post->post_author);
        $this->assertEquals('Some content', $post->post_content);
        $this->assertEquals('Some excerpt', $post->post_excerpt);
    }

    /**
     * Test explicit ID assignment
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Factory\PostFactory::id
     */
    public function testExplicitIdAssignment()
    {
        $post = (new PostFactory())
            ->id(999)
            ->build();

        $this->assertEquals(999, $post->ID);
    }
}
