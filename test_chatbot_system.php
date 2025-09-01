<?php
// Test script to check if the chatbot system is working correctly

// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;

echo "Testing Chatbot System Components\n";
echo "================================\n\n";

// Test 1: Check if Python is installed
echo "Test 1: Checking Python installation...\n";
$process = new Process(['python3', '--version']);
$process->run();

if (!$process->isSuccessful()) {
    echo "❌ Python is not installed or not in PATH\n";
    echo "Error: " . $process->getErrorOutput() . "\n";
} else {
    echo "✅ Python is installed: " . trim($process->getOutput()) . "\n";
}
echo "\n";

// Test 2: Check if required Python packages are installed
echo "Test 2: Checking required Python packages...\n";
$requiredPackages = ['groq', 'python-dotenv', 'beautifulsoup4', 'requests', 'PyPDF2'];

foreach ($requiredPackages as $package) {
    $process = new Process(['pip', 'show', $package]);
    $process->run();
    
    if (!$process->isSuccessful()) {
        echo "❌ Package '$package' is not installed\n";
    } else {
        $output = $process->getOutput();
        preg_match('/Version: (.+)/', $output, $matches);
        $version = $matches[1] ?? 'unknown';
        echo "✅ Package '$package' is installed (version: $version)\n";
    }
}
echo "\n";

// Test 3: Check if GROQ_API_KEY is set
echo "Test 3: Checking GROQ_API_KEY environment variable...\n";
$groqApiKey = env('GROQ_API_KEY');
if (!$groqApiKey) {
    echo "❌ GROQ_API_KEY is not set in .env file\n";
} else {
    $maskedKey = substr($groqApiKey, 0, 4) . str_repeat('*', strlen($groqApiKey) - 8) . substr($groqApiKey, -4);
    echo "✅ GROQ_API_KEY is set: $maskedKey\n";
}
echo "\n";

// Test 4: Check if knowledge base files exist and are writable
echo "Test 4: Checking knowledge base files...\n";
$knowledgeFiles = ['website_knowledge.txt', 'knowledge.txt'];

foreach ($knowledgeFiles as $file) {
    if (!file_exists($file)) {
        echo "❌ File '$file' does not exist\n";
        
        // Try to create the file
        try {
            file_put_contents($file, '');
            echo "   Created empty file '$file'\n";
        } catch (Exception $e) {
            echo "   Failed to create file: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✅ File '$file' exists\n";
    }
    
    if (file_exists($file) && !is_writable($file)) {
        echo "❌ File '$file' is not writable\n";
    } else if (file_exists($file)) {
        echo "✅ File '$file' is writable\n";
    }
}
echo "\n";

// Test 5: Test chatbot.py with a simple query
echo "Test 5: Testing chatbot.py with a simple query...\n";
$process = new Process(['python3', 'chatbot.py', 'Hello, how are you?']);
$process->setTimeout(30);
$process->run();

if (!$process->isSuccessful()) {
    echo "❌ chatbot.py test failed\n";
    echo "Error: " . $process->getErrorOutput() . "\n";
} else {
    $output = $process->getOutput();
    $result = json_decode($output, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ chatbot.py returned invalid JSON\n";
        echo "Output: " . $output . "\n";
    } else {
        if (isset($result['error'])) {
            echo "❌ chatbot.py returned error: " . $result['error'] . "\n";
        } else {
            echo "✅ chatbot.py test successful\n";
            echo "Response: " . substr($result['response'], 0, 100) . "...\n";
        }
    }
}
echo "\n";

echo "Test completed. If any tests failed, please check the error messages and fix the issues.\n";