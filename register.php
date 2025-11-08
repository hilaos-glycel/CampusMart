<?php
$pageTitle = 'Register';
require_once 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>

<main>
    <div class="form-container" style="max-width: 600px;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <i class="fas fa-graduation-cap" style="font-size: 3rem; color: #667eea; margin-bottom: 1rem;"></i>
            <h2>Student Registration</h2>
            <p style="color: #666;">Join the JH Cerilles State College Student Marketplace</p>
        </div>

        <div id="registerAlert"></div>

        <form id="registerForm" method="POST" action="api/register.php">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="student_id">Student ID *</label>
                    <input type="text" id="student_id" name="student_id" required 
                           placeholder="e.g., JH2024001" pattern="JH[0-9]{7}"
                           title="Student ID must be in format JH followed by 7 digits">
                </div>
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Choose a unique username" minlength="3" maxlength="20">
                </div>
            </div>

            <div class="form-group">
                <label for="email">JH Email Address *</label>
                <input type="email" id="email" name="email" required 
                       placeholder="your.name@jh.edu" pattern=".*@jh\.edu$"
                       title="Must be a valid JH Cerilles State College email address">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required 
                           placeholder="Enter your first name">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" required 
                           placeholder="Enter your last name">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="course">Course/Program *</label>
                    <select id="course" name="course" required>
                        <option value="">Select your course</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Business Administration">Business Administration</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Education">Education</option>
                        <option value="Nursing">Nursing</option>
                        <option value="Agriculture">Agriculture</option>
                        <option value="Arts and Sciences">Arts and Sciences</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="year_level">Year Level *</label>
                    <select id="year_level" name="year_level" required>
                        <option value="">Select year level</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                        <option value="Graduate">Graduate Student</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="bio">Bio (Optional)</label>
                <textarea id="bio" name="bio" rows="3" 
                          placeholder="Tell other students about yourself..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Create a strong password" minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="Confirm your password">
                </div>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: flex-start; gap: 0.5rem; font-weight: normal;">
                    <input type="checkbox" id="terms" name="terms" required style="margin-top: 0.25rem;">
                    <span>I agree to the <a href="terms.php" target="_blank" style="color: #667eea;">Terms of Service</a> and <a href="privacy.php" target="_blank" style="color: #667eea;">Privacy Policy</a> *</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;" id="registerBtn">
                <i class="fas fa-user-plus"></i> Register Account
            </button>
        </form>

        <div style="text-align: center;">
            <p style="color: #666; margin-bottom: 1rem;">Already have an account?</p>
            <a href="login.php" class="btn btn-secondary" style="width: 100%;">
                <i class="fas fa-sign-in-alt"></i> Login to Your Account
            </a>
        </div>

        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-top: 2rem;">
            <h4 style="color: #333; margin-bottom: 1rem;"><i class="fas fa-info-circle"></i> Registration Requirements</h4>
            <ul style="color: #666; margin: 0; padding-left: 1.5rem;">
                <li>Valid JH Cerilles State College student ID</li>
                <li>Official JH email address (@jh.edu)</li>
                <li>All required fields must be completed</li>
                <li>Password must be at least 6 characters</li>
                <li>Agreement to terms and conditions</li>
            </ul>
        </div>
    </div>
</main>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate passwords match
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        showAlert('Passwords do not match', 'error');
        return;
    }
    
    const formData = new FormData(this);
    const registerBtn = document.getElementById('registerBtn');
    const originalText = registerBtn.innerHTML;
    
    // Show loading state
    registerBtn.innerHTML = '<div class="loading"></div> Creating Account...';
    registerBtn.disabled = true;
    
    fetch('api/register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Registration successful! Redirecting to login...', 'success');
            setTimeout(() => {
                window.location.href = 'login.php?registered=1';
            }, 2000);
        } else {
            showAlert(data.message || 'Registration failed. Please try again.', 'error');
            resetRegisterButton();
        }
    })
    .catch(error => {
        console.error('Registration error:', error);
        showAlert('An error occurred. Please try again.', 'error');
        resetRegisterButton();
    });
    
    function resetRegisterButton() {
        registerBtn.innerHTML = originalText;
        registerBtn.disabled = false;
    }
});

function showAlert(message, type) {
    const alertDiv = document.getElementById('registerAlert');
    alertDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
    
    // Scroll to top to show alert
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Real-time validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('email').addEventListener('input', function() {
    const email = this.value;
    if (email && !email.endsWith('@jh.edu')) {
        this.setCustomValidity('Must be a JH Cerilles State College email address');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('student_id').addEventListener('input', function() {
    const studentId = this.value;
    const pattern = /^JH\d{7}$/;
    if (studentId && !pattern.test(studentId)) {
        this.setCustomValidity('Student ID must be in format JH followed by 7 digits (e.g., JH2024001)');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<style>
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

.alert {
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 1rem;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.loading {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<?php require_once 'includes/footer.php'; ?>
