<?php
/**
 * Test file for config.php helper functions
 * Run with: php tests/ConfigTest.php
 */

// Include the config file
require_once __DIR__ . '/../public/config.php';

class ConfigTest {
    private $testsPassed = 0;
    private $testsFailed = 0;
    
    public function run() {
        echo "Running Config Tests...\n";
        echo str_repeat("=", 50) . "\n";
        
        $this->testSanitizeInput();
        $this->testValidateApiKey();
        
        echo str_repeat("=", 50) . "\n";
        echo "Tests Passed: {$this->testsPassed}\n";
        echo "Tests Failed: {$this->testsFailed}\n";
        
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
    
    private function testSanitizeInput() {
        echo "\nTest: sanitizeInput()\n";
        
        // Test integer sanitization
        $result = sanitizeInput('42', 'int');
        $this->assert($result === 42, "Should convert string to int");
        
        // Test float sanitization
        $result = sanitizeInput('3.14', 'float');
        $this->assert($result === 3.14, "Should convert string to float");
        
        // Test boolean sanitization
        $result = sanitizeInput('1', 'bool');
        $this->assert($result === true, "Should convert '1' to true");
        
        $result = sanitizeInput('0', 'bool');
        $this->assert($result === false, "Should convert '0' to false");
        
        // Test string sanitization
        $result = sanitizeInput('  hello  ', 'string');
        $this->assert($result === 'hello', "Should trim whitespace from string");
        
        // Test null handling
        $result = sanitizeInput(null, 'int');
        $this->assert($result === null, "Should return null when input is null");
    }
    
    private function testValidateApiKey() {
        echo "\nTest: validateApiKey()\n";
        
        // Test valid API key
        $result = validateApiKey('GROWY_SECRET_123');
        $this->assert($result === true, "Should validate correct API key");
        
        // Test invalid API key
        $result = validateApiKey('WRONG_KEY');
        $this->assert($result === false, "Should reject incorrect API key");
        
        // Test empty API key
        $result = validateApiKey('');
        $this->assert($result === false, "Should reject empty API key");
    }
}

// Run the tests
$test = new ConfigTest();
$success = $test->run();

exit($success ? 0 : 1);
