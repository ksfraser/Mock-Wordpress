<?php
/**
 * WordPress Database Mock
 * 
 * @package ksfraser\MockWordPress
 * @requirement REQ-TEST-003 - Provide database mock for SQL tests
 */

namespace ksfraser\MockWordPress\Mock;

/**
 * Mock WPDB - WordPress Database class
 * 
 * Simulates WordPress $wpdb global for testing database operations
 * without requiring actual database file.
 */
class WPDB
{
    /**
     * Simulated query results
     * 
     * @var array
     */
    public $last_result = [];

    /**
     * Last query executed
     * 
     * @var string
     */
    public $last_query = '';

    /**
     * Error message from last query
     * 
     * @var string
     */
    public $last_error = '';

    /**
     * Number of rows affected by last query
     * 
     * @var int
     */
    public $rows_affected = 0;

    /**
     * Table prefixes
     * 
     * @var string
     */
    public $prefix = 'wp_';
    public $base_prefix = 'wp_';

    /**
     * Prepared statement templates
     * 
     * @var array
     */
    private $prepared_statements = [];

    /**
     * Query log for debugging
     * 
     * @var array
     */
    private $query_log = [];

    /**
     * Prepare a query for execution
     * 
     * Mimics WordPress prepared statements with %s, %d, %f placeholders
     * 
     * @param string $query Query template
     * @param array $values Values to escape
     * @return string Prepared query
     */
    public function prepare($query, $values = array())
    {
        if (!is_array($values)) {
            $values = array_slice(func_get_args(), 1);
        }

        $placeholders = [
            '%d' => '/(%d)/',
            '%f' => '/(%f)/',
            '%s' => '/(%s)/',
            '%i' => '/(%i)/',
        ];

        foreach ($values as $value) {
            $query = preg_replace_callback(
                '/%[dfs]/',
                function () use ($value) {
                    if (is_numeric($value)) {
                        return (int)$value;
                    }
                    return "'" . addslashes($value) . "'";
                },
                $query,
                1
            );
        }

        return $query;
    }

    /**
     * Execute a query
     * 
     * @param string $query Query to execute
     * @return null|false|int
     */
    public function query($query)
    {
        $this->last_query = $query;
        $this->query_log[] = ['query' => $query, 'timestamp' => microtime(true)];

        // For SELECT queries, simulate return of number of rows
        if (stripos($query, 'SELECT') === 0) {
            return count($this->last_result);
        }

        // For INSERT/UPDATE/DELETE, simulate success
        $this->rows_affected = 1;
        return $this->rows_affected;
    }

    /**
     * Get a variable from query result
     * 
     * @param string|null $query Query to execute
     * @param int $x Column offset
     * @param int $y Row offset
     * @return mixed
     */
    public function get_var($query = null, $x = 0, $y = 0)
    {
        if ($query !== null) {
            $this->query($query);
        }

        if (empty($this->last_result)) {
            return null;
        }

        $row = $this->last_result[$y] ?? null;
        if ($row === null) {
            return null;
        }

        $values = array_values((array)$row);
        return $values[$x] ?? null;
    }

    /**
     * Get a row from query result
     * 
     * @param string|null $query Query to execute
     * @param string $output Output format (OBJECT, ARRAY_A, ARRAY_N)
     * @param int $row_offset Row number
     * @return mixed
     */
    public function get_row($query = null, $output = 'OBJECT', $row_offset = 0)
    {
        if ($query !== null) {
            $this->query($query);
        }

        if (empty($this->last_result)) {
            return null;
        }

        $row = $this->last_result[$row_offset] ?? null;
        if ($row === null) {
            return null;
        }

        if ($output === 'ARRAY_A') {
            return (array)$row;
        } elseif ($output === 'ARRAY_N') {
            return array_values((array)$row);
        }

        return $row;
    }

    /**
     * Get results from query
     * 
     * @param string|null $query Query to execute
     * @param string $output Output format
     * @return mixed
     */
    public function get_results($query = null, $output = 'OBJECT')
    {
        if ($query !== null) {
            $this->query($query);
        }

        if (empty($this->last_result)) {
            return [];
        }

        if ($output === 'ARRAY_A') {
            return array_map(function ($row) {
                return (array)$row;
            }, $this->last_result);
        } elseif ($output === 'ARRAY_N') {
            return array_map(function ($row) {
                return array_values((array)$row);
            }, $this->last_result);
        }

        return $this->last_result;
    }

    /**
     * Get the ID of the last inserted row
     * 
     * @return int
     */
    public function getLastInsertId()
    {
        return 1;
    }

    /**
     * Set query results for testing
     * 
     * @param array $results Array of results
     * @return void
     */
    public function setQueryResults($results)
    {
        $this->last_result = $results;
    }

    /**
     * Get query log
     * 
     * @return array
     */
    public function getQueryLog()
    {
        return $this->query_log;
    }

    /**
     * Reset the mock
     * 
     * @return void
     */
    public function reset()
    {
        $this->last_result = [];
        $this->last_query = '';
        $this->last_error = '';
        $this->rows_affected = 0;
        $this->query_log = [];
    }

    /**
     * Escape a string for database
     * 
     * @param string $data Data to escape
     * @return string
     */
    public function esc_like($data)
    {
        return addslashes($data);
    }
}
