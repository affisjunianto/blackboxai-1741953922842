<?php
require_once __DIR__ . '/../functions/functions.php';

// Documentation View
requireLogin();

$pageTitle = 'API Documentation';
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../includes/navbar.php';
include_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="card custom-card mb-4">
            <div class="card-body">
                <h5 class="card-title">API Documentation</h5>
                
                <h6 class="mt-4">Authentication</h6>
                <p>API access requires your username and password in the request body.</p>
                
                <h6 class="mt-4">Check Balance API</h6>
                <p>Endpoint to check user balance. Agents can only check their own balance.</p>
                
                <div class="mb-3">
                    <strong>Endpoint:</strong>
                    <div class="bg-light p-2 rounded">
                        <code>POST /api/balance.php</code>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Request Body (JSON):</strong>
                    <pre class="bg-light p-3 rounded"><code>{
    "username": "your_username",
    "password": "your_password",
    "user_id": 123  // Optional for admin, ignored for agents
}</code></pre>
                </div>

                <div class="mb-3">
                    <strong>Example Response:</strong>
                    <pre class="bg-light p-3 rounded"><code>{
    "status": "success",
    "data": {
        "user_id": 123,
        "balance": 650000
    }
}</code></pre>
                </div>

                <?php if (isAgent() || isAdmin()): ?>
                    <!-- Test API Form -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h6>Test API Call</h6>
                            <form id="testBalanceApi" class="needs-validation" novalidate>
                                <?php if (isAdmin()): ?>
                                    <div class="mb-3">
                                        <label for="testUserId" class="form-label">User ID (Optional)</label>
                                        <input type="number" class="form-control" id="testUserId">
                                    </div>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-primary">Test API Call</button>
                            </form>
                            <div id="apiResponse" class="mt-3" style="display: none;">
                                <h6>Response:</h6>
                                <pre class="bg-light p-3 rounded"><code></code></pre>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (isAgent() || isAdmin()): ?>
<script>
$(document).ready(function() {
    $('#testBalanceApi').on('submit', function(e) {
        e.preventDefault();
        
        const userId = $('#testUserId').val();
        const responseDiv = $('#apiResponse');
        const responsePre = responseDiv.find('code');
        
        const password = prompt('Enter your password:');
        if (!password) return;

        $.ajax({
            url: '/api/balance.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                username: '<?php echo $_SESSION['username']; ?>',
                password: password,
                user_id: userId || undefined
            }),
            success: function(response) {
                responsePre.text(JSON.stringify(response, null, 4));
                responseDiv.show();
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || errorMessage;
                } catch (e) {}
                
                responsePre.text(JSON.stringify({
                    status: 'error',
                    message: errorMessage
                }, null, 4));
                responseDiv.show();
            }
        });
    });
});
</script>
<?php endif; ?>

<?php 
include_once __DIR__ . '/../includes/footer.php';
?>