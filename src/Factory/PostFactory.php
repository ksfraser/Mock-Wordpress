<?php
/**
 * WordPress Post Factory
 * 
 * @package ksfraser\MockWordPress
 * @requirement REQ-TEST-004 - Provide fluent test data builders
 */

namespace ksfraser\MockWordPress\Factory;

/**
 * Factory for creating mock WordPress posts with fluent interface
 * 
 * Example:
 * ```php
 * $post = (new PostFactory())
 *     ->title('My Post')
 *     ->type('post')
 *     ->status('publish')
 *     ->build();
 * ```
 */
class PostFactory
{
    /**
     * Post data
     * 
     * @var array
     */
    private $data = [
        'ID' => 1,
        'post_title' => 'Test Post',
        'post_type' => 'post',
        'post_status' => 'publish',
        'post_author' => 1,
        'post_date' => '2026-01-01 00:00:00',
        'post_date_gmt' => '2026-01-01 00:00:00',
        'post_content' => '',
        'post_excerpt' => '',
        'post_name' => '',
        'guid' => '',
    ];

    /**
     * Static counter for generating IDs
     * 
     * @var int
     */
    private static $id_counter = 0;

    /**
     * Create a new instance
     */
    public function __construct()
    {
        self::$id_counter++;
        $this->data['ID'] = self::$id_counter;
        $this->data['guid'] = "http://example.com/?p=" . self::$id_counter;
        $this->data['post_name'] = sanitize_title($this->data['post_title']);
    }

    /**
     * Set post ID
     * 
     * @param int $id
     * @return self
     */
    public function id($id)
    {
        $this->data['ID'] = $id;
        return $this;
    }

    /**
     * Set post title
     * 
     * @param string $title
     * @return self
     */
    public function title($title)
    {
        $this->data['post_title'] = $title;
        $this->data['post_name'] = sanitize_title($title);
        return $this;
    }

    /**
     * Set post type
     * 
     * @param string $type
     * @return self
     */
    public function type($type)
    {
        $this->data['post_type'] = $type;
        return $this;
    }

    /**
     * Set post status
     * 
     * @param string $status
     * @return self
     */
    public function status($status)
    {
        $this->data['post_status'] = $status;
        return $this;
    }

    /**
     * Set post author
     * 
     * @param int $author_id
     * @return self
     */
    public function author($author_id)
    {
        $this->data['post_author'] = $author_id;
        return $this;
    }

    /**
     * Set post content
     * 
     * @param string $content
     * @return self
     */
    public function content($content)
    {
        $this->data['post_content'] = $content;
        return $this;
    }

    /**
     * Set post excerpt
     * 
     * @param string $excerpt
     * @return self
     */
    public function excerpt($excerpt)
    {
        $this->data['post_excerpt'] = $excerpt;
        return $this;
    }

    /**
     * Set post date
     * 
     * @param string $date (Y-m-d H:i:s)
     * @return self
     */
    public function date($date)
    {
        $this->data['post_date'] = $date;
        $this->data['post_date_gmt'] = $date;
        return $this;
    }

    /**
     * Set post as draft
     * 
     * @return self
     */
    public function draft()
    {
        return $this->status('draft');
    }

    /**
     * Set post as published
     * 
     * @return self
     */
    public function published()
    {
        return $this->status('publish');
    }

    /**
     * Build and return the post object
     * 
     * @return object
     */
    public function build()
    {
        return (object)$this->data;
    }

    /**
     * Build and return as array
     * 
     * @return array
     */
    public function buildArray()
    {
        return $this->data;
    }

    /**
     * Reset the static ID counter
     * 
     * @return void
     */
    public static function resetIdCounter()
    {
        self::$id_counter = 0;
    }
}

/**
 * Helper function to mimic WordPress behavior
 * 
 * @param string $title
 * @return string
 */
function sanitize_title($title)
{
    return strtolower(preg_replace('/[^a-zA-Z0-9\-]/', '-', $title));
}
