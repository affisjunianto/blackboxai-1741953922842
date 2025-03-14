<?php
require_once __DIR__ . '/../functions/functions.php';

// Ensure user is agent
requireAgent();

$pageTitle = 'Manage Sub-Agents';
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../includes/navbar.php';
include_once __DIR__ . '/../includes/sidebar.php';

$pdo = getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('danger', 'Invalid token. Please try again.');
        redirect('/agent/manage_subagents.php');
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

                // Create sub-agent with current agent as parent
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password, role, parent_id) 
                    VALUES (?, ?, ?, 'sub_agent', ?)
                ");
                
                if ($stmt->execute([
                    $username, 
                    $email, 
                    password_hash($password, PASSWORD_DEFAULT),
                    $_SESSION['user_id']
                ])) {
                    setFlashMessage('success', 'Sub-agent created successfully.');
                } else {
                    throw new Exception('Username or email already exists.');
                }
                break;

            case 'update':
                $subAgentId = (int)$_POST['sub_agent_id'];
                $email = sanitizeInput($_POST['email']);
                $newPassword = $_POST['new_password'];

                // Verify ownership
                $stmt = $pdo->prepare("
                    SELECT id FROM users 
                    WHERE id = ? AND parent_id = ? AND role = 'sub_agent'
                ");
                $stmt->execute([$subAgentId, $_SESSION['user_id']]);
                if (!$stmt->fetch()) {
                    throw new Exception('Invalid sub-agent.');
                }

                // Update email
                $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmt->execute([$email, $subAgentId]);

                // Update password if provided
                if (!empty($newPassword)) {
                    if (strlen($newPassword) < 6) {
                        throw new Exception('Password must be at least 6 characters long.');
                    }
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashedPassword, $subAgentId]);
                }

                setFlashMessage('success', 'Sub-agent updated successfully.');
                break;

            case 'delete':
                $subAgentId = (int)$_POST['sub_agent_id'];
                
                // Verify ownership before deletion
                $stmt = $pdo->prepare("
                    DELETE FROM users 
                    WHERE id = ? AND parent_id = ? AND role = 'sub_agent'
                ");
                $stmt->execute([$subAgentId, $_SESSION['user_id']]);

                setFlashMessage('success', 'Sub-agent deleted successfully.');
                break;
        }
    } catch (Exception $e) {
        setFlashMessage('danger', 'Error: ' . $e->getMessage());
    }

    redirect('/agent/manage_subagents.php');
}

// Fetch agent's sub-agents
try {
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE parent_id = ? AND role = 'sub_agent' 
        ORDER BY username
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $subAgents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching sub-agents: " . $e->getMessage());
    $subAgents = [];
    setFlashMessage('danger', 'Error loading sub-agent data');
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Manage Sub-Agents</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSubAgentModal">
                <i class="fas fa-user-plus me-2"></i>Create New Sub-Agent
            </button>
        </div>

        <!-- Sub-Agents Table -->
        <div class="card custom-card">
            <div class="card-body">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Balance</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subAgents as $subAgent): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($subAgent['username']); ?></td>
                                <td><?php echo htmlspecialchars($subAgent['email']); ?></td>
                                <td><?php echo number_format($subAgent['balance']); ?></td>
                                <td><?php echo date('d M Y', strtotime($subAgent['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" 
                                                class="btn btn-sm btn-info edit-subagent"
                                                data-subagent-id="<?php echo $subAgent['id']; ?>"
                                                data-username="<?php echo htmlspecialchars($subAgent['username']); ?>"
                                                data-email="<?php echo htmlspecialchars($subAgent['email']); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger delete-subagent"
                                                data-subagent-id="<?php echo $subAgent['id']; ?>"
                                                data-username="<?php echo htmlspecialchars($subAgent['username']); ?>">
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

<!-- Create Sub-Agent Modal -->
<div class="modal fade" id="createSubAgentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Sub-Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createSubAgentForm" method="POST" class="needs-validation" novalidate>
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
                <button type="submit" form="createSubAgentForm" class="btn btn-primary">Create Sub-Agent</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Sub-Agent Modal -->
<div class="modal fade" id="editSubAgentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Sub-Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editSubAgentForm" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="sub_agent_id" id="editSubAgentId">

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
                <button type="submit" form="editSubAgentForm" class="btn btn-primary">Update Sub-Agent</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Sub-Agent Modal -->
<div class="modal fade" id="deleteSubAgentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Sub-Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the sub-agent <strong id="deleteSubAgentName"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="sub_agent_id" id="deleteSubAgentId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Sub-Agent</button>
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
    
    // Edit Sub-Agent
    $('.edit-subagent').click(function() {
        const subAgentId = $(this).data('subagent-id');
        const username = $(this).data('username');
        const email = $(this).data('email');
        
        $('#editSubAgentId').val(subAgentId);
        $('#editUsername').val(username);
        $('#editEmail').val(email);
        $('#newPassword').val('');
        
        new bootstrap.Modal(document.getElementById('editSubAgentModal')).show();
    });
    
    // Delete Sub-Agent
    $('.delete-subagent').click(function() {
        const subAgentId = $(this).data('subagent-id');
        const username = $(this).data('username');
        
        $('#deleteSubAgentId').val(subAgentId);
        $('#deleteSubAgentName').text(username);
        
        new bootstrap.Modal(document.getElementById('deleteSubAgentModal')).show();
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