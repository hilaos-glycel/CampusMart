<?php
$pageTitle = 'Messages';
require_once 'includes/header.php';

// Redirect if not logged in
if (!$isLoggedIn) {
    header('Location: login.php');
    exit;
}
?>

<main class="messages-main">
    <div class="messages-container">
        <!-- Messages Header -->
        <div class="messages-header">
            <div class="header-content">
                <h1 class="page-title">
                    <i class="fas fa-comments"></i>
                    Messages
                </h1>
                <button class="new-message-btn" id="newMessageBtn">
                    <i class="fas fa-plus"></i>
                    New Message
                </button>
            </div>
        </div>

        <!-- Messages Layout -->
        <div class="messages-layout">
            <!-- Conversations Sidebar -->
            <div class="conversations-sidebar" id="conversationsSidebar">
                <div class="sidebar-header">
                    <h3>Conversations</h3>
                    <div class="search-conversations">
                        <input type="text" placeholder="Search conversations..." id="searchConversations">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                
                <div class="conversations-list" id="conversationsList">
                    <div class="loading-conversations">
                        <div class="loading-spinner"></div>
                        <p>Loading conversations...</p>
                    </div>
                </div>
            </div>

            <!-- Online Users & Friends Sidebar -->
            <div class="friends-sidebar" id="friendsSidebar">
                <!-- Online Users Section -->
                <div class="sidebar-section">
                    <div class="section-header">
                        <h3><i class="fas fa-circle online-indicator"></i> Online Users</h3>
                        <span class="online-count" id="onlineCount">0</span>
                    </div>
                    <div class="online-users-list" id="onlineUsersList">
                        <div class="loading-users">
                            <div class="loading-spinner"></div>
                            <p>Loading online users...</p>
                        </div>
                    </div>
                </div>

                <!-- Friend Requests Section -->
                <div class="sidebar-section">
                    <div class="section-header">
                        <h3><i class="fas fa-user-plus"></i> Friend Requests</h3>
                        <span class="requests-count" id="requestsCount">0</span>
                    </div>
                    <div class="friend-requests-list" id="friendRequestsList">
                        <div class="no-requests">
                            <p>No pending requests</p>
                        </div>
                    </div>
                </div>

                <!-- Friends List Section -->
                <div class="sidebar-section">
                    <div class="section-header">
                        <h3><i class="fas fa-users"></i> Friends</h3>
                        <span class="friends-count" id="friendsCount">0</span>
                    </div>
                    <div class="friends-list" id="friendsList">
                        <div class="loading-friends">
                            <div class="loading-spinner"></div>
                            <p>Loading friends...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="chat-area" id="chatArea">
                <div class="no-conversation-selected">
                    <div class="no-conversation-content">
                        <i class="fas fa-comments"></i>
                        <h3>Select a conversation</h3>
                        <p>Choose a conversation from the sidebar to start messaging</p>
                        <button class="start-new-conversation" id="startNewConversation">
                            <i class="fas fa-plus"></i>
                            Start New Conversation
                        </button>
                    </div>
                </div>

                <!-- Chat Header -->
                <div class="chat-header" id="chatHeader" style="display: none;">
                    <div class="chat-user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-details">
                            <h4 class="user-name" id="chatUserName"></h4>
                            <p class="user-status" id="chatUserStatus">Online</p>
                        </div>
                    </div>
                    <div class="chat-actions">
                        <button class="chat-action-btn" title="Call">
                            <i class="fas fa-phone"></i>
                        </button>
                        <button class="chat-action-btn" title="Video Call">
                            <i class="fas fa-video"></i>
                        </button>
                        <button class="chat-action-btn" title="More Options">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>

                <!-- Messages Container -->
                <div class="messages-container-chat" id="messagesContainer" style="display: none;">
                    <div class="messages-list" id="messagesList">
                        <!-- Messages will be loaded here -->
                    </div>
                </div>

                <!-- Message Input -->
                <div class="message-input-container" id="messageInputContainer" style="display: none;">
                    <form class="message-form" id="messageForm">
                        <div class="message-input-wrapper">
                            <button type="button" class="attachment-btn" title="Attach File">
                                <i class="fas fa-paperclip"></i>
                            </button>
                            <textarea 
                                class="message-input" 
                                id="messageInput" 
                                placeholder="Type your message..."
                                rows="1"
                            ></textarea>
                            <button type="button" class="emoji-btn" title="Add Emoji">
                                <i class="fas fa-smile"></i>
                            </button>
                            <button type="submit" class="send-btn" id="sendBtn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- New Message Modal -->
    <div class="modal-overlay" id="newMessageModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>New Message</h3>
                <button class="modal-close" id="closeNewMessageModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="user-search">
                    <label for="userSearch">To:</label>
                    <input type="text" id="userSearch" placeholder="Search users...">
                    <div class="search-results" id="searchResults"></div>
                </div>
                <div class="message-compose">
                    <label for="newMessageText">Message:</label>
                    <textarea id="newMessageText" placeholder="Type your message..." rows="4"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelNewMessage">Cancel</button>
                <button class="btn btn-primary" id="sendNewMessage">Send Message</button>
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <div class="notification-container" id="notificationContainer"></div>
</main>

<!-- CSS Styles -->
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/messages.css">

<!-- JavaScript -->
<script>
// Global variables
let currentConversationId = null;
let currentOtherUser = null;
let messagesPollingInterval = null;
let conversationsPollingInterval = null;

// Initialize messaging system
document.addEventListener('DOMContentLoaded', function() {
    initializeMessaging();
});

function initializeMessaging() {
    loadConversations();
    setupEventListeners();
    startPolling();
}

function setupEventListeners() {
    // New message button
    document.getElementById('newMessageBtn').addEventListener('click', openNewMessageModal);
    document.getElementById('startNewConversation').addEventListener('click', openNewMessageModal);
    
    // Modal controls
    document.getElementById('closeNewMessageModal').addEventListener('click', closeNewMessageModal);
    document.getElementById('cancelNewMessage').addEventListener('click', closeNewMessageModal);
    document.getElementById('sendNewMessage').addEventListener('click', sendNewMessage);
    
    // Message form
    document.getElementById('messageForm').addEventListener('submit', sendMessage);
    
    // Auto-resize textarea
    const messageInput = document.getElementById('messageInput');
    messageInput.addEventListener('input', autoResizeTextarea);
    
    // User search
    document.getElementById('userSearch').addEventListener('input', debounce(searchUsers, 300));
    
    // Conversation search
    document.getElementById('searchConversations').addEventListener('input', debounce(filterConversations, 300));
    
    // Close modal on overlay click
    document.getElementById('newMessageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeNewMessageModal();
        }
    });
}

async function loadConversations() {
    try {
        const response = await fetch(`${window.siteUrl}/api/get_conversations.php`);
        const data = await response.json();
        
        if (data.success) {
            displayConversations(data.conversations);
            updateUnreadCount(data.total_unread);
        } else {
            showNotification('Error loading conversations', 'error');
        }
    } catch (error) {
        console.error('Error loading conversations:', error);
        showNotification('Error loading conversations', 'error');
    }
}

function displayConversations(conversations) {
    const conversationsList = document.getElementById('conversationsList');
    
    if (conversations.length === 0) {
        conversationsList.innerHTML = `
            <div class="no-conversations">
                <i class="fas fa-comments"></i>
                <p>No conversations yet</p>
                <button class="start-conversation-btn" onclick="openNewMessageModal()">
                    Start a conversation
                </button>
            </div>
        `;
        return;
    }
    
    const conversationsHTML = conversations.map(conv => `
        <div class="conversation-item ${conv.unread_count > 0 ? 'unread' : ''}" 
             data-conversation-id="${conv.id}" 
             onclick="selectConversation(${conv.id}, ${JSON.stringify(conv.other_user).replace(/"/g, '&quot;')})">
            <div class="conversation-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="conversation-info">
                <div class="conversation-header">
                    <h4 class="conversation-name">${escapeHtml(conv.other_user.name)}</h4>
                    <span class="conversation-time">${formatTime(conv.last_message.time)}</span>
                </div>
                <div class="conversation-preview">
                    <p class="last-message">${escapeHtml(conv.last_message.text || 'No messages yet')}</p>
                    ${conv.unread_count > 0 ? `<span class="unread-badge">${conv.unread_count}</span>` : ''}
                </div>
            </div>
        </div>
    `).join('');
    
    conversationsList.innerHTML = conversationsHTML;
}

async function selectConversation(conversationId, otherUser) {
    currentConversationId = conversationId;
    currentOtherUser = otherUser;
    
    // Update UI
    document.querySelector('.no-conversation-selected').style.display = 'none';
    document.getElementById('chatHeader').style.display = 'flex';
    document.getElementById('messagesContainer').style.display = 'flex';
    document.getElementById('messageInputContainer').style.display = 'block';
    
    // Update chat header
    document.getElementById('chatUserName').textContent = otherUser.name;
    
    // Mark conversation as active
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-conversation-id="${conversationId}"]`).classList.add('active');
    
    // Load messages
    await loadMessages(conversationId);
}

async function loadMessages(conversationId) {
    // Validate conversation ID before making API call
    if (!conversationId || conversationId === 'undefined' || conversationId === 'null') {
        console.log('No conversation ID provided, skipping message load');
        return;
    }
    
    try {
        const response = await fetch(`${window.siteUrl}/api/get_messages.php?conversation_id=${conversationId}`);
        const data = await response.json();
        
        if (data.success) {
            displayMessages(data.messages);
            scrollToBottom();
        } else {
            // Only show error if it's not about missing conversation ID
            if (!data.message || !data.message.includes('Conversation ID')) {
                showNotification('Error loading messages', 'error');
            }
        }
    } catch (error) {
        console.error('Error loading messages:', error);
        showNotification('Error loading messages', 'error');
    }
}

function displayMessages(messages) {
    const messagesList = document.getElementById('messagesList');
    
    const messagesHTML = messages.map(msg => `
        <div class="message ${msg.is_own_message ? 'own-message' : 'other-message'}">
            <div class="message-content">
                <div class="message-bubble">
                    <p class="message-text">${escapeHtml(msg.message)}</p>
                    <div class="message-meta">
                        <span class="message-time">${formatTime(msg.created_at)}</span>
                        ${msg.is_own_message ? `<span class="message-status ${msg.is_read ? 'read' : 'sent'}">
                            <i class="fas fa-check${msg.is_read ? '-double' : ''}"></i>
                        </span>` : ''}
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    messagesList.innerHTML = messagesHTML;
}

async function sendMessage(e) {
    e.preventDefault();
    
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message || !currentConversationId || !currentOtherUser) {
        return;
    }
    
    try {
        const response = await fetch(`${window.siteUrl}/api/send_message.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                conversation_id: currentConversationId,
                message: message
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageInput.value = '';
            autoResizeTextarea({ target: messageInput });
            
            // Add message to UI immediately
            addMessageToUI(data.message);
            scrollToBottom();
            
            // Refresh conversations to update last message
            loadConversations();
        } else {
            showNotification(data.message || 'Error sending message', 'error');
        }
    } catch (error) {
        console.error('Error sending message:', error);
        showNotification('Error sending message', 'error');
    }
}

function addMessageToUI(message) {
    const messagesList = document.getElementById('messagesList');
    const messageHTML = `
        <div class="message own-message">
            <div class="message-content">
                <div class="message-bubble">
                    <p class="message-text">${escapeHtml(message.message)}</p>
                    <div class="message-meta">
                        <span class="message-time">${formatTime(message.created_at)}</span>
                        <span class="message-status sent">
                            <i class="fas fa-check"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    messagesList.insertAdjacentHTML('beforeend', messageHTML);
}

function openNewMessageModal() {
    document.getElementById('newMessageModal').style.display = 'flex';
    document.getElementById('userSearch').focus();
}

function closeNewMessageModal() {
    document.getElementById('newMessageModal').style.display = 'none';
    document.getElementById('userSearch').value = '';
    document.getElementById('newMessageText').value = '';
    document.getElementById('searchResults').innerHTML = '';
}

async function searchUsers() {
    const query = document.getElementById('userSearch').value.trim();
    const searchResults = document.getElementById('searchResults');
    
    if (query.length < 2) {
        searchResults.innerHTML = '';
        return;
    }
    
    try {
        const response = await fetch(`${window.siteUrl}/api/search_users.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success) {
            displaySearchResults(data.users);
        }
    } catch (error) {
        console.error('Error searching users:', error);
    }
}

function displaySearchResults(users) {
    const searchResults = document.getElementById('searchResults');
    
    if (users.length === 0) {
        searchResults.innerHTML = '<div class="no-results">No users found</div>';
        return;
    }
    
    const resultsHTML = users.map(user => `
        <div class="search-result-item" onclick="selectUserForNewMessage(${user.id}, '${escapeHtml(user.name)}')">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-info">
                <h5>${escapeHtml(user.name)}</h5>
                <p>${escapeHtml(user.email)}</p>
            </div>
        </div>
    `).join('');
    
    searchResults.innerHTML = resultsHTML;
}

function selectUserForNewMessage(userId, userName) {
    document.getElementById('userSearch').value = userName;
    document.getElementById('userSearch').dataset.selectedUserId = userId;
    document.getElementById('searchResults').innerHTML = '';
}

async function sendNewMessage() {
    const userSearch = document.getElementById('userSearch');
    const messageText = document.getElementById('newMessageText').value.trim();
    const selectedUserId = userSearch.dataset.selectedUserId;
    
    if (!selectedUserId || !messageText) {
        showNotification('Please select a user and enter a message', 'error');
        return;
    }
    
    try {
        const response = await fetch(`${window.siteUrl}/api/send_message.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                receiver_id: parseInt(selectedUserId),
                message: messageText
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeNewMessageModal();
            showNotification('Message sent successfully', 'success');
            
            // Refresh conversations and select the new one
            await loadConversations();
            
            // Find and select the conversation
            setTimeout(() => {
                const conversationItem = document.querySelector(`[data-conversation-id="${data.message.conversation_id}"]`);
                if (conversationItem) {
                    conversationItem.click();
                }
            }, 500);
        } else {
            showNotification(data.message || 'Error sending message', 'error');
        }
    } catch (error) {
        console.error('Error sending new message:', error);
        showNotification('Error sending message', 'error');
    }
}

function startPolling() {
    // Poll for new messages every 5 seconds
    messagesPollingInterval = setInterval(() => {
        if (currentConversationId) {
            loadMessages(currentConversationId);
        }
    }, 5000);
    
    // Poll for conversation updates every 10 seconds
    conversationsPollingInterval = setInterval(() => {
        loadConversations();
    }, 10000);
}

function stopPolling() {
    if (messagesPollingInterval) {
        clearInterval(messagesPollingInterval);
    }
    if (conversationsPollingInterval) {
        clearInterval(conversationsPollingInterval);
    }
}

// Utility functions
function autoResizeTextarea(e) {
    const textarea = e.target;
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}

function scrollToBottom() {
    const messagesContainer = document.getElementById('messagesList');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function updateUnreadCount(count) {
    // Update header notification badge
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline' : 'none';
    }
}

function filterConversations() {
    const query = document.getElementById('searchConversations').value.toLowerCase();
    const conversations = document.querySelectorAll('.conversation-item');
    
    conversations.forEach(conv => {
        const name = conv.querySelector('.conversation-name').textContent.toLowerCase();
        const message = conv.querySelector('.last-message').textContent.toLowerCase();
        
        if (name.includes(query) || message.includes(query)) {
            conv.style.display = 'flex';
        } else {
            conv.style.display = 'none';
        }
    });
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) { // Less than 1 minute
        return 'Just now';
    } else if (diff < 3600000) { // Less than 1 hour
        return Math.floor(diff / 60000) + 'm ago';
    } else if (diff < 86400000) { // Less than 1 day
        return Math.floor(diff / 3600000) + 'h ago';
    } else if (diff < 604800000) { // Less than 1 week
        return Math.floor(diff / 86400000) + 'd ago';
    } else {
        return date.toLocaleDateString();
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showNotification(message, type = 'info') {
    const container = document.getElementById('notificationContainer');
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Online Users and Friends Functions
async function loadOnlineUsers() {
    try {
        const response = await fetch(`${window.siteUrl}/api/get_online_users.php`);
        const data = await response.json();
        
        if (data.success) {
            displayOnlineUsers(data.data);
            updateOnlineCount(data.counts.online + data.counts.recently_active);
        } else {
            console.error('Error loading online users:', data.message);
        }
    } catch (error) {
        console.error('Error loading online users:', error);
    }
}

function displayOnlineUsers(users) {
    const onlineUsersList = document.getElementById('onlineUsersList');
    
    // Combine online and recently active users
    const activeUsers = [...users.online, ...users.recently_active];
    
    if (activeUsers.length === 0) {
        onlineUsersList.innerHTML = '<div class="no-users"><p>No users online</p></div>';
        return;
    }
    
    const usersHTML = activeUsers.map(user => `
        <div class="user-item ${user.status}" data-user-id="${user.id}">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
                <span class="status-indicator ${user.status}"></span>
            </div>
            <div class="user-info">
                <div class="user-name">${user.name}</div>
                <div class="user-details">${user.course} - Year ${user.year_level}</div>
                ${user.status_message ? `<div class="status-message">${user.status_message}</div>` : ''}
            </div>
            <div class="user-actions">
                ${user.friendship_status === 'friends' ? 
                    `<button class="action-btn message-btn" onclick="startConversationWithUser(${user.id}, '${user.name}')" title="Message">
                        <i class="fas fa-comment"></i>
                    </button>` :
                    user.friendship_status === 'none' ?
                    `<button class="action-btn friend-btn" onclick="sendFriendRequest(${user.id})" title="Add Friend">
                        <i class="fas fa-user-plus"></i>
                    </button>` :
                    user.friendship_status === 'request_sent' ?
                    `<span class="request-status">Request Sent</span>` :
                    `<button class="action-btn accept-btn" onclick="acceptFriendRequest(${user.id})" title="Accept Request">
                        <i class="fas fa-check"></i>
                    </button>`
                }
            </div>
        </div>
    `).join('');
    
    onlineUsersList.innerHTML = usersHTML;
}

async function sendFriendRequest(userId) {
    try {
        const message = prompt('Add a message (optional):') || '';
        
        const response = await fetch(`${window.siteUrl}/api/send_friend_request.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                receiver_id: userId,
                message: message
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            loadOnlineUsers();
        } else {
            showNotification(data.message || 'Error sending friend request', 'error');
        }
    } catch (error) {
        console.error('Error sending friend request:', error);
        showNotification('Error sending friend request', 'error');
    }
}

async function startConversationWithUser(userId, userName) {
    try {
        const response = await fetch(`${window.siteUrl}/api/send_message.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                receiver_id: userId,
                message: 'Hi there! 👋'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(`Started conversation with ${userName}`, 'success');
            loadConversations();
        } else {
            showNotification(data.message || 'Error starting conversation', 'error');
        }
    } catch (error) {
        console.error('Error starting conversation:', error);
        showNotification('Error starting conversation', 'error');
    }
}

async function updateUserStatus(isOnline = true) {
    try {
        await fetch(`${window.siteUrl}/api/update_user_status.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ is_online: isOnline })
        });
    } catch (error) {
        console.error('Error updating user status:', error);
    }
}

function updateOnlineCount(count) {
    document.getElementById('onlineCount').textContent = count;
}

async function loadFriendRequests() {
    try {
        const response = await fetch(`${window.siteUrl}/api/get_friend_requests.php`);
        const data = await response.json();
        
        if (data.success) {
            displayFriendRequests(data.data);
            updateRequestsCount(data.counts.received);
        } else {
            console.error('Error loading friend requests:', data.message);
        }
    } catch (error) {
        console.error('Error loading friend requests:', error);
    }
}

function displayFriendRequests(requests) {
    const friendRequestsList = document.getElementById('friendRequestsList');
    
    if (requests.received.length === 0) {
        friendRequestsList.innerHTML = '<div class="no-requests"><p>No pending requests</p></div>';
        return;
    }
    
    const requestsHTML = requests.received.map(request => `
        <div class="request-item" data-request-id="${request.id}">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
                <span class="status-indicator ${request.sender.is_online ? 'online' : 'offline'}"></span>
            </div>
            <div class="request-info">
                <div class="user-name">${request.sender.name}</div>
                <div class="user-details">${request.sender.course} - Year ${request.sender.year_level}</div>
                ${request.message ? `<div class="request-message">"${request.message}"</div>` : ''}
                <div class="request-time">${formatTime(request.created_at)}</div>
            </div>
            <div class="request-actions">
                <button class="action-btn accept-btn" onclick="manageFriendRequest(${request.id}, 'accept')" title="Accept">
                    <i class="fas fa-check"></i>
                </button>
                <button class="action-btn decline-btn" onclick="manageFriendRequest(${request.id}, 'decline')" title="Decline">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `).join('');
    
    friendRequestsList.innerHTML = requestsHTML;
}

async function manageFriendRequest(requestId, action) {
    try {
        const response = await fetch(`${window.siteUrl}/api/manage_friend_request.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                request_id: requestId,
                action: action
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            loadFriendRequests(); // Refresh requests
            loadOnlineUsers(); // Refresh online users
            loadConversations(); // Refresh conversations
        } else {
            showNotification(data.message || 'Error managing friend request', 'error');
        }
    } catch (error) {
        console.error('Error managing friend request:', error);
        showNotification('Error managing friend request', 'error');
    }
}

function updateRequestsCount(count) {
    const requestsCount = document.getElementById('requestsCount');
    requestsCount.textContent = count;
    requestsCount.style.display = count > 0 ? 'inline' : 'none';
}

// Update initialization
const originalInit = initializeMessaging;
initializeMessaging = function() {
    originalInit();
    loadOnlineUsers();
    loadFriendRequests();
    updateUserStatus(true);
    
    // Set user offline when page unloads
    window.addEventListener('beforeunload', () => updateUserStatus(false));
    
    // Poll online users and friend requests every 30 seconds
    setInterval(() => {
        loadOnlineUsers();
        loadFriendRequests();
    }, 30000);
};

</script>

<?php require_once 'includes/footer.php'; ?>
