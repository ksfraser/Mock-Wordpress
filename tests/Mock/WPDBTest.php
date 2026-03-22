<?php
/**
 * Unit Tests for WPDB Mock
 * 
 * @package ksfraser\MockWordPress\Tests
 * @requirement REQ-TEST-003 - Verify database mock works correctly
 */

namespace ksfraser\MockWordPress\Tests;

use PHPUnit\Framework\TestCase;
use ksfraser\MockWordPress\Mock\WPDB;

/**
 * Test WPDB Mock Database
 * 
 * @covers \ksfraser\MockWordPress\Mock\WPDB
 */
class WPDBTest extends TestCase
{
    /**
     * Test prepare escapes values correctly
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WPDB::prepare
     */
    public function testPrepareEscapesSValues()
    {
        $wpdb = new WPDB();
        $query = $wpdb->prepare("SELECT * FROM posts WHERE ID = %d", 123);

        $this->assertStringContainsString('123', $query);
    }

    /**
     * Test prepare with string placeholder
     * 
     * @test
     */
    public function testPrepareWithStringPlaceholder()
    {
        $wpdb = new WPDB();
        $query = $wpdb->prepare("SELECT * FROM posts WHERE post_title = %s", 'test title');

        $this->assertStringContainsString("'test title'", $query);
    }

    /**
     * Test prepare with multiple placeholders
     * 
     * @test
     */
    public function testPrepareWithMultiplePlaceholders()
    {
        $wpdb = new WPDB();
        $query = $wpdb->prepare(
            "SELECT * FROM posts WHERE ID = %d AND status = %s",
            [123, 'publish']
        );

        $this->assertStringContainsString('123', $query);
        $this->assertStringContainsString('publish', $query);
    }

    /**
     * Test query method stores last query
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WPDB::query
     */
    public function testQueryStoresLastQuery()
    {
        $wpdb = new WPDB();
        $sql = "SELECT * FROM posts";

        $wpdb->query($sql);

        $this->assertEquals($sql, $wpdb->last_query);
    }

    /**
     * Test get_var returns single value
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WPDB::get_var
     */
    public function testGetVarReturnsSingleValue()
    {
        $wpdb = new WPDB();
        $wpdb->setQueryResults([
            (object)['id' => 1, 'name' => 'Test'],
            (object)['id' => 2, 'name' => 'Test2'],
        ]);

        $result = $wpdb->get_var(null, 0, 0);
        $this->assertEquals(1, $result);
    }

    /**
     * Test get_row returns single row
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WPDB::get_row
     */
    public function testGetRowReturnsSingleRow()
    {
        $wpdb = new WPDB();
        $row_data = (object)['id' => 1, 'name' => 'Test'];
        $wpdb->setQueryResults([$row_data]);

        $result = $wpdb->get_row();

        $this->assertIsObject($result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('Test', $result->name);
    }

    /**
     * Test get_row with ARRAY_A format
     * 
     * @test
     */
    public function testGetRowWithArrayAFormat()
    {
        $wpdb = new WPDB();
        $wpdb->setQueryResults([(object)['id' => 1, 'name' => 'Test']]);

        $result = $wpdb->get_row(null, 'ARRAY_A');

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
    }

    /**
     * Test get_results returns all rows
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WPDB::get_results
     */
    public function testGetResultsReturnsAllRows()
    {
        $wpdb = new WPDB();
        $rows = [
            (object)['id' => 1, 'name' => 'Test1'],
            (object)['id' => 2, 'name' => 'Test2'],
        ];
        $wpdb->setQueryResults($rows);

        $results = $wpdb->get_results();

        $this->assertCount(2, $results);
    }

    /**
     * Test get_results with ARRAY_A format
     * 
     * @test
     */
    public function testGetResultsWithArrayAFormat()
    {
        $wpdb = new WPDB();
        $rows = [
            (object)['id' => 1, 'name' => 'Test1'],
            (object)['id' => 2, 'name' => 'Test2'],
        ];
        $wpdb->setQueryResults($rows);

        $results = $wpdb->get_results(null, 'ARRAY_A');

        $this->assertIsArray($results[0]);
        $this->assertEquals('Test1', $results[0]['name']);
    }

    /**
     * Test getQueryLog tracks queries
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WPDB::getQueryLog
     */
    public function testGetQueryLogTracksQueries()
    {
        $wpdb = new WPDB();
        $wpdb->query("SELECT * FROM posts");
        $wpdb->query("SELECT * FROM users");

        $log = $wpdb->getQueryLog();

        $this->assertCount(2, $log);
        $this->assertStringContainsString('SELECT * FROM posts', $log[0]['query']);
    }

    /**
     * Test reset clears data
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WPDB::reset
     */
    public function testResetClearsData()
    {
        $wpdb = new WPDB();
        $wpdb->query("SELECT * FROM posts");
        $wpdb->setQueryResults([(object)['id' => 1]]);

        $wpdb->reset();

        $this->assertEquals('', $wpdb->last_query);
        $this->assertEmpty($wpdb->last_result);
        $this->assertEmpty($wpdb->getQueryLog());
    }

    /**
     * Test esc_like escapes database wildcards
     * 
     * @test
     * @covers \ksfraser\MockWordPress\Mock\WPDB::esc_like
     */
    public function testEscLikeEscapesWildcards()
    {
        $wpdb = new WPDB();
        $result = $wpdb->esc_like("test'value");

        $this->assertStringContainsString("test\\'value", $result);
    }

    /**
     * Test get_var returns null for non-existent results
     * 
     * @test
     */
    public function testGetVarReturnsNullForNoResults()
    {
        $wpdb = new WPDB();
        $wpdb->setQueryResults([]);

        $result = $wpdb->get_var();

        $this->assertNull($result);
    }

    /**
     * Test get_row returns null for non-existent row
     * 
     * @test
     */
    public function testGetRowReturnsNullForNoResults()
    {
        $wpdb = new WPDB();
        $wpdb->setQueryResults([]);

        $result = $wpdb->get_row();

        $this->assertNull($result);
    }
}
