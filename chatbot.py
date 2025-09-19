#!/usr/bin/env python3
import sys
import json
import os
import re
from groq import Groq
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# Initialize Groq client
api_key = os.getenv("GROQ_API_KEY")
if not api_key:
    print(json.dumps({"error": "GROQ_API_KEY not found in environment variables"}))
    sys.exit(1)

try:
    client = Groq(api_key=api_key)
except Exception as e:
    print(json.dumps({"error": f"Failed to initialize Groq client: {str(e)}"}))
    sys.exit(1)

def clean_text(text):
    """Clean text to remove special characters and normalize whitespace"""
    # Remove any HTML tags that might remain
    text = re.sub(r'<[^>]+>', ' ', text)
    # Replace multiple whitespace with a single space
    text = re.sub(r'\s+', ' ', text)
    # Remove any non-printable characters
    text = ''.join(c for c in text if c.isprintable() or c in ['\n', '\t'])
    return text.strip()

def load_knowledge():
    """Load knowledge from text files"""
    knowledge = []
    
    # Load website knowledge if available
    if os.path.exists("website_knowledge.txt"):
        try:
            with open("website_knowledge.txt", "r", encoding="utf-8") as f:
                website_content = f.read()
                if website_content.strip():
                    website_content = clean_text(website_content)
                    knowledge.append("=== WEBSITE KNOWLEDGE ===\n" + website_content)
        except Exception as e:
            print(f"Error loading website knowledge: {e}", file=sys.stderr)

    if os.path.exists("app_knowledge.txt"):
        try:
            with open("app_knowledge.txt", "r", encoding="utf-8") as f:
                app_content = f.read()
                if app_content.strip():
                    app_content = clean_text(app_content)
                    knowledge.append("=== APPLICATION KNOWLEDGE ===\n" + app_content)
        except Exception as e:
            print(f"Error loading application knowledge: {e}", file=sys.stderr)

    # Load PDF knowledge if available
    if os.path.exists("knowledge.txt"):
        try:
            with open("knowledge.txt", "r", encoding="utf-8") as f:
                pdf_content = f.read()
                if pdf_content.strip():
                    pdf_content = clean_text(pdf_content)
                    knowledge.append("=== PDF KNOWLEDGE ===\n" + pdf_content)
        except Exception as e:
            print(f"Error loading PDF knowledge: {e}", file=sys.stderr)
    
    # Join all knowledge sources
    combined_knowledge = "\n\n".join(knowledge)
    
    # Limit knowledge size to avoid token limits
    max_chars = 32000  # Approximate character limit
    if len(combined_knowledge) > max_chars:
        print(f"Knowledge base too large ({len(combined_knowledge)} chars), truncating to {max_chars} chars", file=sys.stderr)
        combined_knowledge = combined_knowledge[:max_chars] + "...[truncated]"
    
    return combined_knowledge

def get_chatbot_response(user_message):
    """Get response from Groq API"""
    try:
        knowledge = load_knowledge()
        
        if not knowledge:
            return "I don't have any knowledge base loaded yet. Please add some information by crawling websites or uploading PDFs."
        
        # Create system prompt with knowledge base
        system_prompt = f"""You are a helpful assistant for a health information system. 
        Use the following knowledge base to answer user questions:
        
        {knowledge}
        
        If you don't know the answer based on the provided knowledge, say so politely.
        Always be helpful, clear, and concise in your responses.
        When providing information from the knowledge base, cite the source if available.
        """
        
        # Call Groq API
        chat_completion = client.chat.completions.create(
            messages=[
                {
                    "role": "system",
                    "content": system_prompt
                },
                {
                    "role": "user",
                    "content": user_message
                }
            ],
            model="meta-llama/llama-4-scout-17b-16e-instruct",
            temperature=0.5,
            max_tokens=1024,
        )
        
        return chat_completion.choices[0].message.content
    
    except Exception as e:
        print(f"Error getting chatbot response: {e}", file=sys.stderr)
        return f"I'm sorry, I encountered an error while processing your request. Error details: {str(e)}"

def sync_knowledge_files():
    """Sync knowledge files to public directory"""
    try:
        # Copy website_knowledge.txt to public/
        if os.path.exists("website_knowledge.txt"):
            with open("website_knowledge.txt", "r", encoding="utf-8") as src:
                content = src.read()
                with open("public/website_knowledge.txt", "w", encoding="utf-8") as dst:
                    dst.write(content)

        if os.path.exists("app_knowledge.txt"):
            with open("app_knowledge.txt", "r", encoding="utf-8") as src:
                content = src.read()
                with open("public/app_knowledge.txt", "w", encoding="utf-8") as dst:
                    dst.write(content)

        # Copy knowledge.txt to public/
        if os.path.exists("knowledge.txt"):
            with open("knowledge.txt", "r", encoding="utf-8") as src:
                content = src.read()
                with open("public/knowledge.txt", "w", encoding="utf-8") as dst:
                    dst.write(content)
    except Exception as e:
        print(f"Error syncing knowledge files: {e}", file=sys.stderr)

if __name__ == "__main__":
    try:
        # Get user message from command line argument
        if len(sys.argv) > 1:
            user_message = sys.argv[1]
            response = get_chatbot_response(user_message)
            # Sync knowledge files
            sync_knowledge_files()
            print(json.dumps({"response": response}))
        else:
            print(json.dumps({"error": "No message provided"}))
    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)
