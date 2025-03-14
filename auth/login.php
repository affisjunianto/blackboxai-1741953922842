<?php
require_once __DIR__ . '/../functions/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/dashboard/' . (isAdmin() ? 'admin' : 'agent') . '-dashboard.php');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        setFlashMessage('danger', 'Please fill in all fields.');
    } else {
        $user = validateUser($username, $password);
        
        if ($user) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect based on role
            $redirectPath = '/dashboard/' . ($user['role'] === 'admin' ? 'admin' : 'agent') . '-dashboard.php';
            redirect($redirectPath);
        } else {
            setFlashMessage('danger', 'Invalid username or password.');
        }
    }
}

$pageTitle = 'Login';
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-dice fa-3x text-primary mb-3"></i>
                        <h2 class="font-weight-bold">Ampibet</h2>
                        <p class="text-muted">Sign in to your account</p>
                    </div>

                    <form method="POST" action="" class="needs-validation" novalidate>
                        <!-- Username field -->
                        <div class="mb-4">
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
                                       required>
                                <div class="invalid-feedback">
                                    Please enter your username.
                                </div>
                            </div>
                        </div>

                        <!-- Password field -->
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       required>
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">
                                    Please enter your password.
                                </div>
                            </div>
                        </div>

                        <!-- Remember me checkbox -->
                        <div class="mb-4 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="remember" 
                                   name="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>

                        <!-- Submit button -->
                        <button type="submit" class="btn btn-primary w-100 mb-4">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>

                        <!-- Register link -->
                        <div class="text-center">
                            <p class="mb-0">Don't have an account? 
                                <a href="register.php" class="text-primary">Register</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>