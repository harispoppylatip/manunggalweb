<?php
/**
 * Test file for API endpoints
 * Run with: php tests/ApiTest.php
 * 
 * Note: These tests require a running database connection
 */

class ApiTest {
    private $testsPassed = 0;
    private $testsFailed = 0;
    private $baseUrl = 'http://localhost:5500';
    
    public function run() {
        echo "Running API Tests...\n";
        echo str_repeat("=", 50) . "\n";
        
        $this->testSendDataEndpoint();
        $this->testGetRealtimeEndpoint();
        $this->testMoistureConfigEndpoint();
        
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
    
    private function testSendDataEndpoint() {
        echo "\nTest: senddata.php endpoint\n";
        
        $data = [
            'suhu' => 25.5,
            'kelembapan_tanah' => 60,
            'ph' => 6.8,
            'relay' => 1
        ];
        
        $ch = curl_init($this->baseUrl . '/senddata.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-Key: GROWY_SECRET_123'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            echo "  ⚠ SKIP: Server not running (expected for CI/CD)\n";
            return;
        }
        
        $this->assert($httpCode === 200, "Should return 200 OK with valid API key");
        
        $result = json_decode($response, true);
        $this->assert(isset($result['ok']) && $result['ok'] === true, "Should return success response");
    }
    
    private function testGetRealtimeEndpoint() {
        echo "\nTest: get_realtime.php endpoint\n";
        
        $ch = curl_init($this->baseUrl . '/get_realtime.php?hours=1');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            echo "  ⚠ SKIP: Server not running (expected for CI/CD)\n";
            return;
        }
        
        $this->assert($httpCode === 200, "Should return 200 OK");
        
        $result = json_decode($response, true);
        $this->assert(is_array($result), "Should return array response");
        $this->assert(isset($result['ok']), "Should have 'ok' field in response");
    }
    
    private function testMoistureConfigEndpoint() {
        echo "\nTest: moisture_config.php endpoint\n";
        
        $ch = curl_init($this->baseUrl . '/moisture_config.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            echo "  ⚠ SKIP: Server not running (expected for CI/CD)\n";
            return;
        }
        
        $this->assert($httpCode === 200, "Should return 200 OK");
        
        $result = json_decode($response, true);
        $this->assert(is_array($result), "Should return array response");
        $this->assert(isset($result['ok']), "Should have 'ok' field in response");
    }
}

// Run the tests
$test = new ApiTest();
$success = $test->run();

exit($success ? 0 : 1);
