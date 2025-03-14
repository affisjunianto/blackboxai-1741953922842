<?php
require_once __DIR__ . '/../functions/functions.php';

// Check if this is an API request or documentation view
$isApiRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isApiRequest) {
    // API Request Handler
    header('Content-Type: application/json');

    // Check for agent_id in request
    $agentId = $_GET['agent_id'] ?? null;
    if (!$agentId) {
        jsonResponse([
            'status' => 'error',
            'message' => 'Agent ID is required'
        ], 400);
    }

    // Validate agent and IP
    if (!validateAPIRequest($agentId, $_SERVER['REMOTE_ADDR'])) {
        // Log unauthorized attempt
        logAPIRequest(
            $agentId,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['REQUEST_URI'],
            $_SERVER['REQUEST_METHOD'],
            403,
            $_REQUEST,
            ['status' => 'error', 'message' => 'Unauthorized IP address']
        );

        jsonResponse([
            'status' => 'error',
            'message' => 'Unauthorized IP address'
        ], 403);
    }

    // Handle API endpoints
    $endpoint = $_GET['endpoint'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];

    try {
        switch ($endpoint) {
            case 'balance':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }

                if (!isset($_GET['user_id'])) {
                    throw new Exception('User ID is required', 400);
                }

                $userId = (int)$_GET['user_id'];
                $balance = getUserBalance($userId);

                if ($balance === false) {
                    throw new Exception('User not found', 404);
                }

                $response = [
                    'status' => 'success',
                    'data' => [
                        'user_id' => $userId,
                        'balance' => $balance
                    ]
                ];

                // Log successful request
                logAPIRequest(
                    $agentId,
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['REQUEST_URI'],
                    $method,
                    200,
                    $_REQUEST,
                    $response
                );

                jsonResponse($response);
                break;

            default:
                throw new Exception('Invalid endpoint', 404);
        }
    } catch (Exception $e) {
        $statusCode = $e->getCode() ?: 500;
        $response = [
            'status' => 'error',
            'message' => $e->getMessage()
        ];

        // Log failed request
        logAPIRequest(
            $agentId,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['REQUEST_URI'],
            $method,
            $statusCode,
            $_REQUEST,
            $response
        );

        jsonResponse($response, $statusCode);
    }
} else {
    // Documentation View
    requireLogin();
    
    $pageTitle = 'API Documentation';
    include_once __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../includes/navbar.php';
    include_once __DIR__ . '/../includes/sidebar.php';

    // Get agent's whitelisted IPs if user is an agent
    $whitelistedIPs = isAgent() ? getAgentWhitelistedIPs($_SESSION['user_id']) : [];
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">API Documentation</h1>
            <?php if (isAgent()): ?>
                <a href="/agent/manage_whitelist.php" class="btn btn-primary">
                    <i class="fas fa-shield-alt me-2"></i>Manage IP Whitelist
                </a>
            <?php endif; ?>
        </div>

        <!-- API Overview -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <h5 class="card-title">Overview</h5>
                <p>The Ampibet API provides programmatic access to user balances and transaction data. 
                   All API requests must include your agent ID and come from whitelisted IP addresses.</p>
                
                <h6 class="mt-4">Base URL</h6>
                <div class="bg-light p-3 rounded">
                    <code><?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']; ?>/api/</code>
                </div>

                <h6 class="mt-4">Authentication</h6>
                <p>API access requires:</p>
                <ul>
                    <li>Your agent ID included in each request as <code>agent_id</code> parameter</li>
                    <li>Requests must come from your whitelisted IP addresses (maximum 2 IPs allowed)</li>
                </ul>

                <?php if (isAgent()): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Your agent ID is: <strong><?php echo $_SESSION['user_id']; ?></strong>
                    </div>

                    <h6 class="mt-4">Your Whitelisted IPs</h6>
                    <?php if (empty($whitelistedIPs)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            You haven't whitelisted any IP addresses yet. 
                            <a href="/agent/manage_whitelist.php" class="alert-link">Add IP addresses</a> to start using the API.
                        </div>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($whitelistedIPs as $ip): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($ip['ip_address']); ?>
                                    <?php if ($ip['description']): ?>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($ip['description']); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>

                <h6 class="mt-4">Response Format</h6>
                <p>All API responses are in JSON format with the following structure:</p>
                <pre class="bg-light p-3 rounded"><code>{
    "status": "success|error",
    "data": { ... }  // For successful requests
    "message": "..." // For error responses
}</code></pre>
            </div>
        </div>

        <!-- Endpoints Documentation -->
        <div class="card custom-card">
            <div class="card-body">
                <h5 class="card-title">Endpoints</h5>

                <!-- Get Balance Endpoint -->
                <div class="endpoint-doc mb-4">
                    <h6 class="text-primary">Get User Balance</h6>
                    <p>Retrieve the current balance for a specific user.</p>

                    <div class="mb-3">
                        <strong>Endpoint:</strong>
                        <div class="bg-light p-2 rounded">
                            <code>GET /api/?endpoint=balance&agent_id={your_agent_id}&user_id={user_id}</code>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Parameters:</strong>
                        <ul class="list-unstyled">
                            <li><code>agent_id</code> (required) - Your agent ID</li>
                            <li><code>user_id</code> (required) - The ID of the user to check</li>
                        </ul>
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

                    <?php if (isAgent()): ?>
                        <!-- Test API Form -->
                        <div class="card mt-3">
                            <div class="card-body">
                                <h6>Test API Call</h6>
                                <form id="testBalanceApi" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="testUserId" class="form-label">User ID</label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="testUserId" 
                                               required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        Test API Call
                                    </button>
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
</div>

<?php if (isAgent()): ?>
<script>
$(document).ready(function() {
    // Test API Call
    $('#testBalanceApi').on('submit', function(e) {
        e.preventDefault();
        
        const userId = $('#testUserId').val();
        const responseDiv = $('#apiResponse');
        const responsePre = responseDiv.find('code');
        
        $.ajax({
            url: '/api/',
            method: 'GET',
            data: {
                endpoint: 'balance',
                agent_id: <?php echo $_SESSION['user_id']; ?>,
                user_id: userId
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
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
}
?>