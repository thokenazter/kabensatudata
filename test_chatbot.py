#!/usr/bin/env python3
import sys
import os
from chatbot import get_chatbot_response

# Test the chatbot
try:
    print("Testing chatbot...")
    response = get_chatbot_response("What is this website about?")
    print(f"Response: {response}")
        
except Exception as e:
    print(f"Error: {str(e)}")
    sys.exit(1)

print("Test completed successfully")