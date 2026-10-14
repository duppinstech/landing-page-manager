---
name: testing-patterns
description: How to write tests for Landing Page Manager — PHPUnit with Brain Monkey for unit tests, wp-phpunit for integration, mocking wp_remote_* and Carbon Fields. Use when adding tests, setting up the test harness, or writing a regression test for a bug fix.
---

# Testing patterns

## Harness (to be added — PLAN.md P1)
```
composer require --dev phpunit/phpunit:^9 brain/monkey:^2 yoast/phpunit-polyfills:^1
tests/
  bootstrap.php        # require vendor/autoload.php; Brain\Monkey\setUp in TestCase
  unit/                # no WordPress loaded
  integration/         # wp-phpunit, WP_TESTS_DIR
phpunit.xml.dist
```
PHPUnit 9 is the newest version that still runs on PHP 7.4.

## Unit test skeleton (Brain Monkey)
```php
use Brain\Monkey;
use Brain\Monkey\Functions;

class Test_Update_Checker extends \PHPUnit\Framework\TestCase {
    protected function setUp(): void { parent::setUp(); Monkey\setUp(); }
    protected function tearDown(): void { Monkey\tearDown(); parent::tearDown(); }

    public function test_version_compare_strips_v_prefix() {
        Functions\when( 'get_option' )->justReturn( 'github' );
        // Use reflection for private static methods only when no public seam exists.
    }
}
```

## Patterns
- **HTTP**: `Functions\expect( 'wp_remote_get' )->andReturn( [...] )`, or `pre_http_request` in integration tests.
- **Carbon Fields**: stub `carbon_get_theme_option` and `carbon_get_post_meta` with `Functions\when()`.
- **Pro gating**: test both `lpmanager_is_premium()` true and false by defining or not defining `lpmanager_pro_loaded`.
- **Rewrite / host parsing**: feed `$_SERVER['HTTP_HOST']` variants (apex, www, client subdomain, port).
- **Regression test**: name it after the bug (`test_ajax_url_uses_current_subdomain`) and confirm it fails before the fix.
