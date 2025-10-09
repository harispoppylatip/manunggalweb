# Test Suite for ManunggalWeb

This directory contains automated tests for the ManunggalWeb IoT Dashboard application.

## Test Files

- **ConfigTest.php** - Tests for helper functions in `config.php`
  - Tests input sanitization (int, float, bool, string)
  - Tests API key validation
  - Tests null handling

- **ApiTest.php** - Tests for API endpoints
  - Tests `senddata.php` endpoint with API key authentication
  - Tests `get_realtime.php` endpoint
  - Tests `moisture_config.php` endpoint
  - Note: These tests require a running server

- **run_tests.php** - Test runner that executes all tests

## Running Tests

### Run All Tests

```bash
php tests/run_tests.php
```

### Run Individual Test Files

```bash
# Test config helper functions
php tests/ConfigTest.php

# Test API endpoints (requires running server)
php tests/ApiTest.php
```

## Test Requirements

- **PHP 7.4+** (or the version used by your server)
- **ConfigTest.php** - No dependencies, runs standalone
- **ApiTest.php** - Requires:
  - Running web server (e.g., via `docker-compose up`)
  - Database connection
  - curl extension enabled

## Test Output

The test runner provides:
- ✓ PASS - Test passed successfully
- ✗ FAIL - Test failed
- ⚠ SKIP - Test skipped (e.g., server not running)

Example output:
```
╔════════════════════════════════════════════════╗
║   ManunggalWeb Test Suite                     ║
╚════════════════════════════════════════════════╝

Running: ConfigTest.php
==================================================
✓ PASS: Should convert string to int
✓ PASS: Should convert string to float
...
==================================================
Tests Passed: 9
Tests Failed: 0

✅ All tests passed!
```

## Adding New Tests

To add new tests:

1. Create a new test file in the `tests/` directory
2. Follow the pattern from existing test files:
   ```php
   <?php
   class MyNewTest {
       private $testsPassed = 0;
       private $testsFailed = 0;
       
       public function run() {
           // Your test methods
           return $this->testsFailed === 0;
       }
       
       private function assert($condition, $message) {
           if ($condition) {
               echo "✓ PASS: $message\n";
               $this->testsPassed++;
           } else {
               echo "✗ FAIL: $message\n";
               $this->testsFailed++;
           }
       }
   }
   
   $test = new MyNewTest();
   exit($test->run() ? 0 : 1);
   ```
3. Add the test file to `run_tests.php` in the `$testFiles` array

## CI/CD Integration

The test suite can be integrated into CI/CD pipelines:

```bash
# Exit with non-zero code if tests fail
php tests/run_tests.php || exit 1
```

## Notes

- Config tests run without requiring a database connection
- API tests gracefully skip when the server is not available
- All tests output results in a standard format for easy parsing
