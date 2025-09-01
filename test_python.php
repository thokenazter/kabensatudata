<?php
// Test script to check if Python is working correctly

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Python execution...\n\n";

try {
    // Create a simple test Python script
    file_put_contents('test_script.py', '#!/usr/bin/env python3
import sys
import json
print(json.dumps({"status": "success", "python_version": sys.version}))
');
    
    // Make it executable
    chmod('test_script.py', 0755);
    
    // Execute the Python script
    $process = new Process(['python3', 'test_script.py']);
    $process->setTimeout(10);
    $process->run();
    
    // Check if process was successful
    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }
    
    // Get output
    $output = $process->getOutput();
    echo "Python script output:\n";
    echo $output . "\n\n";
    
    // Parse JSON output
    $result = json_decode($output, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Error parsing JSON output: " . json_last_error_msg() . "\n";
    } else {
        echo "Status: " . $result['status'] . "\n";
        echo "Python version: " . $result['python_version'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Clean up
if (file_exists('test_script.py')) {
    unlink('test_script.py');
}

echo "\nTest completed.\n";