<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChatbotController extends Controller
{
    /**
     * Process a chatbot query
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = $request->input('message');
        
        try {
            // Execute Python script with the user's message
            $process = new Process(['python3', base_path('chatbot.py'), $message]);
            $process->setTimeout(120); // Increase timeout to 2 minutes
            $process->run();

            // Check if process was successful
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Get the output from the Python script
            $output = $process->getOutput();
            $result = json_decode($output, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to decode JSON from Python script: ' . $output);
                return response()->json([
                    'success' => false,
                    'message' => 'Error processing your request',
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'response' => $result['response'] ?? 'Sorry, I could not process your request.',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Chatbot error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing your request: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Crawl website to build knowledge base
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function crawlWebsite(Request $request)
    {
        $request->validate([
            'url' => 'required|string',
            'max_pages' => 'nullable|integer|min:1|max:100',
            'append' => 'nullable|boolean',
        ]);

        $url = $request->input('url');
        $maxPages = $request->input('max_pages', 50);
        $append = $request->input('append', false);
        
        try {
            // Normalize URL if needed
            if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
                $url = 'https://' . $url;
            }
            
            // Execute Python script to crawl website
            $process = new Process([
                'python3', 
                base_path('crawl_website.py'), 
                $url, 
                (string)$maxPages,
                $append ? 'true' : 'false'
            ]);
            
            $process->setTimeout(600); // 10 minutes timeout for crawling
            $process->setIdleTimeout(60); // 1 minute idle timeout
            
            // Enable output streaming
            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    Log::info('Crawler output: ' . $buffer);
                }
            });

            // Check if process was successful
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Get the output from the Python script
            $output = $process->getOutput();
            $result = json_decode($output, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to decode JSON from crawl script: ' . $output);
                return response()->json([
                    'success' => false,
                    'message' => 'Error processing your request: Invalid JSON response',
                    'raw_output' => $output,
                ], 500);
            }
            
            // Check if there's an error in the result
            if (isset($result['error'])) {
                Log::error('Crawler returned error: ' . $result['error']);
                return response()->json([
                    'success' => false,
                    'message' => 'Error crawling website: ' . $result['error'],
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
            
        } catch (ProcessFailedException $e) {
            $output = $e->getProcess()->getOutput();
            $errorOutput = $e->getProcess()->getErrorOutput();
            Log::error('Website crawling error: ' . $e->getMessage());
            Log::error('Process output: ' . $output);
            Log::error('Process error output: ' . $errorOutput);
            
            return response()->json([
                'success' => false,
                'message' => 'Error crawling website: ' . $e->getMessage(),
                'raw_output' => $errorOutput ?: $output,
            ], 500);
        } catch (\Exception $e) {
            Log::error('Website crawling error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error crawling website: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Upload and process PDF for knowledge extraction
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function uploadPdf(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:10240', // Max 10MB
            'append' => 'nullable|boolean',
        ]);

        try {
            // Store the uploaded file
            $path = $request->file('pdf_file')->store('pdfs', 'local');
            $fullPath = storage_path('app/' . $path);
            
            $append = $request->input('append', true);
            $appendParam = $append ? 'true' : 'false';
            
            // Execute Python script to extract text from PDF
            $process = new Process(['python3', base_path('extract_knowledge.py'), $fullPath, $appendParam]);
            $process->setTimeout(180); // Increase timeout to 3 minutes
            $process->run();

            // Check if process was successful
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Get the output from the Python script
            $output = $process->getOutput();
            $result = json_decode($output, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to decode JSON from PDF extraction script: ' . $output);
                return response()->json([
                    'success' => false,
                    'message' => 'Error processing your PDF',
                ], 500);
            }
            
            // Clean up the temporary file
            Storage::disk('local')->delete($path);
            
            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
            
        } catch (ProcessFailedException $e) {
            $output = $e->getProcess()->getOutput();
            $errorOutput = $e->getProcess()->getErrorOutput();
            Log::error('PDF processing error: ' . $e->getMessage());
            Log::error('Process output: ' . $output);
            Log::error('Process error output: ' . $errorOutput);
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing PDF: ' . $e->getMessage(),
                'raw_output' => $errorOutput ?: $output,
            ], 500);
        } catch (\Exception $e) {
            Log::error('PDF processing error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function syncAppKnowledge(Request $request)
    {
        try {
            Artisan::call('chatbot:sync-app-knowledge');

            return response()->json([
                'success' => true,
                'message' => 'Pengetahuan aplikasi berhasil disinkronkan.',
                'artisan_output' => trim(Artisan::output()),
            ]);
        } catch (\Exception $e) {
            Log::error('Chatbot knowledge sync error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyinkronkan pengetahuan aplikasi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
