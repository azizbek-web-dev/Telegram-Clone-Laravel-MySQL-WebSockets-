<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telegram Clone</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="chat-body">
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="user-info">
                    <div class="user-avatar" id="userAvatar">
                        <img src="" alt="User" id="userAvatarImg">
                    </div>
                    <div class="user-details">
                        <h3 id="userName">Loading...</h3>
                        <p id="userStatus">Online</p>
                    </div>
                </div>
                <div class="sidebar-actions">
                    <button class="btn-icon" id="newChatBtn" title="New Chat">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                    </button>
                    <button class="btn-icon" id="settingsBtn" title="Settings">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search chats...">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
            </div>
            
            <div class="chats-list" id="chatsList">
                <!-- Chats will be loaded here -->
            </div>
        </div>
        
        <!-- Main Chat Area -->
        <div class="main-chat">
            <div class="chat-header" id="chatHeader" style="display: none;">
                <div class="chat-info">
                    <div class="chat-avatar" id="chatAvatar">
                        <img src="" alt="Chat" id="chatAvatarImg">
                    </div>
                    <div class="chat-details">
                        <h3 id="chatName">Select a chat</h3>
                        <p id="chatStatus">Choose a conversation to start messaging</p>
                    </div>
                </div>
                <div class="chat-actions">
                    <button class="btn-icon" id="chatInfoBtn" title="Chat Info">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 16v-4"/>
                            <path d="M12 8h.01"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <div class="welcome-message">
                    <div class="welcome-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                    <h2>Welcome to Telegram Clone</h2>
                    <p>Select a chat from the sidebar to start messaging</p>
                </div>
            </div>
            
            <div class="chat-input-container" id="chatInputContainer" style="display: none;">
                <div class="chat-input">
                    <button class="btn-icon" id="attachBtn" title="Attach File">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66L9.64 16.2a2 2 0 0 1-2.83-2.83l8.49-8.49"/>
                        </svg>
                    </button>
                    <input type="text" id="messageInput" placeholder="Type a message...">
                    <button class="btn-icon" id="sendBtn" title="Send Message">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M22 2L11 13"/>
                            <path d="M22 2l-7 20-4-9-9-4 20-7z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- New Chat Modal -->
    <div class="modal" id="newChatModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>New Chat</h3>
                <button class="btn-icon" id="closeNewChatModal">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M18 6L6 18"/>
                        <path d="M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="chatType">Chat Type</label>
                    <select id="chatType" name="type">
                        <option value="private">Private Chat</option>
                        <option value="group">Group Chat</option>
                        <option value="channel">Channel</option>
                    </select>
                </div>
                <div class="form-group" id="chatNameGroup" style="display: none;">
                    <label for="chatName">Chat Name</label>
                    <input type="text" id="chatName" name="name">
                </div>
                <div class="form-group" id="chatDescriptionGroup" style="display: none;">
                    <label for="chatDescription">Description</label>
                    <textarea id="chatDescription" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="chatUsers">Select Users</label>
                    <input type="text" id="userSearch" placeholder="Search users...">
                    <div class="users-list" id="usersList"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelNewChat">Cancel</button>
                <button class="btn btn-primary" id="createChat">Create Chat</button>
            </div>
        </div>
    </div>
    
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/chat.js') }}"></script>
</body>
</html>
