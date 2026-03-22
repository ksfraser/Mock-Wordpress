<?php
/**
 * WordPress User Factory
 * 
 * @package ksfraser\MockWordPress
 * @requirement REQ-TEST-004 - Provide fluent test data builders
 */

namespace ksfraser\MockWordPress\Factory;

/**
 * Factory for creating mock WordPress users with fluent interface
 * 
 * Example:
 * ```php
 * $user = (new UserFactory())
 *     ->login('testuser')
 *     ->email('test@example.com')
 *     ->role('administrator')
 *     ->build();
 * ```
 */
class UserFactory
{
    /**
     * User data
     * 
     * @var array
     */
    private $data = [
        'ID' => 1,
        'user_login' => 'testuser',
        'user_pass' => 'password',
        'user_nicename' => 'testuser',
        'user_email' => 'test@example.com',
        'user_url' => '',
        'user_registered' => '2026-01-01 00:00:00',
        'user_activation_key' => '',
        'user_status' => '0',
        'display_name' => 'Test User',
    ];

    /**
     * Static counter for generating IDs
     * 
     * @var int
     */
    private static $id_counter = 0;

    /**
     * User roles/capabilities
     * 
     * @var array
     */
    private $roles = ['subscriber'];

    /**
     * Create a new instance
     */
    public function __construct()
    {
        self::$id_counter++;
        $this->data['ID'] = self::$id_counter;
    }

    /**
     * Set user ID
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
     * Set user login
     * 
     * @param string $login
     * @return self
     */
    public function login($login)
    {
        $this->data['user_login'] = $login;
        $this->data['user_nicename'] = sanitize_user($login);
        return $this;
    }

    /**
     * Set user email
     * 
     * @param string $email
     * @return self
     */
    public function email($email)
    {
        $this->data['user_email'] = $email;
        return $this;
    }

    /**
     * Set user password
     * 
     * @param string $password
     * @return self
     */
    public function password($password)
    {
        $this->data['user_pass'] = wp_hash_password($password);
        return $this;
    }

    /**
     * Set display name
     * 
     * @param string $name
     * @return self
     */
    public function displayName($name)
    {
        $this->data['display_name'] = $name;
        return $this;
    }

    /**
     * Set user role
     * 
     * @param string $role
     * @return self
     */
    public function role($role)
    {
        $this->roles = [$role];
        return $this;
    }

    /**
     * Add user role
     * 
     * @param string $role
     * @return self
     */
    public function addRole($role)
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    /**
     * Make user an administrator
     * 
     * @return self
     */
    public function administrator()
    {
        return $this->role('administrator');
    }

    /**
     * Make user a shop manager
     * 
     * @return self
     */
    public function shopManager()
    {
        return $this->role('shop_manager');
    }

    /**
     * Make user a customer
     * 
     * @return self
     */
    public function customer()
    {
        return $this->role('customer');
    }

    /**
     * Build and return the user object
     * 
     * @return object
     */
    public function build()
    {
        $user = (object)$this->data;
        $user->roles = $this->roles;
        return $user;
    }

    /**
     * Build and return as array
     * 
     * @return array
     */
    public function buildArray()
    {
        $data = $this->data;
        $data['roles'] = $this->roles;
        return $data;
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
 * Helper functions to mimic WordPress behavior
 */

function sanitize_user($user, $strict = false)
{
    $user = wp_check_plain_text($user);

    if ($strict) {
        $user = preg_replace('/[^a-z0-9\-._@]/i', '', $user);
    } else {
        $user = trim($user);
    }

    return $user;
}

function wp_check_plain_text($user)
{
    return (string)$user;
}

function wp_hash_password($password, $user_id = '')
{
    // Simple mock - just return MD5 hash
    return md5($password);
}
