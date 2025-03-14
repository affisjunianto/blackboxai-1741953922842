<?php
require_once __DIR__ . '/functions/functions.php';

// If user is logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    redirect('/dashboard/' . (isAdmin() ? 'admin' : 'agent') . '-dashboard.php');
}

$pageTitle = 'Welcome to Ampibet';
include_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="row min-vh-100 align-items-center">
        <div class="col-md-6 text-center text-md-start">
            <h1 class="display-4 fw-bold mb-4">Welcome to Ampibet</h1>
            <p class="lead mb-4">
                A comprehensive platform for managing agents and sub-agents with an integrated balance system.
            </p>
            <div class="d-grid gap-3 d-md-flex justify-content-md-start">
                <a href="/auth/login.php" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
                <a href="/auth/register.php" class="btn btn-outline-primary btn-lg px-4">
                    <i class="fas fa-user-plus me-2"></i>Register
                </a>
            </div>
        </div>
        <div class="col-md-6 d-none d-md-block">
            <div class="text-center">
                <i class="fas fa-dice fa-10x text-primary mb-4"></i>
                <div class="row mt-5">
                    <div class="col-6">
                        <div class="card custom-card mb-4">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Agent Management</h5>
                                <p class="card-text">Efficiently manage your agents and sub-agents</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card custom-card mb-4">
                            <div class="card-body text-center">
                                <i class="fas fa-coins fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Balance System</h5>
                                <p class="card-text">Secure and reliable balance management</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card custom-card">
                            <div class="card-body text-center">
                                <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Secure Access</h5>
                                <p class="card-text">Role-based access control system</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card custom-card">
                            <div class="card-body text-center">
                                <i class="fas fa-code fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">API Support</h5>
                                <p class="card-text">Integration ready with API access</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="container py-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card custom-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user-shield fa-4x text-primary mb-3"></i>
                    <h3 class="card-title">Secure Authentication</h3>
                    <p class="card-text">
                        Advanced security measures including password hashing and role-based access control 
                        ensure your data remains protected.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card custom-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-wallet fa-4x text-primary mb-3"></i>
                    <h3 class="card-title">Balance Management</h3>
                    <p class="card-text">
                        Efficient coin-based balance system with automatic conversion rates and 
                        comprehensive transaction tracking.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card custom-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users-cog fa-4x text-primary mb-3"></i>
                    <h3 class="card-title">Agent Hierarchy</h3>
                    <p class="card-text">
                        Structured agent and sub-agent management system with clear hierarchies 
                        and delegated responsibilities.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="bg-light py-5">
    <div class="container text-center">
        <h2 class="mb-4">Ready to Get Started?</h2>
        <p class="lead mb-4">Join our platform today and experience efficient agent management.</p>
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
            <a href="/auth/register.php" class="btn btn-primary btn-lg px-4 gap-3">
                <i class="fas fa-user-plus me-2"></i>Create Account
            </a>
            <a href="/auth/login.php" class="btn btn-outline-secondary btn-lg px-4">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In
            </a>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>