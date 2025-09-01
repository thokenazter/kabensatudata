#!/usr/bin/env python3
import sys
import os
from crawl_website import crawl_website

# Test crawling a simple website
try:
    print("Testing website crawler...")
    result = crawl_website("https://example.com", max_pages=1, append=False)
    print(f"Result: {result}")
    
    # Check if website_knowledge.txt was created and has content
    if os.path.exists("website_knowledge.txt"):
        with open("website_knowledge.txt", "r", encoding="utf-8") as f:
            content = f.read()
            print(f"website_knowledge.txt exists with {len(content)} characters")
    else:
        print("website_knowledge.txt was not created")
        
except Exception as e:
    print(f"Error: {str(e)}")
    sys.exit(1)

print("Test completed successfully")