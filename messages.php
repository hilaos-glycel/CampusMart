<?php
$pageTitle = 'Messages';
require_once 'includes/header.php';

// Redirect if not logged in
if (!$isLoggedIn) {
    header('Location: login.php');
    exit;
}
?>

<main class="fb-messages-main">
    <div class="fb-messages-container">
        <!-- Messages Header -->
        <div class="fb-messages-header">
            <div class="header-left">
                <h1 class="messages-title">
                    <i class="fas fa-comment-dots"></i>
                    Messages
                </h1>
            </div>
            <div class="header-right">
                <button class="compose-btn" id="composeBtn">
                    <i class="fas fa-edit"></i>
                    New Message
                </button>
            </div>
        </div>

        <!-- Messages Layout -->
        <div class="fb-messages-layout">
            <!-- Conversations Sidebar -->
            <div class="fb-conversations-sidebar" id="conversationsSidebar">
                <!-- Search Bar -->
                <div class="search-bar">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search messages or people..." id="searchMessages">
                    </div>
                </div>

                <!-- Quick Filters -->
                <div class="quick-filters">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="unread">Unread</button>
                    <button class="filter-btn" data-filter="online">Online</button>
                </div>

                <!-- Conversations List -->
                <div class="fb-conversations-list" id="conversationsList">
                    <div class="loading-conversations">
                        <div class="loading-spinner"></div>
                        <p>Loading conversations...</p>
                    </div>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="fb-chat-area" id="chatArea">
                <!-- No Conversation Selected -->
                <div class="no-conversation" id="noConversation">
                    <div class="no-conversation-content">
                        <div class="message-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3>Your Messages</h3>
                        <p>Send private messages to classmates and connect with people on CampusMart.</p>
                        <button class="start-messaging-btn" id="startMessagingBtn">
                            <i class="fas fa-plus"></i>
                            Start Messaging
                        </button>
                    </div>
                </div>

                <!-- Active Chat -->
                <div class="active-chat" id="activeChat" style="display: none;">
                    <!-- Chat Header -->
                    <div class="fb-chat-header" id="chatHeader">
                        <div class="chat-user-info">
                            <div class="user-avatar">
                                <img src="" alt="" id="chatUserAvatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="avatar-fallback">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span class="online-dot" id="chatUserOnlineStatus"></span>
                            </div>
                            <div class="user-details">
                                <h4 class="user-name" id="chatUserName"></h4>
                                <p class="user-status" id="chatUserStatus">Active now</p>
                            </div>
                        </div>
                        <div class="chat-actions">
                            <button class="chat-action-btn" title="Call" id="callBtn">
                                <i class="fas fa-phone"></i>
                            </button>
                            <button class="chat-action-btn" title="Video Call" id="videoCallBtn">
                                <i class="fas fa-video"></i>
                            </button>
                            <button class="chat-action-btn" title="Info" id="infoBtn">
                                <i class="fas fa-info-circle"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Messages Container -->
                    <div class="fb-messages-container-chat" id="messagesContainer">
                        <div class="messages-list" id="messagesList">
                            <!-- Messages will be loaded here -->
                        </div>
                        
                        <!-- Typing Indicator -->
                        <div class="typing-indicator" id="typingIndicator" style="display: none;">
                            <div class="typing-dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                            <span class="typing-text">Someone is typing...</span>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="fb-message-input-container" id="messageInputContainer">
                        <div class="message-input-wrapper">
                            <div class="attachment-dropdown">
                                <button class="attachment-btn" title="Attach" id="attachBtn">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <div class="attachment-menu" id="attachmentMenu" style="display: none;">
                                    <button class="attachment-option" id="imageUploadBtn">
                                        <i class="fas fa-image"></i>
                                        <span>Photo</span>
                                    </button>
                                    <button class="attachment-option" id="videoUploadBtn">
                                        <i class="fas fa-video"></i>
                                        <span>Video</span>
                                    </button>
                                </div>
                            </div>
                            <div class="text-input-wrapper">
                                <textarea 
                                    placeholder="Type a message..." 
                                    id="messageInput"
                                    rows="1"
                                    maxlength="2000"
                                ></textarea>
                                <button class="emoji-btn" title="Emoji" id="emojiBtn">
                                    <i class="fas fa-smile"></i>
                                </button>
                            </div>
                            <button class="send-btn" id="sendBtn" disabled>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        
                        <!-- Media Preview -->
                        <div class="media-preview" id="mediaPreview" style="display: none;">
                            <div class="preview-content">
                                <img id="imagePreview" style="display: none;" />
                                <video id="videoPreview" style="display: none;" controls></video>
                                <div class="preview-info">
                                    <span class="filename" id="previewFilename"></span>
                                    <span class="filesize" id="previewFilesize"></span>
                                </div>
                            </div>
                            <button class="remove-preview" id="removePreview">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <!-- Emoji Picker -->
                        <div class="emoji-picker" id="emojiPicker" style="display: none;">
                            <div class="emoji-categories">
                                <div class="emoji-category">
                                    <h4>😊 Smileys</h4>
                                    <div class="emoji-grid">
                                        <button class="emoji-option" data-emoji="😀">😀</button>
                                        <button class="emoji-option" data-emoji="😃">😃</button>
                                        <button class="emoji-option" data-emoji="😄">😄</button>
                                        <button class="emoji-option" data-emoji="😁">😁</button>
                                        <button class="emoji-option" data-emoji="😆">😆</button>
                                        <button class="emoji-option" data-emoji="😅">😅</button>
                                        <button class="emoji-option" data-emoji="😂">😂</button>
                                        <button class="emoji-option" data-emoji="🤣">🤣</button>
                                        <button class="emoji-option" data-emoji="😊">😊</button>
                                        <button class="emoji-option" data-emoji="😇">😇</button>
                                        <button class="emoji-option" data-emoji="🙂">🙂</button>
                                        <button class="emoji-option" data-emoji="🙃">🙃</button>
                                        <button class="emoji-option" data-emoji="😉">😉</button>
                                        <button class="emoji-option" data-emoji="😌">😌</button>
                                        <button class="emoji-option" data-emoji="😍">😍</button>
                                        <button class="emoji-option" data-emoji="🥰">🥰</button>
                                    </div>
                                </div>
                                <div class="emoji-category">
                                    <h4>❤️ Hearts</h4>
                                    <div class="emoji-grid">
                                        <button class="emoji-option" data-emoji="❤️">❤️</button>
                                        <button class="emoji-option" data-emoji="🧡">🧡</button>
                                        <button class="emoji-option" data-emoji="💛">💛</button>
                                        <button class="emoji-option" data-emoji="💚">💚</button>
                                        <button class="emoji-option" data-emoji="💙">💙</button>
                                        <button class="emoji-option" data-emoji="💜">💜</button>
                                        <button class="emoji-option" data-emoji="🖤">🖤</button>
                                        <button class="emoji-option" data-emoji="🤍">🤍</button>
                                        <button class="emoji-option" data-emoji="🤎">🤎</button>
                                        <button class="emoji-option" data-emoji="💔">💔</button>
                                        <button class="emoji-option" data-emoji="❣️">❣️</button>
                                        <button class="emoji-option" data-emoji="💕">💕</button>
                                    </div>
                                </div>
                                <div class="emoji-category">
                                    <h4>👍 Gestures</h4>
                                    <div class="emoji-grid">
                                        <button class="emoji-option" data-emoji="👍">👍</button>
                                        <button class="emoji-option" data-emoji="👎">👎</button>
                                        <button class="emoji-option" data-emoji="👌">👌</button>
                                        <button class="emoji-option" data-emoji="✌️">✌️</button>
                                        <button class="emoji-option" data-emoji="🤞">🤞</button>
                                        <button class="emoji-option" data-emoji="🤟">🤟</button>
                                        <button class="emoji-option" data-emoji="🤘">🤘</button>
                                        <button class="emoji-option" data-emoji="🤙">🤙</button>
                                        <button class="emoji-option" data-emoji="👈">👈</button>
                                        <button class="emoji-option" data-emoji="👉">👉</button>
                                        <button class="emoji-option" data-emoji="👆">👆</button>
                                        <button class="emoji-option" data-emoji="👇">👇</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Reactions -->
                        <div class="quick-reactions" id="quickReactions">
                            <button class="reaction-btn" data-reaction="👍">👍</button>
                            <button class="reaction-btn" data-reaction="❤️">❤️</button>
                            <button class="reaction-btn" data-reaction="😂">😂</button>
                            <button class="reaction-btn" data-reaction="😮">😮</button>
                            <button class="reaction-btn" data-reaction="😢">😢</button>
                            <button class="reaction-btn" data-reaction="👎">👎</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Message Modal -->
    <div class="fb-modal-overlay" id="newMessageModal" style="display: none;">
        <div class="fb-modal">
            <div class="modal-header">
                <h3>New Message</h3>
                <button class="close-btn" id="closeModalBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="recipient-search">
                    <label>To:</label>
                    <div class="search-recipients">
                        <input type="text" placeholder="Search people..." id="recipientSearch">
                    </div>
                </div>
                <div class="search-results" id="searchResults"></div>
                <div class="message-compose">
                    <textarea placeholder="Type your message..." id="composeMessage" rows="4"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn" id="cancelBtn">Cancel</button>
                <button class="send-message-btn" id="sendMessageBtn" disabled>Send</button>
            </div>
        </div>
    </div>

    <!-- Hidden File Inputs -->
    <input type="file" id="imageFileInput" accept="image/*" style="display: none;">
    <input type="file" id="videoFileInput" accept="video/*" style="display: none;">

    <!-- Notification Container -->
    <div class="notification-container" id="notificationContainer"></div>
</main>

<!-- CSS Styles -->
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/messages_facebook.css">

<!-- JavaScript -->
<script>
// Global variables
let currentConversationId = null;
let currentOtherUser = null;
let messagesPollingInterval = null;
let conversationsPollingInterval = null;
let typingTimeout = null;
let isTyping = false;

// Site URL for API calls
window.siteUrl = '<?php echo SITE_URL; ?>';

// Initialize messaging system
document.addEventListener('DOMContentLoaded', function() {
    initializeFacebookMessaging();
});

function initializeFacebookMessaging() {
    loadConversations();
    setupEventListeners();
    startPolling();
    updateUserStatus(true);
    
    // Set user offline when page unloads
    window.addEventListener('beforeunload', () => updateUserStatus(false));
}

function setupEventListeners() {
    // Compose button
    document.getElementById('composeBtn').addEventListener('click', openNewMessageModal);
    document.getElementById('startMessagingBtn').addEventListener('click', openNewMessageModal);
    
    // Modal events
    document.getElementById('closeModalBtn').addEventListener('click', closeNewMessageModal);
    document.getElementById('cancelBtn').addEventListener('click', closeNewMessageModal);
    document.getElementById('sendMessageBtn').addEventListener('click', sendNewMessage);
    
    // Message input events
    const messageInput = document.getElementById('messageInput');
    messageInput.addEventListener('input', handleMessageInput);
    messageInput.addEventListener('keypress', handleKeyPress);
    
    // Send button event
    document.getElementById('sendBtn').addEventListener('click', sendMessage);
    
    // Search events
    document.getElementById('searchMessages').addEventListener('input', handleSearch);
    document.getElementById('recipientSearch').addEventListener('input', searchRecipients);
    
    // Filter events
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', handleFilter);
    });
    
    // Quick reactions
    document.querySelectorAll('.reaction-btn').forEach(btn => {
        btn.addEventListener('click', sendQuickReaction);
    });
    
    // Media upload events
    document.getElementById('imageUploadBtn').addEventListener('click', () => {
        document.getElementById('imageFileInput').click();
    });
    
    document.getElementById('videoUploadBtn').addEventListener('click', () => {
        document.getElementById('videoFileInput').click();
    });
    
    // File input events
    document.getElementById('imageFileInput').addEventListener('change', handleImageUpload);
    document.getElementById('videoFileInput').addEventListener('change', handleVideoUpload);
    
    // Emoji picker events
    document.getElementById('emojiBtn').addEventListener('click', toggleEmojiPicker);
    document.querySelectorAll('.emoji-option').forEach(btn => {
        btn.addEventListener('click', insertEmoji);
    });
    
    // Attachment menu toggle
    document.getElementById('attachBtn').addEventListener('click', toggleAttachmentMenu);
    
    // Media preview events
    document.getElementById('removePreview').addEventListener('click', removeMediaPreview);
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.attachment-dropdown')) {
            document.getElementById('attachmentMenu').style.display = 'none';
        }
        if (!e.target.closest('.emoji-picker') && !e.target.closest('.emoji-btn')) {
            document.getElementById('emojiPicker').style.display = 'none';
        }
    });
}

// Load conversations
async function loadConversations() {
    try {
        const response = await fetch(`${window.siteUrl}/api/get_conversations.php`);
        const data = await response.json();
        
        if (data.success) {
            displayConversations(data.conversations);
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
                <div class="no-conversations-content">
                    <i class="fas fa-comments"></i>
                    <h4>No messages yet</h4>
                    <p>Start a conversation with your classmates</p>
                    <button class="start-conversation-btn" onclick="openNewMessageModal()">
                        <i class="fas fa-plus"></i>
                        Start Messaging
                    </button>
                </div>
            </div>
        `;
        return;
    }
    
    const conversationsHTML = conversations.map(conv => {
        const lastMessage = conv.last_message ? conv.last_message.text : 'Start a conversation';
        const timeAgo = conv.last_message ? formatTimeAgo(conv.last_message.created_at) : '';
        const isUnread = conv.unread_count > 0;
        
        return `
            <div class="conversation-item ${isUnread ? 'unread' : ''}" 
                 data-conversation-id="${conv.id}" 
                 onclick="selectConversation(${conv.id}, ${JSON.stringify(conv.other_user).replace(/"/g, '&quot;')})">
                <div class="conversation-avatar">
                    <img src="${window.siteUrl}/assets/images/default-avatar.png" 
                         alt="${conv.other_user.name}"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="avatar-fallback">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="online-indicator ${conv.other_user.is_online ? 'online' : ''}"></span>
                </div>
                <div class="conversation-content">
                    <div class="conversation-header">
                        <h4 class="user-name">${conv.other_user.name}</h4>
                        <span class="message-time">${timeAgo}</span>
                    </div>
                    <div class="conversation-preview">
                        <p class="last-message">${lastMessage}</p>
                        ${isUnread ? `<span class="unread-badge">${conv.unread_count}</span>` : ''}
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    conversationsList.innerHTML = conversationsHTML;
}

// Select conversation
async function selectConversation(conversationId, otherUser) {
    currentConversationId = conversationId;
    currentOtherUser = otherUser;
    
    // Update UI
    document.getElementById('noConversation').style.display = 'none';
    document.getElementById('activeChat').style.display = 'flex';
    
    // Update chat header
    document.getElementById('chatUserName').textContent = otherUser.name;
    document.getElementById('chatUserStatus').textContent = otherUser.is_online ? 'Active now' : 'Active recently';
    
    // Update active conversation
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-conversation-id="${conversationId}"]`).classList.add('active');
    
    // Load messages
    await loadMessages(conversationId);
}

// Load messages for conversation
async function loadMessages(conversationId) {
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
    
    const messagesHTML = messages.map(message => {
        const isOwnMessage = message.is_own_message;
        const timeFormatted = formatTime(message.created_at);
        
        let messageContent = '';
        
        if (message.message_type === 'image') {
            messageContent = `
                <div class="media-message image-message">
                    <img src="${message.media.url}" alt="${message.media.filename}" 
                         onclick="openImageModal('${message.media.url}')" 
                         style="max-width: 300px; max-height: 200px; border-radius: 8px; cursor: pointer;">
                    ${message.message ? `<p class="media-caption">${escapeHtml(message.message)}</p>` : ''}
                </div>
            `;
        } else if (message.message_type === 'video') {
            messageContent = `
                <div class="media-message video-message">
                    <video controls style="max-width: 300px; max-height: 200px; border-radius: 8px;">
                        <source src="${message.media.url}" type="${message.media.mime_type}">
                        Your browser does not support the video tag.
                    </video>
                    ${message.message ? `<p class="media-caption">${escapeHtml(message.message)}</p>` : ''}
                </div>
            `;
        } else {
            messageContent = `<p>${escapeHtml(message.message)}</p>`;
        }
        
        return `
            <div class="message-wrapper ${isOwnMessage ? 'own-message' : 'other-message'}">
                <div class="message-bubble">
                    <div class="message-content">
                        ${messageContent}
                    </div>
                    <div class="message-meta">
                        <span class="message-time">${timeFormatted}</span>
                        ${message.is_read ? '<i class="fas fa-check-double read-receipt"></i>' : '<i class="fas fa-check sent-receipt"></i>'}
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    messagesList.innerHTML = messagesHTML;
}

// Send message
async function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message || !currentConversationId) {
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
            
            // Reload messages
            await loadMessages(currentConversationId);
            
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

// Handle message input
function handleMessageInput(e) {
    const message = e.target.value.trim();
    const sendBtn = document.getElementById('sendBtn');
    
    // Enable send button if there's text OR media
    sendBtn.disabled = !message && !currentMediaData;
    autoResizeTextarea(e);
    
    // Show typing indicator
    if (message && !isTyping) {
        isTyping = true;
        // Send typing indicator to other user
    }
    
    // Clear typing timeout
    clearTimeout(typingTimeout);
    typingTimeout = setTimeout(() => {
        isTyping = false;
        // Hide typing indicator
    }, 1000);
}

function handleKeyPress(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

// Auto resize textarea
function autoResizeTextarea(e) {
    const textarea = e.target;
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}

// Utility functions
function formatTimeAgo(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'now';
    if (diff < 3600000) return Math.floor(diff / 60000) + 'm';
    if (diff < 86400000) return Math.floor(diff / 3600000) + 'h';
    if (diff < 604800000) return Math.floor(diff / 86400000) + 'd';
    return date.toLocaleDateString();
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function scrollToBottom() {
    const messagesContainer = document.getElementById('messagesContainer');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Modal functions
function openNewMessageModal() {
    document.getElementById('newMessageModal').style.display = 'flex';
    document.getElementById('recipientSearch').focus();
}

function closeNewMessageModal() {
    document.getElementById('newMessageModal').style.display = 'none';
    document.getElementById('recipientSearch').value = '';
    document.getElementById('composeMessage').value = '';
    document.getElementById('searchResults').innerHTML = '';
}

// Search recipients
async function searchRecipients(e) {
    const query = e.target.value.trim();
    
    if (query.length < 2) {
        document.getElementById('searchResults').innerHTML = '';
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
    
    const usersHTML = users.map(user => `
        <div class="user-result" onclick="selectRecipient(${user.id}, '${user.name}')">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-info">
                <div class="user-name">${user.name}</div>
                <div class="user-details">${user.course} - ${user.year_level}</div>
            </div>
        </div>
    `).join('');
    
    searchResults.innerHTML = usersHTML;
}

function selectRecipient(userId, userName) {
    document.getElementById('recipientSearch').value = userName;
    document.getElementById('recipientSearch').dataset.selectedUserId = userId;
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('sendMessageBtn').disabled = false;
}

// Send new message
async function sendNewMessage() {
    const recipientSearch = document.getElementById('recipientSearch');
    const composeMessage = document.getElementById('composeMessage');
    const selectedUserId = recipientSearch.dataset.selectedUserId;
    const messageText = composeMessage.value.trim();
    
    if (!selectedUserId || !messageText) {
        showNotification('Please select a recipient and enter a message', 'error');
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
            
            // Refresh conversations
            await loadConversations();
        } else {
            showNotification(data.message || 'Error sending message', 'error');
        }
    } catch (error) {
        console.error('Error sending new message:', error);
        showNotification('Error sending message', 'error');
    }
}

// Status update
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

// Polling
function startPolling() {
    // Poll for new messages every 3 seconds
    messagesPollingInterval = setInterval(() => {
        if (currentConversationId) {
            loadMessages(currentConversationId);
        }
    }, 3000);
    
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

// Notification system
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

// Filter and search functions
function handleFilter(e) {
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    e.target.classList.add('active');
    
    const filter = e.target.dataset.filter;
    // Implement filtering logic here
}

function handleSearch(e) {
    const query = e.target.value.toLowerCase();
    // Implement search logic here
}

function sendQuickReaction(e) {
    const reaction = e.target.dataset.reaction;
    
    // Insert emoji into message input
    const messageInput = document.getElementById('messageInput');
    const currentValue = messageInput.value;
    messageInput.value = currentValue + reaction;
    messageInput.focus();
    
    // Enable send button if there's content
    const sendBtn = document.getElementById('sendBtn');
    sendBtn.disabled = !messageInput.value.trim() && !currentMediaData;
}

// Media upload functions
let currentMediaFile = null;
let currentMediaData = null;

function toggleAttachmentMenu() {
    const menu = document.getElementById('attachmentMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

function toggleEmojiPicker() {
    const picker = document.getElementById('emojiPicker');
    picker.style.display = picker.style.display === 'none' ? 'block' : 'none';
}

function insertEmoji(e) {
    const emoji = e.target.dataset.emoji;
    const messageInput = document.getElementById('messageInput');
    
    // Insert emoji at cursor position
    const start = messageInput.selectionStart;
    const end = messageInput.selectionEnd;
    const text = messageInput.value;
    
    messageInput.value = text.substring(0, start) + emoji + text.substring(end);
    messageInput.selectionStart = messageInput.selectionEnd = start + emoji.length;
    messageInput.focus();
    
    // Enable send button
    const sendBtn = document.getElementById('sendBtn');
    sendBtn.disabled = !messageInput.value.trim() && !currentMediaData;
    
    // Hide emoji picker
    document.getElementById('emojiPicker').style.display = 'none';
}

async function handleImageUpload(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
        showNotification('Please select a valid image file', 'error');
        return;
    }
    
    // Validate file size (max 10MB)
    if (file.size > 10 * 1024 * 1024) {
        showNotification('Image file too large. Maximum size is 10MB', 'error');
        return;
    }
    
    await uploadMedia(file, 'image');
    document.getElementById('attachmentMenu').style.display = 'none';
}

async function handleVideoUpload(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    // Validate file type
    if (!file.type.startsWith('video/')) {
        showNotification('Please select a valid video file', 'error');
        return;
    }
    
    // Validate file size (max 50MB)
    if (file.size > 50 * 1024 * 1024) {
        showNotification('Video file too large. Maximum size is 50MB', 'error');
        return;
    }
    
    await uploadMedia(file, 'video');
    document.getElementById('attachmentMenu').style.display = 'none';
}

async function uploadMedia(file, type) {
    const formData = new FormData();
    formData.append('media', file);
    
    try {
        showNotification('Uploading file...', 'info');
        
        const response = await fetch(`${window.siteUrl}/api/upload_media.php`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentMediaFile = file;
            currentMediaData = data.data;
            showMediaPreview(file, type, data.data);
            showNotification('File uploaded successfully', 'success');
        } else {
            showNotification(data.message || 'Upload failed', 'error');
        }
    } catch (error) {
        console.error('Upload error:', error);
        showNotification('Upload failed', 'error');
    }
}

function showMediaPreview(file, type, mediaData) {
    const preview = document.getElementById('mediaPreview');
    const imagePreview = document.getElementById('imagePreview');
    const videoPreview = document.getElementById('videoPreview');
    const filename = document.getElementById('previewFilename');
    const filesize = document.getElementById('previewFilesize');
    
    // Hide both previews first
    imagePreview.style.display = 'none';
    videoPreview.style.display = 'none';
    
    if (type === 'image') {
        imagePreview.src = mediaData.file_url;
        imagePreview.style.display = 'block';
    } else if (type === 'video') {
        videoPreview.src = mediaData.file_url;
        videoPreview.style.display = 'block';
    }
    
    filename.textContent = file.name;
    filesize.textContent = formatFileSize(file.size);
    
    preview.style.display = 'block';
    
    // Enable send button
    document.getElementById('sendBtn').disabled = false;
}

function removeMediaPreview() {
    document.getElementById('mediaPreview').style.display = 'none';
    currentMediaFile = null;
    currentMediaData = null;
    
    // Reset file inputs
    document.getElementById('imageFileInput').value = '';
    document.getElementById('videoFileInput').value = '';
    
    // Update send button state (since we just cleared media, only check text)
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    sendBtn.disabled = !messageInput.value.trim();
}

// Enhanced send message function
async function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message && !currentMediaData) {
        return;
    }
    
    if (!currentConversationId) {
        showNotification('Please select a conversation first', 'error');
        return;
    }
    
    try {
        let messageData = {
            conversation_id: currentConversationId,
            message: message
        };
        
        // Add media data if present
        if (currentMediaData) {
            messageData.message_type = currentMediaData.file_type;
            messageData.media_url = currentMediaData.file_path;
            messageData.media_filename = currentMediaData.filename;
            messageData.media_size = currentMediaData.file_size;
            messageData.media_mime_type = currentMediaData.mime_type;
            messageData.thumbnail_url = currentMediaData.thumbnail_url;
        }
        
        const response = await fetch(`${window.siteUrl}/api/send_message.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(messageData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageInput.value = '';
            autoResizeTextarea({ target: messageInput });
            
            // Clear media preview
            if (currentMediaData) {
                removeMediaPreview();
            }
            
            // Reload messages
            await loadMessages(currentConversationId);
            
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

// Utility functions
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function openImageModal(imageUrl) {
    // Create modal for full-size image viewing
    const modal = document.createElement('div');
    modal.className = 'image-modal';
    modal.innerHTML = `
        <div class="image-modal-content">
            <img src="${imageUrl}" alt="Full size image">
            <button class="close-image-modal">&times;</button>
        </div>
    `;
    
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    `;
    
    modal.querySelector('.image-modal-content').style.cssText = `
        position: relative;
        max-width: 90%;
        max-height: 90%;
    `;
    
    modal.querySelector('img').style.cssText = `
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    `;
    
    modal.querySelector('.close-image-modal').style.cssText = `
        position: absolute;
        top: -40px;
        right: 0;
        background: none;
        border: none;
        color: white;
        font-size: 30px;
        cursor: pointer;
    `;
    
    document.body.appendChild(modal);
    
    // Close modal events
    modal.addEventListener('click', function(e) {
        if (e.target === modal || e.target.classList.contains('close-image-modal')) {
            document.body.removeChild(modal);
        }
    });
}

</script>

<?php require_once 'includes/footer.php'; ?>
