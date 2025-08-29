# Chatbot Installation Instructions

Follow these steps to set up the chatbot system in your Laravel application:

## Prerequisites

- PHP 8.1 or higher
- Laravel 10.x
- Python 3.8 or higher
- Composer
- npm

## Installation Steps

### 1. Install Python Dependencies

Create a virtual environment and install the required Python packages:

```bash
# Create a virtual environment
python -m venv venv

# Activate the virtual environment
# On Windows:
venv\Scripts\activate
# On macOS/Linux:
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt
```

### 2. Configure Groq API Key

1. Sign up for an account at [https://console.groq.com](https://console.groq.com)
2. Generate an API key
3. Add the API key to your `.env` file:

```
GROQ_API_KEY=your-groq-api-key-here
```

### 3. Set File Permissions

Make sure the knowledge base files are writable by the web server:

```bash
chmod 666 website_knowledge.txt knowledge.txt
```

### 4. Create Storage Directory for PDFs

```bash
mkdir -p storage/app/pdfs
chmod -R 775 storage/app/pdfs
```

### 5. Add Sound File

Place a message notification sound file at `public/sounds/message.mp3`. You can use any short MP3 file for this purpose.

### 6. Include the Chatbot in Your Layout

Add the CSRF token meta tag to your main layout file:

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

Include the chatbot component in your layout or specific pages:

```php
@include('components.floating-chatbot')
```

### 7. Access the Admin Interface

After logging in, navigate to `/admin/chatbot` to access the chatbot administration interface. From there, you can:

- Crawl website content to build the knowledge base
- Upload PDF documents to enhance the knowledge base
- Test the chatbot with sample questions

## Troubleshooting

### Python Script Execution Issues

If you encounter issues with executing Python scripts:

1. Make sure Python 3 is installed and available in your PATH
2. Verify that the virtual environment is activated
3. Check that all required packages are installed
4. Ensure file permissions are set correctly

### API Key Issues

If the chatbot is not responding correctly:

1. Verify that your Groq API key is correctly set in the `.env` file
2. Check that the API key is valid and has not expired
3. Ensure you have sufficient credits in your Groq account

### File Permission Issues

If knowledge base files cannot be written:

1. Check the permissions on `website_knowledge.txt` and `knowledge.txt`
2. Ensure the web server user has write access to these files
3. Verify that the storage directory for PDFs exists and is writable