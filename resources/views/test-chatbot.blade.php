<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chatbot Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Chatbot Test Page</h1>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Test Crawl Website</h2>
            <form id="crawl-form" class="space-y-4">
                <div>
                    <label for="url" class="block text-sm font-medium text-gray-700 mb-1">Website URL</label>
                    <input type="url" id="url" name="url" required
                        class="w-full px-4 py-2 border rounded-md"
                        value="https://example.com">
                </div>
                
                <div>
                    <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                        Start Crawling
                    </button>
                </div>
            </form>
            
            <div id="crawl-result" class="mt-4 hidden">
                <div class="bg-gray-100 rounded-md p-4">
                    <h3 class="font-medium text-gray-800 mb-2">Crawl Results</h3>
                    <pre id="crawl-status" class="text-sm whitespace-pre-wrap"></pre>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Test Chatbot</h2>
            <form id="chat-form" class="space-y-4">
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <input type="text" id="message" name="message" required
                        class="w-full px-4 py-2 border rounded-md"
                        value="What is this website about?">
                </div>
                
                <div>
                    <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md">
                        Send Message
                    </button>
                </div>
            </form>
            
            <div id="chat-result" class="mt-4 hidden">
                <div class="bg-gray-100 rounded-md p-4">
                    <h3 class="font-medium text-gray-800 mb-2">Response</h3>
                    <pre id="chat-status" class="text-sm whitespace-pre-wrap"></pre>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Knowledge Base Content</h2>
            
            <div class="space-y-4">
                <div>
                    <h3 class="font-medium text-gray-800 mb-2">Website Knowledge</h3>
                    <pre id="website-knowledge" class="text-sm bg-gray-100 p-4 rounded-md whitespace-pre-wrap max-h-60 overflow-y-auto">Loading...</pre>
                </div>
                
                <div>
                    <h3 class="font-medium text-gray-800 mb-2">PDF Knowledge</h3>
                    <pre id="pdf-knowledge" class="text-sm bg-gray-100 p-4 rounded-md whitespace-pre-wrap max-h-60 overflow-y-auto">Loading...</pre>
                </div>
                
                <button id="refresh-knowledge" 
                    class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md">
                    Refresh Knowledge Base
                </button>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const crawlForm = document.getElementById('crawl-form');
            const crawlResult = document.getElementById('crawl-result');
            const crawlStatus = document.getElementById('crawl-status');
            
            const chatForm = document.getElementById('chat-form');
            const chatResult = document.getElementById('chat-result');
            const chatStatus = document.getElementById('chat-status');
            
            const websiteKnowledge = document.getElementById('website-knowledge');
            const pdfKnowledge = document.getElementById('pdf-knowledge');
            const refreshKnowledge = document.getElementById('refresh-knowledge');
            
            // Load knowledge base content
            function loadKnowledgeBase() {
                fetch('/website_knowledge.txt')
                    .then(response => response.text())
                    .then(data => {
                        websiteKnowledge.textContent = data || 'No content';
                    })
                    .catch(error => {
                        websiteKnowledge.textContent = 'Error loading website knowledge';
                    });
                    
                fetch('/knowledge.txt')
                    .then(response => response.text())
                    .then(data => {
                        pdfKnowledge.textContent = data || 'No content';
                    })
                    .catch(error => {
                        pdfKnowledge.textContent = 'Error loading PDF knowledge';
                    });
            }
            
            // Load knowledge base on page load
            loadKnowledgeBase();
            
            // Refresh knowledge base
            refreshKnowledge.addEventListener('click', loadKnowledgeBase);
            
            // Website Crawler Form
            crawlForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const url = document.getElementById('url').value;
                
                // Show loading state
                crawlResult.classList.remove('hidden');
                crawlStatus.textContent = 'Crawling website... This may take a few minutes.';
                
                // Send API request
                fetch('/api/crawl-website', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ 
                        url: url, 
                        max_pages: 1
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        crawlStatus.textContent = `Success!\nCrawled ${data.result.pages_crawled} pages.\nExtracted text from ${data.result.text_extracted} pages.`;
                        // Reload knowledge base
                        loadKnowledgeBase();
                    } else {
                        crawlStatus.textContent = `Error: ${data.message}\n\n${data.raw_output || ''}`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    crawlStatus.textContent = 'An error occurred. Please try again.';
                });
            });
            
            // Chat Form
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const message = document.getElementById('message').value;
                
                // Show loading state
                chatResult.classList.remove('hidden');
                chatStatus.textContent = 'Processing message...';
                
                // Send API request
                fetch('/api/ask-chatbot', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ message })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        chatStatus.textContent = data.response;
                    } else {
                        chatStatus.textContent = `Error: ${data.message}`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    chatStatus.textContent = 'An error occurred. Please try again.';
                });
            });
        });
    </script>
</body>
</html>