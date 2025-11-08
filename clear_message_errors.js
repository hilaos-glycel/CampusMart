// Clear Message Error Notifications
// Run this in browser console to remove persistent error notifications

console.log('🧹 Clearing message error notifications...');

// Find and remove error notifications
const errorSelectors = [
    '.notification.notification-error',
    '.notification-error', 
    '.alert-error',
    '[class*="error"]',
    '.notification'
];

let removedCount = 0;

errorSelectors.forEach(selector => {
    const elements = document.querySelectorAll(selector);
    elements.forEach(element => {
        const text = element.textContent || element.innerText;
        if (text.includes('Conversation ID') || 
            text.includes('Error loading') ||
            text.includes('required')) {
            console.log('Removing notification:', text.substring(0, 50) + '...');
            element.remove();
            removedCount++;
        }
    });
});

// Also check for any notification containers
const notificationContainers = document.querySelectorAll('#notificationContainer, .notification-container, [id*="notification"]');
notificationContainers.forEach(container => {
    const notifications = container.querySelectorAll('*');
    notifications.forEach(notification => {
        const text = notification.textContent || notification.innerText;
        if (text.includes('Conversation ID') || text.includes('Error loading')) {
            console.log('Removing from container:', text.substring(0, 50) + '...');
            notification.remove();
            removedCount++;
        }
    });
});

// Clear any inline error messages
const allElements = document.querySelectorAll('*');
allElements.forEach(element => {
    if (element.children.length === 0) { // Only text nodes
        const text = element.textContent || element.innerText;
        if (text.trim() === 'Conversation ID is required' || 
            text.trim() === 'Error loading messages') {
            console.log('Removing inline error:', text);
            element.remove();
            removedCount++;
        }
    }
});

console.log(`✅ Removed ${removedCount} error notifications`);

if (removedCount === 0) {
    console.log('ℹ️ No error notifications found to remove');
} else {
    console.log('🎉 Error notifications cleared successfully!');
}

// Prevent future "Conversation ID is required" errors
if (typeof loadMessages === 'function') {
    const originalLoadMessages = loadMessages;
    window.loadMessages = function(conversationId) {
        if (!conversationId || conversationId === 'undefined' || conversationId === 'null') {
            console.log('🚫 Prevented loadMessages call with invalid conversation ID:', conversationId);
            return Promise.resolve();
        }
        return originalLoadMessages.call(this, conversationId);
    };
    console.log('🛡️ Added protection against invalid loadMessages calls');
}
