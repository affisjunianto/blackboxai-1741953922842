<?php
require_once __DIR__ . '/../functions/functions.php';

// Ensure user is admin
requireAdmin();

$pageTitle = 'Manage Balance';
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../includes/navbar.php';
include_once __DIR__ . '/../includes/sidebar.php';

// Handle balance update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('danger', 'Invalid token. Please try again.');
        redirect('/admin/manage_balance.php');
    }

    $userId = (int)$_POST['user_id'];
    $amount = (float)$_POST['amount'];
    $type = $_POST['type'];
    
    try {
        $pdo = getConnection();
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Get current balance
        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $currentBalance = $stmt->fetch(PDO::FETCH_ASSOC)['balance'];
        
        // Calculate new balance
        if ($type === 'deposit') {
            $newBalance = $currentBalance + ($amount * COIN_RATE); // Convert to coins
            $transactionType = 'deposit';
        } else {
            if ($currentBalance < $amount) {
                throw new Exception('Insufficient balance');
            }
            $newBalance = $currentBalance - $amount;
            $transactionType = 'withdrawal';
        }
        
        // Update user balance
        $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
        $stmt->execute([$newBalance, $userId]);
        
        // Record transaction
        $stmt = $pdo->prepare("
            INSERT INTO transactions (user_id, type, amount, status, description) 
            VALUES (?, ?, ?, 'completed', ?)
        ");
        $description = $type === 'deposit' 
            ? "Admin deposit of " . number_format($amount, 2) . " IDR"
            : "Admin withdrawal of " . number_format($amount) . " coins";
        $transactionAmount = $type === 'deposit' ? ($amount * COIN_RATE) : $amount;
        $stmt->execute([$userId, $transactionType, $transactionAmount, $description]);
        
        // Commit transaction
        $pdo->commit();
        
        setFlashMessage('success', 'Balance updated successfully');
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Balance Update Error: " . $e->getMessage());
        setFlashMessage('danger', 'Error updating balance: ' . $e->getMessage());
    }
    
    redirect('/admin/manage_balance.php');
}

// Get all users except admin
try {
    $pdo = getConnection();
    $stmt = $pdo->query("
        SELECT u.*, 
               COALESCE(p.username, 'N/A') as parent_name 
        FROM users u 
        LEFT JOIN users p ON u.parent_id = p.id 
        WHERE u.role != 'admin' 
        ORDER BY u.role, u.username
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    $users = [];
    setFlashMessage('danger', 'Error loading user data');
}
?>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="h3 mb-4">Manage Balance</h1>

        <!-- Balance Management Card -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <form id="balanceForm" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="row g-3">
                        <!-- User Selection -->
                        <div class="col-md-4">
                            <label for="user_id" class="form-label">Select User</label>
                            <select class="form-select select2" id="user_id" name="user_id" required>
                                <option value="">Choose user...</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" 
                                            data-balance="<?php echo $user['balance']; ?>">
                                        <?php echo htmlspecialchars($user['username']); ?> 
                                        (<?php echo ucfirst($user['role']); ?>) 
                                        - Current: <?php echo number_format($user['balance']); ?> coins
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a user.</div>
                        </div>

                        <!-- Transaction Type -->
                        <div class="col-md-2">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="deposit">Deposit</option>
                                <option value="withdraw">Withdraw</option>
                            </select>
                            <div class="invalid-feedback">Please select a type.</div>
                        </div>

                        <!-- Amount -->
                        <div class="col-md-4">
                            <label for="amount" class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text amount-label">Rp</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="amount" 
                                       name="amount" 
                                       min="1" 
                                       step="1" 
                                       required>
                                <div class="invalid-feedback">Please enter a valid amount.</div>
                            </div>
                            <small class="text-muted conversion-text"></small>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                Update Balance
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card custom-card">
            <div class="card-body">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Parent Agent</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $user['role'] === 'agent' ? 'primary' : 'secondary'; 
                                    ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['parent_name']); ?></td>
                                <td><?php echo number_format($user['balance']); ?> coins</td>
                                <td>
                                    <button type="button" 
                                            class="btn btn-sm btn-primary quick-action"
                                            data-user-id="<?php echo $user['id']; ?>"
                                            data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                            data-balance="<?php echo $user['balance']; ?>">
                                        <i class="fas fa-coins"></i> Quick Update
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Modal -->
<div class="modal fade" id="quickActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Balance Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickActionForm" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="user_id" id="quickUserId">
                    
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" id="quickUsername" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Balance</label>
                        <input type="text" class="form-control" id="quickCurrentBalance" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <select class="form-select" name="type" id="quickType" required>
                            <option value="deposit">Deposit</option>
                            <option value="withdraw">Withdraw</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text quick-amount-label">Rp</span>
                            <input type="number" 
                                   class="form-control" 
                                   name="amount" 
                                   id="quickAmount" 
                                   required>
                        </div>
                        <small class="text-muted quick-conversion-text"></small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="quickActionForm" class="btn btn-primary">Update Balance</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const COIN_RATE = <?php echo COIN_RATE; ?>;
    
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5'
    });
    
    // Initialize DataTable
    $('.datatable').DataTable({
        order: [[1, 'asc'], [0, 'asc']]
    });
    
    // Update amount label and conversion text based on type
    function updateAmountDisplay(typeSelect, amountLabel, conversionText, amount) {
        const type = typeSelect.value;
        const amountValue = parseFloat(amount.value) || 0;
        
        if (type === 'deposit') {
            amountLabel.textContent = 'Rp';
            conversionText.textContent = amountValue ? 
                `Will add ${numberFormat(amountValue * COIN_RATE)} coins` : '';
        } else {
            amountLabel.textContent = 'Coins';
            conversionText.textContent = '';
        }
    }
    
    // Main form handling
    const mainForm = document.getElementById('balanceForm');
    const mainType = document.getElementById('type');
    const mainAmount = document.getElementById('amount');
    const mainAmountLabel = document.querySelector('.amount-label');
    const mainConversionText = document.querySelector('.conversion-text');
    
    mainType.addEventListener('change', () => {
        updateAmountDisplay(mainType, mainAmountLabel, mainConversionText, mainAmount);
    });
    
    mainAmount.addEventListener('input', () => {
        updateAmountDisplay(mainType, mainAmountLabel, mainConversionText, mainAmount);
    });
    
    // Quick action modal handling
    const quickActionModal = new bootstrap.Modal(document.getElementById('quickActionModal'));
    const quickType = document.getElementById('quickType');
    const quickAmount = document.getElementById('quickAmount');
    const quickAmountLabel = document.querySelector('.quick-amount-label');
    const quickConversionText = document.querySelector('.quick-conversion-text');
    
    document.querySelectorAll('.quick-action').forEach(button => {
        button.addEventListener('click', () => {
            const userId = button.dataset.userId;
            const username = button.dataset.username;
            const balance = parseInt(button.dataset.balance);
            
            document.getElementById('quickUserId').value = userId;
            document.getElementById('quickUsername').value = username;
            document.getElementById('quickCurrentBalance').value = numberFormat(balance) + ' coins';
            
            quickType.value = 'deposit';
            quickAmount.value = '';
            updateAmountDisplay(quickType, quickAmountLabel, quickConversionText, quickAmount);
            
            quickActionModal.show();
        });
    });
    
    quickType.addEventListener('change', () => {
        updateAmountDisplay(quickType, quickAmountLabel, quickConversionText, quickAmount);
    });
    
    quickAmount.addEventListener('input', () => {
        updateAmountDisplay(quickType, quickAmountLabel, quickConversionText, quickAmount);
    });
    
    // Form submission handling
    [mainForm, document.getElementById('quickActionForm')].forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }
            
            const formData = new FormData(this);
            const type = formData.get('type');
            const amount = parseFloat(formData.get('amount'));
            const userId = formData.get('user_id');
            
            // Confirm action
            Swal.fire({
                title: 'Confirm Balance Update',
                html: `Are you sure you want to ${type} ${type === 'deposit' ? 
                    'Rp' + numberFormat(amount) + ' (' + numberFormat(amount * COIN_RATE) + ' coins)' : 
                    numberFormat(amount) + ' coins'}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, update balance',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
    
    function numberFormat(number) {
        return new Intl.NumberFormat().format(number);
    }
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>