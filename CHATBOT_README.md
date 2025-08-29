# Chatbot System Documentation

## Overview

This chatbot system is powered by the Groq API using the LLaMA 3.3 70B model. It provides a conversational interface for users to ask questions about health information. The chatbot can access knowledge from both crawled website content and uploaded PDF documents.

## Components

### Frontend
- Floating chatbot UI component (`resources/views/components/floating-chatbot.blade.php`)
- Can be included in any page with `@include('components.floating-chatbot')`

### Backend
- `ChatbotController.php` - Handles API requests for the chatbot
- API endpoints:
  - `/api/ask-chatbot` - Process user queries
  - `/api/crawl-website` - Crawl website content for knowledge base
  - `/api/upload-pdf` - Extract text from uploaded PDFs

### Python Scripts
- `chatbot.py` - Main script that processes user queries using Groq API
- `crawl_website.py` - Crawls website content to build knowledge base
- `extract_knowledge.py` - Extracts text from uploaded PDFs

## Setup Instructions

1. Install Python dependencies:
   ```
   pip install -r requirements.txt
   ```

2. Set up your Groq API key in the `.env` file:
   ```
   GROQ_API_KEY=your-groq-api-key-here
   ```

3. Include the chatbot component in your blade templates:
   ```php
   @include('components.floating-chatbot')
   ```

4. Make sure to include the CSRF token meta tag in your layout:
   ```html
   <meta name="csrf-token" content="{{ csrf_token() }}">
   ```

5. Build the knowledge base:
   - Use the admin interface to crawl websites
   - Upload PDF documents through the admin interface

## Knowledge Base

The chatbot uses two main sources of knowledge:
1. `website_knowledge.txt` - Contains text extracted from crawled websites
2. `knowledge.txt` - Contains text extracted from uploaded PDFs

## Usage

### Including the Chatbot in a Page
```php
@include('components.floating-chatbot')
```

### API Endpoints

#### Ask the Chatbot
```
POST /api/ask-chatbot
{
    "message": "What is hypertension?"
}
```

#### Crawl a Website
```
POST /api/crawl-website
{
    "url": "https://example.com/health-info",
    "max_pages": 50
}
```

#### Upload a PDF
```
POST /api/upload-pdf
Form data:
- pdf_file: [PDF file]
- append: true/false
```

## Customization

You can customize the appearance of the chatbot by modifying the `floating-chatbot.blade.php` file. The component uses Tailwind CSS for styling.

## Troubleshooting

- Make sure Python 3 is installed and available in your PATH
- Check that all required Python packages are installed
- Verify that the Groq API key is correctly set in the `.env` file
- Ensure the knowledge base files are writable by the web server