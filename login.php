<?php
$pageTitle = 'Login';
require_once 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// Check for timeout parameter
if (isset($_GET['timeout'])) {
    $error = 'Your session has expired. Please log in again.';
}

// Check for registration success
if (isset($_GET['registered'])) {
    $success = 'Registration successful! You can now log in.';
}
?>

<main>
    <div class="form-container">
        <div style="text-align: center; margin-bottom: 2rem;">
            <i class="fas fa-graduation-cap" style="font-size: 3rem; color: #667eea; margin-bottom: 1rem;"></i>
            <h2>Student Login</h2>
            <p style="color: #666;">Sign in to access the JH Cerilles State College Student Marketplace</p>
        </div>

        <div id="loginAlert">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
        </div>

        <form id="loginForm" method="POST" action="api/login.php">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="loginId">Student ID, Username, or Email</label>
                <input type="text" id="loginId" name="loginId" required 
                       placeholder="Enter your Student ID, Username, or Email"
                       value="<?php echo isset($_POST['loginId']) ? htmlspecialchars($_POST['loginId']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Enter your password">
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal;">
                    <input type="checkbox" id="rememberMe" name="rememberMe">
                    Remember me
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div style="text-align: center;">
            <p style="color: #666; margin-bottom: 1rem;">Don't have an account?</p>
            <a href="register.php" class="btn btn-secondary" style="width: 100%;">
                <i class="fas fa-user-plus"></i> Register as JH Student
            </a>
        </div>

        <div style="text-align: center; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e1e5e9;">
            <a href="forgot-password.php" style="color: #667eea; text-decoration: none;">
                <i class="fas fa-key"></i> Forgot Password?
            </a>
        </div>
    </div>
</main>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const loginBtn = document.getElementById('loginBtn');
    const originalText = loginBtn.innerHTML;
    
    // Show loading state
    loginBtn.innerHTML = '<div class="loading"></div> Logging in...';
    loginBtn.disabled = true;
    
    fetch('api/login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Login successful! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 1500);
        } else {
            showAlert(data.message || 'Login failed. Please try again.', 'error');
            resetLoginButton();
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        showAlert('An error occurred. Please try again.', 'error');
        resetLoginButton();
    });
    
    function resetLoginButton() {
        loginBtn.innerHTML = originalText;
        loginBtn.disabled = false;
    }
});

function showAlert(message, type) {
    const alertDiv = document.getElementById('loginAlert');
    alertDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
    
    // Auto-hide success messages
    if (type === 'success') {
        setTimeout(() => {
            alertDiv.innerHTML = '';
        }, 3000);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
