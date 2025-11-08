<?php
$pageTitle = 'Test Messaging System';
require_once 'includes/header.php';

// Redirect if not logged in
if (!$isLoggedIn) {
    header('Location: login.php');
    exit;
}
?>

<main style="padding: 2rem 0; background: #f8fafc; min-height: calc(100vh - 80px);">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 2rem;">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h1 style="color: #1e293b; margin-bottom: 1rem; font-size: 2.5rem; font-weight: 700;">
                💬 CampusMart Messaging System
            </h1>
            <p style="color: #64748b; font-size: 1.2rem; max-width: 600px; margin: 0 auto;">
                Complete real-time messaging system for student communication
            </p>
        </div>

        <!-- Feature Overview -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
            <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="fas fa-comments" style="color: white; font-size: 20px;"></i>
                </div>
                <h3 style="color: #1e293b; margin-bottom: 0.5rem; font-size: 1.25rem; font-weight: 600;">Real-time Messaging</h3>
                <p style="color: #64748b; line-height: 1.6; margin: 0;">Send and receive messages instantly with live updates and read receipts.</p>
            </div>

            <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="fas fa-search" style="color: white; font-size: 20px;"></i>
                </div>
                <h3 style="color: #1e293b; margin-bottom: 0.5rem; font-size: 1.25rem; font-weight: 600;">User Search</h3>
                <p style="color: #64748b; line-height: 1.6; margin: 0;">Find and start conversations with any student in the CampusMart community.</p>
            </div>

            <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="fas fa-bell" style="color: white; font-size: 20px;"></i>
                </div>
                <h3 style="color: #1e293b; margin-bottom: 0.5rem; font-size: 1.25rem; font-weight: 600;">Smart Notifications</h3>
                <p style="color: #64748b; line-height: 1.6; margin: 0;">Get notified about new messages with unread counters and priority alerts.</p>
            </div>
        </div>

        <!-- API Testing Section -->
        <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; margin-bottom: 3rem;">
            <h2 style="color: #1e293b; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">🧪 API Testing Dashboard</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <button onclick="testAPI('conversations')" class="test-btn" style="background: #10b981; color: white; border: none; padding: 1rem; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                    <i class="fas fa-list"></i> Test Get Conversations
                </button>
                
                <button onclick="testAPI('users')" class="test-btn" style="background: #3b82f6; color: white; border: none; padding: 1rem; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                    <i class="fas fa-users"></i> Test Search Users
                </button>
                
                <button onclick="testAPI('send')" class="test-btn" style="background: #8b5cf6; color: white; border: none; padding: 1rem; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                    <i class="fas fa-paper-plane"></i> Test Send Message
                </button>
                
                <button onclick="clearResults()" class="test-btn" style="background: #6b7280; color: white; border: none; padding: 1rem; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                    <i class="fas fa-trash"></i> Clear Results
                </button>
            </div>
            
            <div id="testResults" style="background: #1f2937; color: #f9fafb; padding: 1.5rem; border-radius: 8px; font-family: 'Courier New', monospace; font-size: 0.875rem; max-height: 400px; overflow-y: auto; display: none;">
                <div id="testOutput"></div>
            </div>
        </div>

        <!-- Current User Info -->
        <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; margin-bottom: 3rem;">
            <h2 style="color: #1e293b; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">👤 Current User Information</h2>
            
            <div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #10b981;">
                <p style="margin: 0 0 0.5rem 0;"><strong>Name:</strong> <?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></p>
                <p style="margin: 0 0 0.5rem 0;"><strong>Email:</strong> <?php echo htmlspecialchars($currentUser['email']); ?></p>
                <p style="margin: 0;"><strong>User ID:</strong> <?php echo $currentUser['id']; ?></p>
            </div>
        </div>

        <!-- Test Accounts -->
        <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; margin-bottom: 3rem;">
            <h2 style="color: #1e293b; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">🔑 Test Accounts</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <div style="background: #f0fdf4; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #10b981;">
                    <h4 style="color: #15803d; margin: 0 0 0.5rem 0;">Maria Hilaos</h4>
                    <p style="color: #15803d; margin: 0; font-size: 0.875rem;">Username: hilaos<br>Password: hilaos123<br>Computer Science Student</p>
                </div>
                
                <div style="background: #eff6ff; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #3b82f6;">
                    <h4 style="color: #1d4ed8; margin: 0 0 0.5rem 0;">John Sapuay</h4>
                    <p style="color: #1d4ed8; margin: 0; font-size: 0.875rem;">Username: sapuay<br>Password: sapuay123<br>Business Administration Student</p>
                </div>
                
                <div style="background: #fef3c7; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #f59e0b;">
                    <h4 style="color: #92400e; margin: 0 0 0.5rem 0;">Anna Legaspi</h4>
                    <p style="color: #92400e; margin: 0; font-size: 0.875rem;">Username: legaspi<br>Password: legaspi123<br>Engineering Student</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
            <h2 style="color: #1e293b; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">🚀 Quick Actions</h2>
            
            <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                <a href="messages.php" style="background: linear-gradient(135deg, #10b981, #059669); color: white; text-decoration: none; padding: 1rem 2rem; border-radius: 8px; font-weight: 600; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-comments"></i>
                    Open Messages
                </a>
                
                <a href="setup_messaging.php" style="background: #6b7280; color: white; text-decoration: none; padding: 1rem 2rem; border-radius: 8px; font-weight: 600; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-cog"></i>
                    Setup Messaging
                </a>
                
                <a href="dashboard.php" style="background: #3b82f6; color: white; text-decoration: none; padding: 1rem 2rem; border-radius: 8px; font-weight: 600; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                
                <a href="marketplace.php" style="background: #8b5cf6; color: white; text-decoration: none; padding: 1rem 2rem; border-radius: 8px; font-weight: 600; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-store"></i>
                    Marketplace
                </a>
            </div>
        </div>
    </div>
</main>

<style>
.test-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

a:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

#testResults {
    transition: all 0.3s ease;
}

#testResults.show {
    display: block !important;
}
</style>

<script>
let testCounter = 0;

async function testAPI(type) {
    const testResults = document.getElementById('testResults');
    const testOutput = document.getElementById('testOutput');
    
    testResults.style.display = 'block';
    testResults.classList.add('show');
    
    testCounter++;
    const timestamp = new Date().toLocaleTimeString();
    
    addOutput(`\n=== Test ${testCounter}: ${type.toUpperCase()} API (${timestamp}) ===`);
    
    try {
        switch(type) {
            case 'conversations':
                await testConversations();
                break;
            case 'users':
                await testSearchUsers();
                break;
            case 'send':
                await testSendMessage();
                break;
        }
    } catch (error) {
        addOutput(`❌ ERROR: ${error.message}`);
    }
}

async function testConversations() {
    addOutput('📡 Fetching conversations...');
    
    const response = await fetch(`${window.siteUrl}/api/get_conversations.php`);
    const data = await response.json();
    
    addOutput(`📊 Response Status: ${response.status}`);
    addOutput(`📋 Response Data:`);
    addOutput(JSON.stringify(data, null, 2));
    
    if (data.success) {
        addOutput(`✅ SUCCESS: Found ${data.conversations.length} conversations`);
        addOutput(`📬 Total unread messages: ${data.total_unread}`);
    } else {
        addOutput(`❌ FAILED: ${data.message}`);
    }
}

async function testSearchUsers() {
    addOutput('🔍 Searching for users with query "a"...');
    
    const response = await fetch(`${window.siteUrl}/api/search_users.php?q=a`);
    const data = await response.json();
    
    addOutput(`📊 Response Status: ${response.status}`);
    addOutput(`📋 Response Data:`);
    addOutput(JSON.stringify(data, null, 2));
    
    if (data.success) {
        addOutput(`✅ SUCCESS: Found ${data.users.length} users`);
        data.users.forEach(user => {
            addOutput(`   👤 ${user.name} (${user.email})`);
        });
    } else {
        addOutput(`❌ FAILED: ${data.message}`);
    }
}

async function testSendMessage() {
    addOutput('💌 Testing send message functionality...');
    
    // First, search for a user to send message to
    const searchResponse = await fetch(`${window.siteUrl}/api/search_users.php?q=a`);
    const searchData = await searchResponse.json();
    
    if (!searchData.success || searchData.users.length === 0) {
        addOutput('❌ No users found to send message to');
        return;
    }
    
    const targetUser = searchData.users[0];
    addOutput(`📤 Sending test message to: ${targetUser.name}`);
    
    const messageData = {
        receiver_id: targetUser.id,
        message: `Test message from messaging system - ${new Date().toLocaleString()}`
    };
    
    const response = await fetch(`${window.siteUrl}/api/send_message.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(messageData)
    });
    
    const data = await response.json();
    
    addOutput(`📊 Response Status: ${response.status}`);
    addOutput(`📋 Response Data:`);
    addOutput(JSON.stringify(data, null, 2));
    
    if (data.success) {
        addOutput(`✅ SUCCESS: Message sent successfully!`);
        addOutput(`📨 Message ID: ${data.message.id}`);
        addOutput(`👤 Sent to: ${targetUser.name}`);
    } else {
        addOutput(`❌ FAILED: ${data.message}`);
    }
}

function addOutput(text) {
    const testOutput = document.getElementById('testOutput');
    testOutput.innerHTML += text + '\n';
    testOutput.scrollTop = testOutput.scrollHeight;
}

function clearResults() {
    const testOutput = document.getElementById('testOutput');
    const testResults = document.getElementById('testResults');
    
    testOutput.innerHTML = '';
    testResults.style.display = 'none';
    testResults.classList.remove('show');
    testCounter = 0;
}

// Auto-scroll test results
function scrollToBottom() {
    const testOutput = document.getElementById('testOutput');
    if (testOutput) {
        testOutput.scrollTop = testOutput.scrollHeight;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('🧪 Messaging Test Page Loaded');
    console.log('👤 Current User:', window.currentUser);
});
</script>

<?php require_once 'includes/footer.php'; ?>
