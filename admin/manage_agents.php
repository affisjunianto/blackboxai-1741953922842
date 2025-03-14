<?php
require_once __DIR__ . '/../functions/functions.php';

// Ensure user is admin
requireAdmin();

$pageTitle = 'Manage Agents';
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../includes/navbar.php';
include_once __DIR__ . '/../includes/sidebar.php';

$pdo = getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('danger', 'Invalid token. Please try again.');
        redirect('/admin/manage_agents.php');
    }

    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'create':
                $username = sanitizeInput($_POST['username']);
                $email = sanitizeInput($_POST['email']);
                $password = $_POST['password'];

                // Validate input
                if (empty($username) || empty($email) || empty($password)) {
                    throw new Exception('All fields are required.');
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Invalid email format.');
                }

                if (strlen($password) < 6) {
                    throw new Exception('Password must be at least 6 characters long.');
                }

                // Create agent
                if (createUser($username, $password, 'agent', $email)) {
                    setFlashMessage('success', 'Agent created successfully.');
                } else {
                    throw new Exception('Username or email already exists.');
                }
                break;

            case 'update':
                $agentId = (int)$_POST['agent_id'];
                $email = sanitizeInput($_POST['email']);
                $newPassword = $_POST['new_password'];

                $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ? AND role = 'agent'");
                $stmt->execute([$email, $agentId]);

                if (!empty($newPassword)) {
                    if (strlen($newPassword) < 6) {
                        throw new Exception('Password must be at least 6 characters long.');
                    }
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'agent'");
                    $stmt->execute([$hashedPassword, $agentId]);
                }

                setFlashMessage('success', 'Agent updated successfully.');
                break;

            case 'delete':
                $agentId = (int)$_POST['agent_id'];

                // Check if agent has sub-agents
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE parent_id = ?");
                $stmt->execute([$agentId]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('Cannot delete agent with existing sub-agents.');
                }

                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'agent'");
                $stmt->execute([$agentId]);

                setFlashMessage('success', 'Agent deleted successfully.');
                break;
        }
    } catch (Exception $e) {
        setFlashMessage('danger', 'Error: ' . $e->getMessage());
    }

    redirect('/admin/manage_agents.php');
}

// Fetch all agents
try {
    $stmt = $pdo->query("
        SELECT u.*, 
               COUNT(s.id) as sub_agent_count,
               SUM(s.balance) as total_sub_balance
        FROM users u
        LEFT JOIN users s ON s.parent_id = u.id
        WHERE u.role = 'agent'
        GROUP BY u.id
        ORDER BY u.username
    ");
    $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching agents: " . $e->getMessage());
    $agents = [];
    setFlashMessage('danger', 'Error loading agent data');
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Manage Agents</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAgentModal">
                <i class="fas fa-user-plus me-2"></i>Create New Agent
            </button>
        </div>

        <!-- Agents Table -->
        <div class="card custom-card">
            <div class="card-body">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Balance</th>
                            <th>Sub-Agents</th>
                            <th>Sub-Agents Balance</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agents as $agent): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($agent['username']); ?></td>
                                <td><?php echo htmlspecialchars($agent['email']); ?></td>
                                <td><?php echo number_format($agent['balance']); ?></td>
                                <td><?php echo number_format($agent['sub_agent_count']); ?></td>
                                <td><?php echo number_format($agent['total_sub_balance'] ?? 0); ?></td>
                                <td><?php echo date('d M Y', strtotime($agent['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" 
                                                class="btn btn-sm btn-info edit-agent"
                                                data-agent-id="<?php echo $agent['id']; ?>"
                                                data-username="<?php echo htmlspecialchars($agent['username']); ?>"
                                                data-email="<?php echo htmlspecialchars($agent['email']); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger delete-agent"
                                                data-agent-id="<?php echo $agent['id']; ?>"
                                                data-username="<?php echo htmlspecialchars($agent['username']); ?>"
                                                <?php echo $agent['sub_agent_count'] > 0 ? 'disabled' : ''; ?>>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Agent Modal -->
<div class="modal fade" id="createAgentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createAgentForm" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="create">

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               required 
                               minlength="3">
                        <div class="invalid-feedback">
                            Username must be at least 3 characters long.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               required>
                        <div class="invalid-feedback">
                            Please enter a valid email address.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   minlength="6">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">
                            Password must be at least 6 characters long.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="createAgentForm" class="btn btn-primary">Create Agent</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Agent Modal -->
<div class="modal fade" id="editAgentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editAgentForm" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="agent_id" id="editAgentId">

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" id="editUsername" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" 
                               class="form-control" 
                               id="editEmail" 
                               name="email" 
                               required>
                        <div class="invalid-feedback">
                            Please enter a valid email address.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password (leave blank to keep current)</label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control" 
                                   id="newPassword" 
                                   name="new_password" 
                                   minlength="6">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    onclick="togglePassword('newPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">
                            Password must be at least 6 characters long.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editAgentForm" class="btn btn-primary">Update Agent</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Agent Modal -->
<div class="modal fade" id="deleteAgentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the agent <strong id="deleteAgentName"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="agent_id" id="deleteAgentId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Agent</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('.datatable').DataTable({
        order: [[0, 'asc']]
    });
    
    // Edit Agent
    $('.edit-agent').click(function() {
        const agentId = $(this).data('agent-id');
        const username = $(this).data('username');
        const email = $(this).data('email');
        
        $('#editAgentId').val(agentId);
        $('#editUsername').val(username);
        $('#editEmail').val(email);
        $('#newPassword').val('');
        
        new bootstrap.Modal(document.getElementById('editAgentModal')).show();
    });
    
    // Delete Agent
    $('.delete-agent').click(function() {
        const agentId = $(this).data('agent-id');
        const username = $(this).data('username');
        
        $('#deleteAgentId').val(agentId);
        $('#deleteAgentName').text(username);
        
        new bootstrap.Modal(document.getElementById('deleteAgentModal')).show();
    });
});

// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>