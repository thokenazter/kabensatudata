<!-- Floating Chatbot Component -->
<div id="floating-chatbot" class="fixed bottom-4 right-4 z-50">
    <!-- Chat Button -->
    <button id="chat-button" class="bg-blue-600 hover:bg-blue-700 text-white rounded-full p-4 shadow-lg flex items-center justify-center w-16 h-16 focus:outline-none transition-all duration-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
    </button>
    
    <!-- Chat Window -->
    <div id="chat-window" class="hidden bg-white rounded-lg shadow-xl w-80 sm:w-96 h-96 flex flex-col overflow-hidden transition-all duration-300">
        <!-- Chat Header -->
        <div class="bg-blue-600 text-white p-4 flex justify-between items-center">
            <h3 class="font-medium">Health Assistant</h3>
            <button id="close-chat" class="focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <!-- Chat Messages -->
        <div id="chat-messages" class="flex-1 p-4 overflow-y-auto">
            <div class="chat-message bot">
                <div class="bg-gray-100 rounded-lg p-3 max-w-[80%] inline-block">
                    <p>Hello! I'm your health assistant. How can I help you today?</p>
                </div>
            </div>
        </div>
        
        <!-- Typing Indicator -->
        <div id="typing-indicator" class="hidden px-4 py-2">
            <div class="bg-gray-100 rounded-lg p-3 max-w-[60%] inline-block">
                <div class="flex space-x-1">
                    <div class="bg-gray-400 rounded-full w-2 h-2 animate-bounce"></div>
                    <div class="bg-gray-400 rounded-full w-2 h-2 animate-bounce" style="animation-delay: 0.2s"></div>
                    <div class="bg-gray-400 rounded-full w-2 h-2 animate-bounce" style="animation-delay: 0.4s"></div>
                </div>
            </div>
        </div>
        
        <!-- Chat Input -->
        <div class="border-t p-4">
            <form id="chat-form" class="flex">
                <input 
                    id="chat-input" 
                    type="text" 
                    placeholder="Type your message..." 
                    class="flex-1 border rounded-l-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
                <button 
                    type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white rounded-r-lg px-4 py-2 focus:outline-none"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Audio for notification -->
<audio id="message-sound" preload="auto">
    <source src="{{ asset('sounds/message.mp3') }}" type="audio/mpeg">
</audio>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatButton = document.getElementById('chat-button');
    const chatWindow = document.getElementById('chat-window');
    const closeChat = document.getElementById('close-chat');
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const typingIndicator = document.getElementById('typing-indicator');
    const messageSound = document.getElementById('message-sound');
    
    // Toggle chat window
    chatButton.addEventListener('click', function() {
        chatWindow.classList.toggle('hidden');
        chatButton.classList.toggle('rotate-90');
        if (!chatWindow.classList.contains('hidden')) {
            chatInput.focus();
        }
    });
    
    // Close chat window
    closeChat.addEventListener('click', function() {
        chatWindow.classList.add('hidden');
        chatButton.classList.remove('rotate-90');
    });
    
    // Handle form submission
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add user message to chat
        addMessage(message, 'user');
        
        // Clear input
        chatInput.value = '';
        
        // Show typing indicator
        typingIndicator.classList.remove('hidden');
        
        // Send message to server
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
            // Hide typing indicator
            typingIndicator.classList.add('hidden');
            
            if (data.success) {
                // Add bot response
                addMessage(data.response, 'bot');
                // Play sound
                messageSound.play().catch(e => console.log('Audio play error:', e));
            } else {
                // Add error message
                addMessage('Sorry, I encountered an error. Please try again later.', 'bot error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            typingIndicator.classList.add('hidden');
            addMessage('Sorry, I encountered an error. Please try again later.', 'bot error');
        });
    });
    
    // Function to add a message to the chat
    function addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${sender} my-2`;
        
        const bubble = document.createElement('div');
        bubble.className = sender === 'user' 
            ? 'bg-blue-600 text-white rounded-lg p-3 max-w-[80%] ml-auto'
            : 'bg-gray-100 rounded-lg p-3 max-w-[80%] inline-block';
        
        const paragraph = document.createElement('p');
        paragraph.textContent = text;
        
        bubble.appendChild(paragraph);
        messageDiv.appendChild(bubble);
        chatMessages.appendChild(messageDiv);
        
        // Scroll to bottom
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});
</script>

<style>
.chat-message.user {
    text-align: right;
}

.chat-message.bot {
    text-align: left;
}

#chat-messages::-webkit-scrollbar {
    width: 6px;
}

#chat-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
}

#chat-messages::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

#chat-messages::-webkit-scrollbar-thumb:hover {
    background: #555;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}
</style>