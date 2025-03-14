<?php
require_once __DIR__ . '/functions/functions.php';

$pageTitle = 'Ampibet Setup';
include_once __DIR__ . '/includes/header.php';

// Function to check if database exists
function databaseExists($pdo, $dbName) {
    try {
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbName'");
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        return false;
    }
}

// Function to check if tables exist
function tablesExist($pdo) {
    try {
        $stmt = $pdo->query("SHOW TABLES");
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

$setupComplete = false;
$errors = [];
$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Create database connection without database name
        $pdo = new PDO(
            "mysql:host=" . DB_HOST,
            DB_USER,
            DB_PASSWORD,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Create database if it doesn't exist
        if (!databaseExists($pdo, DB_NAME)) {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $messages[] = "Database created successfully.";
        }

        // Select the database
        $pdo->exec("USE " . DB_NAME);

        // Check if tables already exist
        if (!tablesExist($pdo)) {
            // Read and execute SQL schema
            $sql = file_get_contents(__DIR__ . '/database/schema.sql');
            $pdo->exec($sql);
            $messages[] = "Database tables created successfully.";

            // Create default admin user if not exists
            $adminUsername = 'admin';
            $adminPassword = 'admin123'; // This should be changed immediately after setup
            $adminEmail = 'admin@ampibet.com';

            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$adminUsername]);
            
            if (!$stmt->fetch()) {
                createUser($adminUsername, $adminPassword, 'admin', $adminEmail);
                $messages[] = "Default admin user created successfully.";
                $messages[] = "Username: admin";
                $messages[] = "Password: admin123";
                $messages[] = "Please change these credentials immediately after logging in.";
            }

            $setupComplete = true;
        } else {
            $messages[] = "Database tables already exist.";
            $setupComplete = true;
        }
    } catch (PDOException $e) {
        $errors[] = "Database Error: " . $e->getMessage();
    } catch (Exception $e) {
        $errors[] = "Error: " . $e->getMessage();
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card custom-card mt-5">
                <div class="card-body text-center">
                    <i class="fas fa-cogs fa-4x text-primary mb-3"></i>
                    <h2 class="card-title">Ampibet Setup</h2>
                    
                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Setup Errors:</h5>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($messages): ?>
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle me-2"></i>Setup Messages:</h5>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($messages as $message): ?>
                                    <li><?php echo $message; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!$setupComplete): ?>
                        <p class="mb-4">Click the button below to initialize the database and create the admin account.</p>
                        <form method="POST" class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-magic me-2"></i>Run Setup
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info mb-4">
                            <h5><i class="fas fa-info-circle me-2"></i>Setup Complete!</h5>
                            <p class="mb-0">You can now proceed to the login page.</p>
                        </div>
                        <a href="/auth/login.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Go to Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Setup Instructions -->
            <div class="card custom-card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Setup Instructions</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2">Ensure your MySQL server is running and accessible.</li>
                        <li class="mb-2">Verify the database credentials in <code>config.php</code>.</li>
                        <li class="mb-2">Run the setup by clicking the "Run Setup" button.</li>
                        <li class="mb-2">After setup, log in with the default admin credentials.</li>
                        <li>Change the default admin password immediately.</li>
                    </ol>
                </div>
            </div>

            <!-- System Requirements -->
            <div class="card custom-card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">System Requirements</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            PHP Version
                            <span class="badge bg-<?php echo PHP_VERSION_ID >= 70400 ? 'success' : 'danger'; ?>">
                                <?php echo PHP_VERSION; ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            PDO MySQL Extension
                            <span class="badge bg-<?php echo extension_loaded('pdo_mysql') ? 'success' : 'danger'; ?>">
                                <?php echo extension_loaded('pdo_mysql') ? 'Installed' : 'Missing'; ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            MySQL Connection
                            <?php
                            try {
                                $testPdo = new PDO(
                                    "mysql:host=" . DB_HOST,
                                    DB_USER,
                                    DB_PASSWORD
                                );
                                echo '<span class="badge bg-success">Connected</span>';
                            } catch (PDOException $e) {
                                echo '<span class="badge bg-danger">Failed</span>';
                            }
                            ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>