@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 mt-16">
    <h1 class="text-3xl font-bold mb-8">Chatbot Administration</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Website Crawler Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Website Crawler</h2>
            <p class="text-gray-600 mb-4">Crawl website content to build the chatbot's knowledge base.</p>
            
            <form id="crawl-form" class="space-y-4">
                <div>
                    <label for="url" class="block text-sm font-medium text-gray-700 mb-1">Website URL</label>
                    <input type="url" id="url" name="url" required
                        class="w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
                        placeholder="https://example.com">
                </div>
                
                <div>
                    <label for="max_pages" class="block text-sm font-medium text-gray-700 mb-1">Maximum Pages</label>
                    <input type="number" id="max_pages" name="max_pages" min="1" max="100" value="50"
                        class="w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Maximum number of pages to crawl (1-100)</p>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="append_website" name="append_website" 
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="append_website" class="ml-2 block text-sm text-gray-700">
                        Append to existing knowledge base
                    </label>
                </div>
                
                <div>
                    <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Start Crawling
                    </button>
                </div>
            </form>
            
            <div id="crawl-result" class="mt-4 hidden">
                <div class="bg-gray-100 rounded-md p-4">
                    <h3 class="font-medium text-gray-800 mb-2">Crawl Results</h3>
                    <p id="crawl-status" class="text-sm"></p>
                </div>
            </div>
        </div>
        
        <!-- PDF Upload Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">PDF Knowledge Base</h2>
            <p class="text-gray-600 mb-4">Upload PDF documents to enhance the chatbot's knowledge.</p>
            
            <form id="pdf-form" class="space-y-4" enctype="multipart/form-data">
                <div>
                    <label for="pdf_file" class="block text-sm font-medium text-gray-700 mb-1">PDF File</label>
                    <input type="file" id="pdf_file" name="pdf_file" required accept=".pdf"
                        class="w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="append" name="append" checked
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="append" class="ml-2 block text-sm text-gray-700">
                        Append to existing knowledge base
                    </label>
                </div>
                
                <div>
                    <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Upload PDF
                    </button>
                </div>
            </form>
            
            <div id="pdf-result" class="mt-4 hidden">
                <div class="bg-gray-100 rounded-md p-4">
                    <h3 class="font-medium text-gray-800 mb-2">Upload Results</h3>
                    <p id="pdf-status" class="text-sm"></p>
                </div>
            </div>
        </div>

        <!-- Application Knowledge Sync Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Pengetahuan Aplikasi</h2>
            <p class="text-gray-600 mb-4">Bangun pengetahuan chatbot langsung dari data yang ditampilkan di halaman aplikasi.</p>

            <div class="space-y-4">
                <button id="sync-knowledge-button"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    Sinkronkan Pengetahuan Aplikasi
                </button>
            </div>

            <div id="sync-knowledge-result" class="mt-4 hidden">
                <div class="bg-gray-100 rounded-md p-4">
                    <h3 class="font-medium text-gray-800 mb-2">Status Sinkronisasi</h3>
                    <p id="sync-knowledge-status" class="text-sm"></p>
                </div>
            </div>

            <p class="text-xs text-gray-500 mt-4">Tips: jalankan sinkronisasi setelah data dashboard, rekam medis, atau stok obat diperbarui agar jawaban chatbot tetap akurat.</p>
        </div>
    </div>
    
    <!-- Test Chatbot Section -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Test Chatbot</h2>
        <p class="text-gray-600 mb-4">Test the chatbot with sample questions.</p>
        
        <div class="flex space-x-4">
            <div class="flex-1">
                <form id="test-form" class="space-y-4">
                    <div>
                        <label for="test_message" class="block text-sm font-medium text-gray-700 mb-1">Test Message</label>
                        <input type="text" id="test_message" name="test_message" required
                            class="w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ask a question...">
                    </div>
                    
                    <div>
                        <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            Send Test Message
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="flex-1">
                <div class="border rounded-md p-4 h-full">
                    <h3 class="font-medium text-gray-800 mb-2">Response</h3>
                    <div id="test-result" class="text-sm text-gray-600">
                        <p class="italic">Response will appear here...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const crawlForm = document.getElementById('crawl-form');
    const crawlResult = document.getElementById('crawl-result');
    const crawlStatus = document.getElementById('crawl-status');
    
    const pdfForm = document.getElementById('pdf-form');
    const pdfResult = document.getElementById('pdf-result');
    const pdfStatus = document.getElementById('pdf-status');

    const testForm = document.getElementById('test-form');
    const testResult = document.getElementById('test-result');

    const syncKnowledgeButton = document.getElementById('sync-knowledge-button');
    const syncKnowledgeResult = document.getElementById('sync-knowledge-result');
    const syncKnowledgeStatus = document.getElementById('sync-knowledge-status');
    
    // Website Crawler Form
    crawlForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const url = document.getElementById('url').value;
        const maxPages = document.getElementById('max_pages').value;
        const append = document.getElementById('append_website').checked;
        
        // Show loading state
        crawlResult.classList.remove('hidden');
        crawlStatus.innerHTML = '<span class="text-blue-600">Crawling website... This may take a few minutes.</span>';
        
        // Send API request
        fetch('/api/crawl-website', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ url, max_pages: maxPages, append: append })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                crawlStatus.innerHTML = `
                    <span class="text-green-600">Success!</span>
                    <p class="mt-1">Crawled ${data.result.pages_crawled} pages.</p>
                    <p>Extracted text from ${data.result.text_extracted} pages.</p>
                `;
            } else {
                crawlStatus.innerHTML = `<span class="text-red-600">Error: ${data.message}</span>`;
                if (data.raw_output) {
                    crawlStatus.innerHTML += `<p class="mt-2 text-xs text-gray-500">Raw output: ${data.raw_output}</p>`;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            crawlStatus.innerHTML = '<span class="text-red-600">An error occurred. Please try again.</span>';
        });
    });
    
    // PDF Upload Form
    pdfForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const pdfFile = document.getElementById('pdf_file').files[0];
        const append = document.getElementById('append').checked;
        
        if (!pdfFile) {
            alert('Please select a PDF file.');
            return;
        }
        
        // Create form data
        const formData = new FormData();
        formData.append('pdf_file', pdfFile);
        formData.append('append', append);
        
        // Show loading state
        pdfResult.classList.remove('hidden');
        pdfStatus.innerHTML = '<span class="text-blue-600">Uploading and processing PDF...</span>';
        
        // Send API request
        fetch('/api/upload-pdf', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                pdfStatus.innerHTML = `
                    <span class="text-green-600">Success!</span>
                    <p class="mt-1">Processed file: ${data.result.filename}</p>
                    <p>Extracted ${data.result.characters_extracted} characters.</p>
                `;
            } else {
                pdfStatus.innerHTML = `<span class="text-red-600">Error: ${data.message}</span>`;
                if (data.raw_output) {
                    pdfStatus.innerHTML += `<p class="mt-2 text-xs text-gray-500">Raw output: ${data.raw_output}</p>`;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            pdfStatus.innerHTML = '<span class="text-red-600">An error occurred. Please try again.</span>';
        });
    });
    
    // Test Chatbot Form
    testForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const message = document.getElementById('test_message').value;
        
        // Show loading state
        testResult.innerHTML = '<p class="italic">Loading response...</p>';
        
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
                testResult.innerHTML = `<p>${data.response}</p>`;
            } else {
                testResult.innerHTML = `<p class="text-red-600">Error: ${data.message}</p>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            testResult.innerHTML = '<p class="text-red-600">An error occurred. Please try again.</p>';
        });
    });

    if (syncKnowledgeButton) {
        syncKnowledgeButton.addEventListener('click', function () {
            syncKnowledgeButton.disabled = true;
            syncKnowledgeButton.classList.add('opacity-60', 'cursor-not-allowed');
            syncKnowledgeResult.classList.remove('hidden');
            syncKnowledgeStatus.innerHTML = '<span class="text-blue-600">Sedang membangun ringkasan pengetahuan aplikasi...</span>';

            fetch('{{ route('admin.chatbot.sync-app-knowledge') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    syncKnowledgeStatus.innerHTML = `<span class="text-green-600">${data.message}</span>`;
                    if (data.artisan_output) {
                        syncKnowledgeStatus.innerHTML += `<pre class="mt-2 text-xs whitespace-pre-wrap text-gray-600">${data.artisan_output}</pre>`;
                    }
                } else {
                    syncKnowledgeStatus.innerHTML = `<span class="text-red-600">${data.message || 'Sinkronisasi gagal.'}</span>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                syncKnowledgeStatus.innerHTML = '<span class="text-red-600">Terjadi kesalahan saat menyinkronkan pengetahuan aplikasi.</span>';
            })
            .finally(() => {
                syncKnowledgeButton.disabled = false;
                syncKnowledgeButton.classList.remove('opacity-60', 'cursor-not-allowed');
            });
        });
    }
});
</script>
@endpush
@endsection
