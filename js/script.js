// CampusMart - Main JavaScript File
// Common functionality and interactive features

// Global variables
let isMenuOpen = false;

// DOM Content Loaded Event
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// Initialize the application
function initializeApp() {
    setupNavigation();
    setupFormValidation();
    setupAnimations();
    checkAuthStatus();
}

// Navigation Setup
function setupNavigation() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            toggleMobileMenu();
        });
    }
    
    // Close mobile menu when clicking on a link
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (isMenuOpen) {
                toggleMobileMenu();
            }
        });
    });
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        if (isMenuOpen && !hamburger.contains(event.target) && !navMenu.contains(event.target)) {
            toggleMobileMenu();
        }
    });
}

// Toggle Mobile Menu
function toggleMobileMenu() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger && navMenu) {
        navMenu.classList.toggle('active');
        hamburger.classList.toggle('active');
        isMenuOpen = !isMenuOpen;
        
        // Animate hamburger bars
        const bars = hamburger.querySelectorAll('.bar');
        if (isMenuOpen) {
            bars[0].style.transform = 'rotate(-45deg) translate(-5px, 6px)';
            bars[1].style.opacity = '0';
            bars[2].style.transform = 'rotate(45deg) translate(-5px, -6px)';
        } else {
            bars[0].style.transform = 'none';
            bars[1].style.opacity = '1';
            bars[2].style.transform = 'none';
        }
    }
}

// Form Validation Setup
function setupFormValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(input);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(input);
            });
        });
    });
}

// Validate individual field
function validateField(field) {
    const value = field.value.trim();
    const fieldType = field.type;
    const fieldName = field.name;
    
    // Remove existing error styling
    clearFieldError(field);
    
    // Check if field is required and empty
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, `${getFieldLabel(field)} is required`);
        return false;
    }
    
    // Type-specific validation
    switch (fieldType) {
        case 'email':
            if (value && !isValidEmail(value)) {
                showFieldError(field, 'Please enter a valid email address');
                return false;
            }
            break;
        case 'password':
            if (value && value.length < 6) {
                showFieldError(field, 'Password must be at least 6 characters long');
                return false;
            }
            break;
        case 'number':
            if (value && (isNaN(value) || parseFloat(value) <= 0)) {
                showFieldError(field, 'Please enter a valid positive number');
                return false;
            }
            break;
    }
    
    // Custom validation for student ID
    if (fieldName === 'studentId' && value && !value.toUpperCase().startsWith('JH')) {
        showFieldError(field, 'Student ID must start with "JH"');
        return false;
    }
    
    // Custom validation for JH email
    if (fieldName === 'email' && field.pattern && value && !new RegExp(field.pattern).test(value)) {
        showFieldError(field, 'Please use your official JH email address');
        return false;
    }
    
    return true;
}

// Show field error
function showFieldError(field, message) {
    field.style.borderColor = '#e74c3c';
    
    // Remove existing error message
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Add error message
    const errorElement = document.createElement('small');
    errorElement.className = 'field-error';
    errorElement.style.color = '#e74c3c';
    errorElement.style.marginTop = '0.25rem';
    errorElement.style.display = 'block';
    errorElement.textContent = message;
    
    field.parentNode.appendChild(errorElement);
}

// Clear field error
function clearFieldError(field) {
    field.style.borderColor = '#e1e5e9';
    
    const errorElement = field.parentNode.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
}

// Get field label text
function getFieldLabel(field) {
    const label = field.parentNode.querySelector('label');
    if (label) {
        return label.textContent.replace('*', '').trim();
    }
    return field.name.charAt(0).toUpperCase() + field.name.slice(1);
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Setup Animations
function setupAnimations() {
    // Smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    const animateElements = document.querySelectorAll('.feature-card, .category-card, .product-card');
    animateElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(element);
    });
}

// Check Authentication Status
function checkAuthStatus() {
    const isLoggedIn = localStorage.getItem('isLoggedIn');
    const currentPage = window.location.pathname.split('/').pop();
    
    // Update navigation based on auth status
    updateNavigationAuth(isLoggedIn);
    
    // Redirect logic for protected pages
    const protectedPages = ['dashboard.html', 'post-item.html', 'post-service.html'];
    
    if (protectedPages.includes(currentPage) && !isLoggedIn) {
        // For demo purposes, we'll redirect to login but show a message
        if (confirm('You need to log in to access this page. Would you like to go to the login page?')) {
            window.location.href = 'login.html';
        } else {
            window.location.href = 'index.html';
        }
    }
}

// Update navigation based on authentication
function updateNavigationAuth(isLoggedIn) {
    const loginBtn = document.querySelector('.nav-link.login-btn');
    if (loginBtn) {
        if (isLoggedIn) {
            loginBtn.innerHTML = '<i class="fas fa-user"></i> Dashboard';
            loginBtn.href = 'dashboard.html';
            loginBtn.classList.remove('login-btn');
        }
    }
}

// Utility Functions

// Format currency in Philippine Peso
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2
    }).format(amount);
}

// Format currency symbol only
function formatCurrencySymbol(amount) {
    return `₱${parseFloat(amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Generate random ID
function generateId() {
    return Math.random().toString(36).substr(2, 9);
}

// Debounce function
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

// Local Storage Helpers
const Storage = {
    set: function(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (error) {
            console.error('Error saving to localStorage:', error);
        }
    },
    
    get: function(key) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (error) {
            console.error('Error reading from localStorage:', error);
            return null;
        }
    },
    
    remove: function(key) {
        try {
            localStorage.removeItem(key);
        } catch (error) {
            console.error('Error removing from localStorage:', error);
        }
    },
    
    clear: function() {
        try {
            localStorage.clear();
        } catch (error) {
            console.error('Error clearing localStorage:', error);
        }
    }
};

// API Simulation Helpers
const API = {
    // Simulate network delay
    delay: function(ms = 1000) {
        return new Promise(resolve => setTimeout(resolve, ms));
    },
    
    // Simulate API response
    response: function(success = true, data = null, message = '') {
        return {
            success: success,
            data: data,
            message: message,
            timestamp: new Date().toISOString()
        };
    },
    
    // Simulate login
    login: async function(studentId, password) {
        await this.delay();
        
        // For demo purposes, accept any JH student ID with password "password"
        if (studentId.toUpperCase().startsWith('JH') && password === 'password') {
            return this.response(true, { studentId: studentId }, 'Login successful');
        } else {
            return this.response(false, null, 'Invalid credentials');
        }
    },
    
    // Simulate registration
    register: async function(userData) {
        await this.delay();
        
        // Basic validation
        if (!userData.studentId.toUpperCase().startsWith('JH')) {
            return this.response(false, null, 'Invalid student ID format');
        }
        
        if (!userData.email.endsWith('@jh.edu')) {
            return this.response(false, null, 'Invalid JH email address');
        }
        
        return this.response(true, { userId: generateId() }, 'Registration successful');
    }
};

// Global Error Handler
window.addEventListener('error', function(event) {
    console.error('Global error:', event.error);
    
    // Show user-friendly error message in production
    if (location.hostname !== 'localhost') {
        showNotification('An unexpected error occurred. Please try again.', 'error');
    }
});

// Notification System
function showNotification(message, type = 'info', duration = 5000) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
        color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
        padding: 1rem 2rem;
        border-radius: 5px;
        border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        animation: slideIn 0.3s ease;
        max-width: 300px;
    `;
    
    notification.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; margin-left: 1rem;">&times;</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after duration
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, duration);
}

// Add CSS for notifications if not present
if (!document.querySelector('#notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
}

// Enhanced Functionality

// Notification System with categories
class NotificationManager {
    constructor() {
        this.notifications = [];
        this.maxNotifications = 5;
        this.init();
    }

    init() {
        // Create notification container
        if (!document.querySelector('#notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                display: flex;
                flex-direction: column;
                gap: 10px;
                max-width: 350px;
            `;
            document.body.appendChild(container);
        }
    }

    show(message, type = 'info', duration = 5000, options = {}) {
        const notification = {
            id: generateId(),
            message,
            type,
            timestamp: Date.now()
        };

        this.notifications.unshift(notification);
        
        // Limit number of notifications
        if (this.notifications.length > this.maxNotifications) {
            this.notifications = this.notifications.slice(0, this.maxNotifications);
        }

        this.render(notification, duration, options);
        return notification.id;
    }

    render(notification, duration, options) {
        const container = document.querySelector('#notification-container');
        const element = document.createElement('div');
        element.id = `notification-${notification.id}`;
        element.className = `notification notification-${notification.type}`;
        
        const colors = {
            success: { bg: '#d4edda', border: '#c3e6cb', text: '#155724' },
            error: { bg: '#f8d7da', border: '#f5c6cb', text: '#721c24' },
            warning: { bg: '#fff3cd', border: '#ffeaa7', text: '#856404' },
            info: { bg: '#d1ecf1', border: '#bee5eb', text: '#0c5460' }
        };

        const color = colors[notification.type] || colors.info;
        
        element.style.cssText = `
            background: ${color.bg};
            color: ${color.text};
            border: 1px solid ${color.border};
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideInRight 0.3s ease-out;
            font-size: 14px;
            line-height: 1.4;
            position: relative;
            cursor: pointer;
        `;

        const icon = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        }[notification.type] || 'fas fa-info-circle';

        element.innerHTML = `
            <div style="display: flex; align-items: flex-start; gap: 10px;">
                <i class="${icon}" style="margin-top: 2px;"></i>
                <div style="flex: 1;">
                    <div style="font-weight: 600; margin-bottom: 4px;">${options.title || notification.type.charAt(0).toUpperCase() + notification.type.slice(1)}</div>
                    <div>${notification.message}</div>
                </div>
                <button onclick="notificationManager.remove('${notification.id}')" style="background: none; border: none; font-size: 16px; cursor: pointer; opacity: 0.7; padding: 0; margin-left: 10px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        // Click to dismiss
        element.addEventListener('click', () => {
            this.remove(notification.id);
        });

        container.appendChild(element);

        // Auto-remove after duration
        if (duration > 0) {
            setTimeout(() => {
                this.remove(notification.id);
            }, duration);
        }
    }

    remove(notificationId) {
        const element = document.querySelector(`#notification-${notificationId}`);
        if (element) {
            element.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => {
                element.remove();
            }, 300);
        }
        this.notifications = this.notifications.filter(n => n.id !== notificationId);
    }

    clear() {
        this.notifications.forEach(n => this.remove(n.id));
    }
}

// Rating System
class RatingSystem {
    static createStarRating(rating, maxRating = 5, interactive = false, onRate = null) {
        const container = document.createElement('div');
        container.className = 'star-rating';
        container.style.cssText = 'display: inline-flex; gap: 2px; align-items: center;';

        for (let i = 1; i <= maxRating; i++) {
            const star = document.createElement('i');
            star.className = i <= rating ? 'fas fa-star' : 'far fa-star';
            star.style.cssText = `
                color: ${i <= rating ? '#ffc107' : '#ccc'};
                font-size: 16px;
                cursor: ${interactive ? 'pointer' : 'default'};
                transition: color 0.2s ease;
            `;

            if (interactive && onRate) {
                star.addEventListener('click', () => onRate(i));
                star.addEventListener('mouseenter', () => {
                    for (let j = 1; j <= i; j++) {
                        container.children[j-1].style.color = '#ffc107';
                    }
                    for (let j = i + 1; j <= maxRating; j++) {
                        container.children[j-1].style.color = '#ccc';
                    }
                });
            }

            container.appendChild(star);
        }

        // Add rating text if not interactive
        if (!interactive && rating > 0) {
            const ratingText = document.createElement('span');
            ratingText.textContent = ` ${rating.toFixed(1)}`;
            ratingText.style.cssText = 'margin-left: 5px; color: #666; font-size: 14px;';
            container.appendChild(ratingText);
        }

        return container;
    }
}

// Enhanced Search System
class SearchManager {
    constructor() {
        this.savedSearches = Storage.get('savedSearches') || [];
        this.searchHistory = Storage.get('searchHistory') || [];
    }

    saveSearch(query, filters = {}) {
        const search = {
            id: generateId(),
            query,
            filters,
            timestamp: Date.now(),
            name: prompt('Save this search as:') || `Search: ${query}`
        };

        if (search.name) {
            this.savedSearches.unshift(search);
            this.savedSearches = this.savedSearches.slice(0, 10); // Keep only 10 saved searches
            Storage.set('savedSearches', this.savedSearches);
            notificationManager.show('Search saved successfully!', 'success');
        }
    }

    addToHistory(query) {
        if (query.trim()) {
            this.searchHistory = this.searchHistory.filter(h => h !== query);
            this.searchHistory.unshift(query);
            this.searchHistory = this.searchHistory.slice(0, 20); // Keep only 20 recent searches
            Storage.set('searchHistory', this.searchHistory);
        }
    }

    getSuggestions(query) {
        return this.searchHistory.filter(h => 
            h.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 5);
    }
}

// Price Alert System
class PriceAlertManager {
    constructor() {
        this.alerts = Storage.get('priceAlerts') || [];
    }

    addAlert(itemId, targetPrice, itemTitle) {
        const alert = {
            id: generateId(),
            itemId,
            targetPrice,
            itemTitle,
            created: Date.now(),
            active: true
        };

        this.alerts.push(alert);
        Storage.set('priceAlerts', this.alerts);
        
        notificationManager.show(
            `Price alert set for ${itemTitle} at ${formatCurrencySymbol(targetPrice)}`,
            'success',
            3000,
            { title: 'Price Alert Created' }
        );
    }

    checkAlerts(items) {
        this.alerts.filter(alert => alert.active).forEach(alert => {
            const item = items.find(i => i.id === alert.itemId);
            if (item && item.price <= alert.targetPrice) {
                notificationManager.show(
                    `${item.title} is now ${formatCurrencySymbol(item.price)} - below your target of ${formatCurrencySymbol(alert.targetPrice)}!`,
                    'success',
                    0, // Don't auto-dismiss
                    { title: '🎉 Price Alert!' }
                );
                alert.active = false;
                Storage.set('priceAlerts', this.alerts);
            }
        });
    }
}

// Enhanced Wishlist System
class WishlistManager {
    constructor() {
        this.wishlist = Storage.get('wishlist') || [];
    }

    add(item) {
        if (!this.isInWishlist(item.id)) {
            this.wishlist.push({
                ...item,
                addedDate: Date.now(),
                originalPrice: item.price
            });
            Storage.set('wishlist', this.wishlist);
            notificationManager.show('Added to wishlist!', 'success');
            return true;
        }
        return false;
    }

    remove(itemId) {
        this.wishlist = this.wishlist.filter(item => item.id !== itemId);
        Storage.set('wishlist', this.wishlist);
        notificationManager.show('Removed from wishlist', 'info');
    }

    isInWishlist(itemId) {
        return this.wishlist.some(item => item.id === itemId);
    }

    checkPriceDrops() {
        // This would typically check against current market prices
        // For demo, we'll simulate some price drops
        this.wishlist.forEach(item => {
            if (Math.random() < 0.1) { // 10% chance of price drop
                const newPrice = item.originalPrice * (0.8 + Math.random() * 0.15); // 15-20% discount
                if (newPrice < item.originalPrice) {
                    notificationManager.show(
                        `${item.title} price dropped to ${formatCurrencySymbol(newPrice)}!`,
                        'success',
                        0,
                        { title: '💰 Price Drop Alert!' }
                    );
                }
            }
        });
    }
}

// Message System
class MessageManager {
    constructor() {
        this.conversations = Storage.get('conversations') || [];
    }

    createConversation(participants, itemId = null) {
        const conversation = {
            id: generateId(),
            participants,
            itemId,
            messages: [],
            created: Date.now(),
            lastActivity: Date.now()
        };

        this.conversations.unshift(conversation);
        Storage.set('conversations', this.conversations);
        return conversation.id;
    }

    sendMessage(conversationId, senderId, message, type = 'text') {
        const conversation = this.conversations.find(c => c.id === conversationId);
        if (conversation) {
            const newMessage = {
                id: generateId(),
                senderId,
                message,
                type,
                timestamp: Date.now(),
                read: false
            };

            conversation.messages.push(newMessage);
            conversation.lastActivity = Date.now();
            Storage.set('conversations', this.conversations);

            // Notify other participants
            notificationManager.show('New message received', 'info');
            return newMessage.id;
        }
        return null;
    }

    markAsRead(conversationId, messageIds) {
        const conversation = this.conversations.find(c => c.id === conversationId);
        if (conversation) {
            conversation.messages.forEach(msg => {
                if (messageIds.includes(msg.id)) {
                    msg.read = true;
                }
            });
            Storage.set('conversations', this.conversations);
        }
    }

    getUnreadCount() {
        return this.conversations.reduce((total, conv) => {
            return total + conv.messages.filter(msg => !msg.read).length;
        }, 0);
    }
}

// Initialize enhanced functionality
let notificationManager, searchManager, priceAlertManager, wishlistManager, messageManager;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all managers
    notificationManager = new NotificationManager();
    searchManager = new SearchManager();
    priceAlertManager = new PriceAlertManager();
    wishlistManager = new WishlistManager();
    messageManager = new MessageManager();

    // Check for price drops and alerts periodically
    setInterval(() => {
        wishlistManager.checkPriceDrops();
        // priceAlertManager.checkAlerts would need current item data
    }, 300000); // Every 5 minutes

    // Update unread message count
    updateUnreadMessageCount();
});

// Helper function to update unread message count in navigation
function updateUnreadMessageCount() {
    const unreadCount = messageManager?.getUnreadCount() || 0;
    const messageNavItem = document.querySelector('#messagesTab');
    
    if (messageNavItem && unreadCount > 0) {
        messageNavItem.innerHTML = `
            <i class="fas fa-comments"></i> Messages 
            <span style="background: #e74c3c; color: white; border-radius: 50%; padding: 2px 6px; font-size: 11px; margin-left: 5px;">
                ${unreadCount}
            </span>
        `;
    }
}

// Add animation styles
if (!document.querySelector('#enhanced-animations')) {
    const style = document.createElement('style');
    style.id = 'enhanced-animations';
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .star-rating:hover .fas,
        .star-rating:hover .far {
            transform: scale(1.1);
        }
        
        .notification {
            transition: all 0.3s ease;
        }
        
        .notification:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2) !important;
        }
    `;
    document.head.appendChild(style);
}

// Export functions for use in other files (if using modules)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        formatCurrency,
        formatCurrencySymbol,
        formatDate,
        generateId,
        debounce,
        Storage,
        API,
        showNotification,
        NotificationManager,
        RatingSystem,
        SearchManager,
        PriceAlertManager,
        WishlistManager,
        MessageManager
    };
}
