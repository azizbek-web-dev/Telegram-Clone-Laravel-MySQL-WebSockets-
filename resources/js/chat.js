// Chat Interface JavaScript
class ChatAPI {
    constructor() {
        this.baseURL = '/api';
        this.token = localStorage.getItem('auth_token');
        this.currentChatId = null;
        this.user = JSON.parse(localStorage.getItem('user_data') || '{}');
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${this.token}`,
                ...options.headers
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'An error occurred');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    async getChats() {
        const data = await this.request('/chats');
        return data.data.chats;
    }

    async getChat(chatId) {
        const data = await this.request(`/chats/${chatId}`);
        return data.data.chat;
    }

    async createChat(chatData) {
        const data = await this.request('/chats', {
            method: 'POST',
            body: JSON.stringify(chatData)
        });
        return data.data.chat;
    }

    async getMessages(chatId, page = 1) {
        const data = await this.request(`/messages/chat/${chatId}?page=${page}`);
        return data.data.messages;
    }

    async sendMessage(messageData) {
        const data = await this.request('/messages', {
            method: 'POST',
            body: JSON.stringify(messageData)
        });
        return data.data.message;
    }

    async searchUsers(query) {
        const data = await this.request(`/users/search?query=${encodeURIComponent(query)}`);
        return data.data.users;
    }

    async updateProfile(profileData) {
        const data = await this.request('/users/profile', {
            method: 'PUT',
            body: JSON.stringify(profileData)
        });
        return data.data.user;
    }
}

// Initialize API
const chatAPI = new ChatAPI();

// Chat Interface
class ChatInterface {
    constructor() {
        this.currentChatId = null;
        this.chats = [];
        this.messages = [];
        this.selectedUsers = [];
        this.init();
    }

    init() {
        this.loadUserData();
        this.loadChats();
        this.bindEvents();
        this.setupWebSocket();
    }

    loadUserData() {
        const user = JSON.parse(localStorage.getItem('user_data') || '{}');
        if (user.id) {
            document.getElementById('userName').textContent = user.full_name || user.username;
            if (user.profile_image_url) {
                document.getElementById('userAvatarImg').src = user.profile_image_url;
            }
        }
    }

    async loadChats() {
        try {
            this.chats = await chatAPI.getChats();
            this.renderChats();
        } catch (error) {
            console.error('Error loading chats:', error);
        }
    }

    renderChats() {
        const chatsList = document.getElementById('chatsList');
        chatsList.innerHTML = '';

        if (this.chats.length === 0) {
            chatsList.innerHTML = '<div class="no-chats">No chats yet. Start a new conversation!</div>';
            return;
        }

        this.chats.forEach(chat => {
            const chatElement = this.createChatElement(chat);
            chatsList.appendChild(chatElement);
        });
    }

    createChatElement(chat) {
        const chatDiv = document.createElement('div');
        chatDiv.className = 'chat-item';
        chatDiv.dataset.chatId = chat.id;

        const lastMessage = chat.last_message;
        const messagePreview = lastMessage ? 
            (lastMessage.message_type === 'text' ? lastMessage.content : `📎 ${lastMessage.message_type}`) : 
            'No messages yet';

        chatDiv.innerHTML = `
            <div class="chat-avatar">
                <img src="${chat.image_url || '/images/default-avatar.png'}" alt="${chat.name || 'Chat'}">
            </div>
            <div class="chat-preview">
                <h4>${chat.name || 'Unknown Chat'}</h4>
                <p>${messagePreview}</p>
            </div>
            <div class="chat-time">
                ${lastMessage ? this.formatTime(lastMessage.created_at) : ''}
            </div>
        `;

        chatDiv.addEventListener('click', () => this.selectChat(chat.id));
        return chatDiv;
    }

    async selectChat(chatId) {
        this.currentChatId = chatId;
        
        // Update UI
        document.querySelectorAll('.chat-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector(`[data-chat-id="${chatId}"]`).classList.add('active');

        // Show chat interface
        document.getElementById('chatHeader').style.display = 'flex';
        document.getElementById('chatInputContainer').style.display = 'block';
        document.querySelector('.welcome-message').style.display = 'none';

        // Load chat details and messages
        await this.loadChatDetails(chatId);
        await this.loadMessages(chatId);
    }

    async loadChatDetails(chatId) {
        try {
            const chat = await chatAPI.getChat(chatId);
            
            document.getElementById('chatName').textContent = chat.name || 'Chat';
            document.getElementById('chatStatus').textContent = `${chat.members_count || 0} members`;
            
            if (chat.image_url) {
                document.getElementById('chatAvatarImg').src = chat.image_url;
            }
        } catch (error) {
            console.error('Error loading chat details:', error);
        }
    }

    async loadMessages(chatId) {
        try {
            const messagesData = await chatAPI.getMessages(chatId);
            this.messages = messagesData.data;
            this.renderMessages();
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    renderMessages() {
        const messagesContainer = document.getElementById('chatMessages');
        messagesContainer.innerHTML = '';

        this.messages.forEach(message => {
            const messageElement = this.createMessageElement(message);
            messagesContainer.appendChild(messageElement);
        });

        this.scrollToBottom();
    }

    createMessageElement(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${message.sender_id === this.user.id ? 'own' : ''}`;

        const messageContent = message.message_type === 'text' ? 
            message.content : 
            `📎 ${message.file_name || message.message_type}`;

        messageDiv.innerHTML = `
            <div class="message-avatar">
                <img src="${message.sender.profile_image_url || '/images/default-avatar.png'}" alt="${message.sender.username}">
            </div>
            <div class="message-content">
                <p class="message-text">${messageContent}</p>
                <div class="message-time">${this.formatTime(message.created_at)}</div>
            </div>
        `;

        return messageDiv;
    }

    async sendMessage() {
        const messageInput = document.getElementById('messageInput');
        const content = messageInput.value.trim();

        if (!content || !this.currentChatId) return;

        try {
            const messageData = {
                chat_id: this.currentChatId,
                message_type: 'text',
                content: content
            };

            const message = await chatAPI.sendMessage(messageData);
            this.messages.push(message);
            this.renderMessages();
            messageInput.value = '';
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }

    bindEvents() {
        // Send message
        document.getElementById('sendBtn').addEventListener('click', () => this.sendMessage());
        
        document.getElementById('messageInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });

        // New chat modal
        document.getElementById('newChatBtn').addEventListener('click', () => {
            document.getElementById('newChatModal').classList.add('show');
        });

        document.getElementById('closeNewChatModal').addEventListener('click', () => {
            document.getElementById('newChatModal').classList.remove('show');
        });

        document.getElementById('cancelNewChat').addEventListener('click', () => {
            document.getElementById('newChatModal').classList.remove('show');
        });

        // Chat type change
        document.getElementById('chatType').addEventListener('change', (e) => {
            const chatNameGroup = document.getElementById('chatNameGroup');
            const chatDescriptionGroup = document.getElementById('chatDescriptionGroup');
            
            if (e.target.value === 'private') {
                chatNameGroup.style.display = 'none';
                chatDescriptionGroup.style.display = 'none';
            } else {
                chatNameGroup.style.display = 'block';
                chatDescriptionGroup.style.display = 'block';
            }
        });

        // User search
        document.getElementById('userSearch').addEventListener('input', async (e) => {
            const query = e.target.value.trim();
            if (query.length < 2) {
                document.getElementById('usersList').innerHTML = '';
                return;
            }

            try {
                const users = await chatAPI.searchUsers(query);
                this.renderUserSearch(users);
            } catch (error) {
                console.error('Error searching users:', error);
            }
        });

        // Create chat
        document.getElementById('createChat').addEventListener('click', () => this.createNewChat());

        // Logout
        document.getElementById('settingsBtn').addEventListener('click', () => {
            if (confirm('Are you sure you want to logout?')) {
                this.logout();
            }
        });
    }

    renderUserSearch(users) {
        const usersList = document.getElementById('usersList');
        usersList.innerHTML = '';

        users.forEach(user => {
            const userDiv = document.createElement('div');
            userDiv.className = 'user-item';
            userDiv.dataset.userId = user.id;
            userDiv.innerHTML = `
                <div class="user-avatar">
                    <img src="${user.profile_image_url || '/images/default-avatar.png'}" alt="${user.username}">
                </div>
                <div>
                    <h4>${user.full_name || user.username}</h4>
                    <p>@${user.username}</p>
                </div>
            `;

            userDiv.addEventListener('click', () => this.toggleUserSelection(user.id, userDiv));
            usersList.appendChild(userDiv);
        });
    }

    toggleUserSelection(userId, element) {
        if (this.selectedUsers.includes(userId)) {
            this.selectedUsers = this.selectedUsers.filter(id => id !== userId);
            element.classList.remove('selected');
        } else {
            this.selectedUsers.push(userId);
            element.classList.add('selected');
        }
    }

    async createNewChat() {
        const chatType = document.getElementById('chatType').value;
        const chatName = document.getElementById('chatName').value;
        const chatDescription = document.getElementById('chatDescription').value;

        if (chatType !== 'private' && !chatName.trim()) {
            alert('Please enter a chat name');
            return;
        }

        if (this.selectedUsers.length === 0) {
            alert('Please select at least one user');
            return;
        }

        try {
            const chatData = {
                type: chatType,
                name: chatName,
                description: chatDescription,
                user_ids: this.selectedUsers
            };

            const chat = await chatAPI.createChat(chatData);
            this.chats.unshift(chat);
            this.renderChats();
            this.selectChat(chat.id);
            
            document.getElementById('newChatModal').classList.remove('show');
            this.resetNewChatForm();
        } catch (error) {
            console.error('Error creating chat:', error);
            alert('Error creating chat: ' + error.message);
        }
    }

    resetNewChatForm() {
        document.getElementById('newChatModal').querySelector('form').reset();
        this.selectedUsers = [];
        document.getElementById('usersList').innerHTML = '';
        document.querySelectorAll('.user-item').forEach(item => {
            item.classList.remove('selected');
        });
    }

    setupWebSocket() {
        // WebSocket implementation would go here
        // For now, we'll use polling for new messages
        setInterval(() => {
            if (this.currentChatId) {
                this.loadMessages(this.currentChatId);
            }
        }, 5000);
    }

    scrollToBottom() {
        const messagesContainer = document.getElementById('chatMessages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;

        if (diff < 60000) { // Less than 1 minute
            return 'Just now';
        } else if (diff < 3600000) { // Less than 1 hour
            return Math.floor(diff / 60000) + 'm ago';
        } else if (diff < 86400000) { // Less than 1 day
            return Math.floor(diff / 3600000) + 'h ago';
        } else {
            return date.toLocaleDateString();
        }
    }

    logout() {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data');
        window.location.href = '/login';
    }
}

// Initialize chat interface when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname === '/chat') {
        new ChatInterface();
    }
});
