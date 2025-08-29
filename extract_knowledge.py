#!/usr/bin/env python3
import sys
import json
import os
import re
import PyPDF2
import tempfile
import time

def clean_text(text):
    """Clean text to remove special characters and normalize whitespace"""
    # Replace multiple whitespace with a single space
    text = re.sub(r'\s+', ' ', text)
    # Remove any non-printable characters
    text = ''.join(c for c in text if c.isprintable() or c in ['\n', '\t'])
    return text.strip()

def extract_text_from_pdf(pdf_path):
    """Extract text from a PDF file"""
    text = ""
    try:
        with open(pdf_path, 'rb') as file:
            pdf_reader = PyPDF2.PdfReader(file)
            
            # Extract PDF metadata if available
            metadata = {}
            if pdf_reader.metadata:
                for key in pdf_reader.metadata:
                    if key and pdf_reader.metadata[key]:
                        metadata[key] = str(pdf_reader.metadata[key])
            
            # Extract text from each page
            total_pages = len(pdf_reader.pages)
            print(f"Extracting text from PDF with {total_pages} pages", file=sys.stderr)
            
            for page_num in range(total_pages):
                try:
                    page = pdf_reader.pages[page_num]
                    page_text = page.extract_text()
                    if page_text:
                        text += f"--- Page {page_num + 1} ---\n{page_text}\n\n"
                except Exception as e:
                    print(f"Error extracting text from page {page_num + 1}: {str(e)}", file=sys.stderr)
            
            # Add metadata to the beginning if available
            if metadata:
                meta_text = "--- PDF Metadata ---\n"
                for key, value in metadata.items():
                    if key.startswith('/'):
                        key = key[1:]
                    meta_text += f"{key}: {value}\n"
                text = meta_text + "\n\n" + text
                
        return text
    except Exception as e:
        error_msg = f"Error extracting text: {str(e)}"
        print(error_msg, file=sys.stderr)
        return error_msg

def process_pdf(pdf_path, output_file="knowledge.txt", append=True):
    """Process PDF and save extracted text to file"""
    try:
        # Extract text from PDF
        extracted_text = extract_text_from_pdf(pdf_path)
        
        if not extracted_text or len(extracted_text.strip()) < 100:
            return {
                "filename": os.path.basename(pdf_path),
                "characters_extracted": 0,
                "error": "No meaningful text could be extracted from the PDF"
            }
        
        # Clean the extracted text
        cleaned_text = clean_text(extracted_text)
        
        # Get PDF filename for reference
        filename = os.path.basename(pdf_path)
        
        # Write or append to knowledge file
        mode = "a" if append and os.path.exists(output_file) else "w"
        with open(output_file, mode, encoding="utf-8") as f:
            if append and os.path.exists(output_file) and os.path.getsize(output_file) > 0:
                f.write("\n\n" + "=" * 80 + "\n\n")
            
            f.write(f"SOURCE: {filename}\n")
            f.write(f"ADDED: {time.strftime('%Y-%m-%d %H:%M:%S')}\n\n")
            f.write(cleaned_text)
            f.write("\n\n" + "-" * 80 + "\n\n")
        
        return {
            "filename": filename,
            "characters_extracted": len(cleaned_text),
            "pages": extracted_text.count("--- Page")
        }
    except Exception as e:
        error_msg = f"Error processing PDF: {str(e)}"
        print(error_msg, file=sys.stderr)
        return {
            "filename": os.path.basename(pdf_path),
            "error": error_msg
        }

def sync_knowledge_files():
    """Sync knowledge files to public directory"""
    try:
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
        if len(sys.argv) > 1:
            pdf_path = sys.argv[1]
            append = True
            if len(sys.argv) > 2 and sys.argv[2].lower() == "false":
                append = False
            
            result = process_pdf(pdf_path, append=append)
            # Sync knowledge files
            sync_knowledge_files()
            print(json.dumps(result))
        else:
            print(json.dumps({"error": "No PDF file path provided"}))
    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)