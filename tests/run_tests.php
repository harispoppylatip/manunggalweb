#!/usr/bin/env php
<?php
/**
 * Test runner for ManunggalWeb
 * Runs all test files in the tests directory
 * 
 * Usage: php tests/run_tests.php
 */

echo "\n";
echo "╔════════════════════════════════════════════════╗\n";
echo "║   ManunggalWeb Test Suite                     ║\n";
echo "╚════════════════════════════════════════════════╝\n";
echo "\n";

$testFiles = [
    'ConfigTest.php',
    'ApiTest.php'
];

$totalPassed = 0;
$totalFailed = 0;

foreach ($testFiles as $testFile) {
    $filePath = __DIR__ . '/' . $testFile;
    
    if (!file_exists($filePath)) {
        echo "⚠ Warning: Test file not found: $testFile\n";
        continue;
    }
    
    echo "Running: $testFile\n";
    echo str_repeat("-", 50) . "\n";
    
    // Execute test file and capture output
    $output = [];
    $returnCode = 0;
    exec("php " . escapeshellarg($filePath) . " 2>&1", $output, $returnCode);
    
    echo implode("\n", $output) . "\n";
    
    // Parse results
    foreach ($output as $line) {
        if (preg_match('/Tests Passed: (\d+)/', $line, $matches)) {
            $totalPassed += (int)$matches[1];
        }
        if (preg_match('/Tests Failed: (\d+)/', $line, $matches)) {
            $totalFailed += (int)$matches[1];
        }
    }
    
    echo "\n";
}

echo "╔════════════════════════════════════════════════╗\n";
echo "║   Test Summary                                 ║\n";
echo "╚════════════════════════════════════════════════╝\n";
echo "Total Tests Passed: $totalPassed\n";
echo "Total Tests Failed: $totalFailed\n";
echo "\n";

if ($totalFailed > 0) {
    echo "❌ Some tests failed!\n";
    exit(1);
} else {
    echo "✅ All tests passed!\n";
    exit(0);
}
