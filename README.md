# Mock WordPress

Reusable WordPress mocks and test utilities for unit testing WordPress plugins and themes without requiring a full WordPress installation.

**Namespace**: `ksfraser\MockWordPress\`  
**Requirement**: PHP 7.3+

## Features

- **WordPress Function Mocks** (`WordPressFunctions`) - Core WordPress functions like `get_option()`, `get_post_meta()`, etc.
- **Hook System Mock** (`WordPressHooks`) - Register and track `add_action()`, `add_filter()`, `do_action()`, `apply_filters()`
- **Database Mock** (`WPDB`) - Mock WordPress database operations
- **Test Data Factories** - Fluent builders for creating test WordPress posts and users
- **Hook Assertions** - PHPUnit assertions for verifying hook behavior
- **Base Test Case** - Extended PHPUnit TestCase with WordPress integration

## Installation

```bash
composer require ksfraser/mock-wordpress --dev
```

Or use file-based repository during development:

```json
{
  "repositories": [
    {"type": "path", "url": "../mock-wordpress"}
  ],
  "require-dev": {
    "ksfraser/mock-wordpress": "^1.0"
  }
}
```

## Usage

### Basic Test with Mocks

```php
use ksfraser\MockWordPress\TestCase;
use ksfraser\MockWordPress\Mock\WordPressFunctions;
use ksfraser\MockWordPress\Mock\WordPressHooks;

class MyPluginTest extends TestCase
{
    public function testPluginSetsOption()
    {
        WordPressFunctions::update_option('my_plugin_setting', 'value');
        $result = WordPressFunctions::get_option('my_plugin_setting');
        
        $this->assertEquals('value', $result);
    }
}
```

### Testing Hooks

```php
class HookRegistrationTest extends TestCase
{
    public function testPluginRegistersActionHook()
    {
        // Your plugin code registers hooks...
        $callback = function() { echo 'done'; };
        WordPressHooks::add_action('save_post', $callback);
        
        // Assert
        $this->assertHookRegistered('save_post');
    }

    public function testActionIsExecuted()
    {
        $executed = [];
        WordPressHooks::add_action('my_action', function($value) use (&$executed) {
            $executed[] = $value;
        });
        
        WordPressHooks::do_action('my_action', 'data');
        
        $this->assertActionExecuted('my_action');
        $this->assertContains('data', $executed);
    }
}
```

### Using Test Data Factories

```php
use ksfraser\MockWordPress\Factory\PostFactory;
use ksfraser\MockWordPress\Factory\UserFactory;

class PostProcessingTest extends TestCase
{
    public function testProcessPost()
    {
        $post = (new PostFactory())
            ->title('Test Post')
            ->type('post')
            ->status('publish')
            ->author(1)
            ->content('Post content here')
            ->build();
        
        // Use $post in your test
        $this->assertEquals('Test Post', $post->post_title);
    }

    public function testProcessUser()
    {
        $user = (new UserFactory())
            ->login('testuser')
            ->email('test@example.com')
            ->administrator()
            ->build();
        
        $this->assertContains('administrator', $user->roles);
    }
}
```

### Testing Database Operations

```php
use ksfraser\MockWordPress\Mock\WPDB;

class DatabaseTest extends TestCase
{
    public function testPreparedQueries()
    {
        $wpdb = $this->wpdb; // Inherited from TestCase
        
        $query = $wpdb->prepare(
            "SELECT * FROM posts WHERE ID = %d AND status = %s",
            [123, 'publish']
        );
        
        $this->assertStringContainsString('123', $query);
        $this->assertStringContainsString("'publish'", $query);
    }

    public function testQueryResults()
    {
        $this->setWpdbResults([
            (object)['ID' => 1, 'post_title' => 'Post 1'],
            (object)['ID' => 2, 'post_title' => 'Post 2'],
        ]);
        
        $results = $this->wpdb->get_results();
        
        $this->assertCount(2, $results);
    }
}
```

## Components

### WordPressFunctions

Static methods mimicking common WordPress functions:

- `get_option()` / `update_option()` / `delete_option()`
- `get_post_meta()` / `update_post_meta()` / `delete_post_meta()`
- `get_user_meta()` / `update_user_meta()`
- `esc_html()` / `esc_attr()` / `sanitize_text_field()`
- `__()` / `_e()` (translation functions)
- `current_user_can()`
- `wp_safe_remote_post()` / `wp_safe_remote_get()`

### WordPressHooks

Track and manage WordPress hooks:

- `add_action($hook, $callback, $priority, $accepted_args)`
- `add_filter($hook, $callback, $priority, $accepted_args)`
- `do_action($hook, ...$args)` - Execute action hooks
- `apply_filters($hook, $value, ...$args)` - Apply filter hooks
- `hasHook($hook, $callback)`
- `removeHook($hook, $callback, $priority)`
- `getExecutedActions($hook)` - Get execution history
- `getAppliedFilters($hook)` - Get filter application history
- `reset()` - Clear all hooks

### WPDB

Mock WordPress database operations:

- `prepare($query, $values)` - Prepare queries with placeholders
- `query($query)` - Execute query
- `get_var($query, $x, $y)` - Get single value
- `get_row($query, $output, $row)` - Get single row
- `get_results($query, $output)` - Get all results
- `setQueryResults($results)` - Set results for testing
- `getQueryLog()` - Get executed queries log

### Test Factories

#### PostFactory

Fluent builder for WordPress posts:

```php
$post = (new PostFactory())
    ->id(123)
    ->title('My Post')
    ->type('post')
    ->status('publish')
    ->author(1)
    ->content('Post content')
    ->excerpt('Post excerpt')
    ->date('2026-01-15 10:30:00')
    ->published()  // Helper for status
    ->build();
```

#### UserFactory

Fluent builder for WordPress users:

```php
$user = (new UserFactory())
    ->id(42)
    ->login('username')
    ->email('user@example.com')
    ->password('secure_password')
    ->displayName('User Name')
    ->role('administrator')  // or customer, shop_manager, subscriber
    ->addRole('editor')  // Add additional role
    ->build();
```

### Hook Assertions

PHPUnit assertions for hooks (available in `TestCase`):

```php
$this->assertHookRegistered('save_post');
$this->assertActionExecuted('publish_post');
$this->assertActionNotExecuted('trashed_post');
$this->assertFilterApplied('the_content');
$this->assertActionExecutedTimes('save_post', 3);
```

### Base TestCase

Extend `ksfraser\MockWordPress\TestCase` instead of `PHPUnit\Framework\TestCase`:

```php
use ksfraser\MockWordPress\TestCase;

class MyTest extends TestCase
{
    // setUp() and tearDown() automatically configure mocks
    // $this->wpdb is available
    // Hook assertion methods available
}
```

## Running Tests

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/Mock/WordPressFunctionsTest.php
vendor/bin/phpunit --coverage-html=coverage
```

## Test Coverage

- Tests included for all core components
- Run with coverage reports:
  ```bash
  composer test-coverage
  ```

## Dependencies

- PHPUnit 9.6+
- PHP 7.3+

## Related Packages

- [ksfraser/mock-woocommerce](../mock-woocommerce) - WooCommerce specific mocks
- [ksfraser/test-factories](../test-factories) - Auction-specific test data builders

## License

GPL 3.0 or later

## Contributing

When adding new mocks or factories:
1. Follow PSR-12 code standards
2. Add PHPDoc documentation with `@requirement` tags
3. Include 100% unit test coverage
4. Ensure fluent interfaces for factories
5. Update this README with usage examples
