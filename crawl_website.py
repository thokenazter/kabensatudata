#!/usr/bin/env python3
import sys
import requests
from bs4 import BeautifulSoup
import re
import json
import time
import os
from urllib.parse import urljoin, urlparse
from requests.exceptions import RequestException
from urllib.robotparser import RobotFileParser

def is_valid_url(url, base_domain):
    """Check if URL is valid and belongs to the same domain"""
    try:
        parsed_url = urlparse(url)
        parsed_base = urlparse(base_domain)
        
        # Check if it's the same domain
        same_domain = parsed_url.netloc == parsed_base.netloc
        
        # Check if it's a proper URL (not a javascript: or mailto: link)
        proper_scheme = parsed_url.scheme in ['http', 'https']
        
        # Check if it's not an anchor link to the same page
        not_anchor = bool(parsed_url.path) and not (parsed_url.path == parsed_base.path and parsed_url.fragment)
        
        return bool(parsed_url.netloc) and same_domain and proper_scheme and not_anchor
    except Exception:
        return False

def get_robots_parser(base_url):
    """Get robots.txt parser for the website"""
    try:
        parsed_url = urlparse(base_url)
        robots_url = f"{parsed_url.scheme}://{parsed_url.netloc}/robots.txt"
        
        rp = RobotFileParser()
        rp.set_url(robots_url)
        rp.read()
        return rp
    except Exception:
        # If robots.txt doesn't exist or can't be parsed, return None
        return None

def extract_text_from_html(html_content):
    """Extract meaningful text from HTML content"""
    soup = BeautifulSoup(html_content, 'html.parser')
    
    # Remove unwanted elements
    for element in soup(["script", "style", "nav", "footer", "header", "aside", "iframe", "noscript"]):
        element.extract()
    
    # Extract text from specific content areas if they exist
    content_areas = soup.select('main, article, #content, .content, #main, .main, .post, .entry, .page')
    
    if content_areas:
        # If content areas are found, extract text from them
        text = ""
        for area in content_areas:
            area_text = area.get_text(separator=' ', strip=True)
            text += area_text + " "
    else:
        # Otherwise, extract text from the entire body
        text = soup.get_text(separator=' ', strip=True)
    
    # Clean text (remove extra whitespace)
    text = re.sub(r'\s+', ' ', text).strip()
    
    # Remove very short lines (likely navigation or UI elements)
    lines = [line for line in text.split('\n') if len(line.strip()) > 30]
    text = ' '.join(lines)
    
    return text

def crawl_website(start_url, max_pages=50, append=False):
    """Crawl website and extract text content"""
    # Normalize the start URL
    if not start_url.startswith(('http://', 'https://')):
        start_url = 'https://' + start_url
    
    visited_urls = set()
    urls_to_visit = [start_url]
    extracted_text = []
    base_domain = urlparse(start_url).netloc
    
    # Check if robots.txt exists and can be parsed
    robots_parser = get_robots_parser(start_url)
    
    # User agent for requests
    headers = {
        'User-Agent': 'Mozilla/5.0 (compatible; HealthAssistantBot/1.0; +http://example.com/bot)'
    }
    
    print(f"Starting crawl of {start_url} with max pages: {max_pages}", file=sys.stderr)
    
    while urls_to_visit and len(visited_urls) < max_pages:
        # Get the next URL to visit
        current_url = urls_to_visit.pop(0)
        
        # Skip if already visited
        if current_url in visited_urls:
            continue
        
        # Check robots.txt
        if robots_parser and not robots_parser.can_fetch(headers['User-Agent'], current_url):
            print(f"Skipping {current_url} (disallowed by robots.txt)", file=sys.stderr)
            continue
        
        # Mark as visited
        visited_urls.add(current_url)
        
        try:
            # Fetch the page with a timeout and proper headers
            response = requests.get(
                current_url, 
                timeout=15,
                headers=headers
            )
            
            # Check if successful
            if response.status_code != 200:
                print(f"Skipping {current_url} (status code: {response.status_code})", file=sys.stderr)
                continue
            
            # Check content type
            content_type = response.headers.get('Content-Type', '')
            if 'text/html' not in content_type.lower():
                print(f"Skipping {current_url} (not HTML: {content_type})", file=sys.stderr)
                continue
                
            # Extract text
            text = extract_text_from_html(response.text)
            
            # Add to extracted text if not empty and has sufficient content
            if text and len(text) > 100:  # Minimum content length
                print(f"Extracted {len(text)} characters from {current_url}", file=sys.stderr)
                extracted_text.append({
                    "url": current_url,
                    "content": text,
                    "title": BeautifulSoup(response.text, 'html.parser').title.string if BeautifulSoup(response.text, 'html.parser').title else "No Title"
                })
            else:
                print(f"Skipping {current_url} (insufficient content)", file=sys.stderr)
            
            # Find all links on the page
            soup = BeautifulSoup(response.text, 'html.parser')
            for link in soup.find_all('a', href=True):
                href = link['href']
                full_url = urljoin(current_url, href)
                
                # Only add URLs from the same domain that haven't been visited
                if is_valid_url(full_url, start_url) and full_url not in visited_urls and full_url not in urls_to_visit:
                    urls_to_visit.append(full_url)
            
            # Be nice to the server
            time.sleep(0.5)
                    
        except RequestException as e:
            print(f"Request error crawling {current_url}: {e}", file=sys.stderr)
            continue
        except Exception as e:
            print(f"Error crawling {current_url}: {e}", file=sys.stderr)
            continue
    
    # Save extracted text to file
    mode = "a" if append and os.path.exists("website_knowledge.txt") else "w"
    with open("website_knowledge.txt", mode, encoding="utf-8") as f:
        if append:
            f.write("\n\n" + "=" * 100 + "\n")
            f.write(f"NEW CRAWL: {start_url} - {time.strftime('%Y-%m-%d %H:%M:%S')}\n")
            f.write("=" * 100 + "\n\n")
            
        for item in extracted_text:
            f.write(f"URL: {item['url']}\n")
            f.write(f"TITLE: {item['title']}\n\n")
            f.write(f"{item['content']}\n\n")
            f.write("-" * 80 + "\n\n")
    
    return {
        "pages_crawled": len(visited_urls),
        "text_extracted": len(extracted_text),
        "base_url": start_url
    }

def sync_knowledge_files():
    """Sync knowledge files to public directory"""
    try:
        # Copy website_knowledge.txt to public/
        if os.path.exists("website_knowledge.txt"):
            with open("website_knowledge.txt", "r", encoding="utf-8") as src:
                content = src.read()
                with open("public/website_knowledge.txt", "w", encoding="utf-8") as dst:
                    dst.write(content)
    except Exception as e:
        print(f"Error syncing knowledge files: {e}", file=sys.stderr)

if __name__ == "__main__":
    try:
        if len(sys.argv) > 1:
            start_url = sys.argv[1]
            max_pages = int(sys.argv[2]) if len(sys.argv) > 2 else 50
            append = sys.argv[3].lower() == 'true' if len(sys.argv) > 3 else False
            
            result = crawl_website(start_url, max_pages, append)
            # Sync knowledge files
            sync_knowledge_files()
            print(json.dumps(result))
        else:
            print(json.dumps({"error": "No start URL provided"}))
    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)