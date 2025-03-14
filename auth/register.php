<?php
require_once __DIR__ . '/../functions/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/dashboard/' . (isAdmin() ? 'admin' : 'agent') . '-dashboard.php');
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long.";
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }
    
    if (empty($errors)) {
        // Try to create the user
        if (createUser($username, $password, 'agent', $email)) {
            setFlashMessage('success', 'Registration successful! Please login.');
            redirect('/auth/login.php');
        } else {
            setFlashMessage('danger', 'Username or email already exists.');
        }
    } else {
        setFlashMessage('danger', implode('<br>', $errors));
    }
}

$pageTitle = 'Register';
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                        <h2 class="font-weight-bold">Create Account</h2>
                        <p class="text-muted">Join Ampibet today</p>
                    </div>

                    <form method="POST" action="" class="needs-validation" novalidate>
                        <!-- Username field -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                       required 
                                       minlength="3">
                                <div class="invalid-feedback">
                                    Please choose a username (minimum 3 characters).
                                </div>
                            </div>
                        </div>

                        <!-- Email field -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                        </div>

                        <!-- Password field -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       required 
                                       minlength="6">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">
                                    Password must be at least 6 characters long.
                                </div>
                            </div>
                        </div>

                        <!-- Confirm Password field -->
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       required>
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">
                                    Please confirm your password.
                                </div>
                            </div>
                        </div>

                        <!-- Terms checkbox -->
                        <div class="mb-4 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="terms" 
                                   required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="text-primary">Terms of Service</a>
                            </label>
                            <div class="invalid-feedback">
                                You must agree to the terms of service.
                            </div>
                        </div>

                        <!-- Submit button -->
                        <button type="submit" class="btn btn-primary w-100 mb-4">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>

                        <!-- Login link -->
                        <div class="text-center">
                            <p class="mb-0">Already have an account? 
                                <a href="login.php" class="text-primary">Login</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePasswordVisibility(inputId, buttonId) {
    const input = document.getElementById(inputId);
    const button = document.getElementById(buttonId);
    const icon = button.querySelector('i');
    
    button.addEventListener('click', function() {
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
}

// Initialize password toggles
togglePasswordVisibility('password', 'togglePassword');
togglePasswordVisibility('confirm_password', 'toggleConfirmPassword');

// Password match validation
document.getElementById('confirm_password').addEventListener('input', function() {
    if (this.value !== document.getElementById('password').value) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>