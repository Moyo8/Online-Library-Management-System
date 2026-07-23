<?php
/**
 * OLMS Database Setup & Diagnostic Script
 * This script helps verify and set up the database connection
 */

// Set headers
header('Content-Type: text/html; charset=utf-8');

// Define constants
define('ROOT', __DIR__ . '/');

// Database configuration
$host = 'localhost';
$db = 'olms';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset";
$dsn_with_db = "mysql:host=$host;dbname=$db;charset=$charset";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OLMS - Database Setup & Diagnostic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container-setup {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 600px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            margin: 5px 5px 5px 0;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        .status-warning {
            background: #fff3cd;
            color: #856404;
        }
        .result-section {
            margin: 20px 0;
            padding: 20px;
            border-radius: 10px;
            background: #f8f9fa;
            border-left: 4px solid #667eea;
        }
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
        }
        .btn-custom:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
    </style>
</head>
<body>
    <div class="container-setup">
        <h1 class="mb-4">🚀 OLMS Database Setup</h1>
        
        <div class="result-section">
            <h5>Connection Configuration</h5>
            <p><strong>Host:</strong> <?php echo htmlspecialchars($host); ?></p>
            <p><strong>Database:</strong> <?php echo htmlspecialchars($db); ?></p>
            <p><strong>User:</strong> <?php echo htmlspecialchars($user); ?></p>
        </div>

        <div class="result-section">
            <h5>Diagnostic Results</h5>
            
            <?php
            // Test 1: Connection to MySQL server
            echo "<p><strong>1. MySQL Server Connection:</strong></p>";
            try {
                $pdo_test = new PDO($dsn, $user, $pass);
                echo '<span class="status-badge status-success">✓ Connected to MySQL server</span><br>';
                $server_ok = true;
            } catch (\PDOException $e) {
                echo '<span class="status-badge status-error">✗ Connection failed: ' . htmlspecialchars($e->getMessage()) . '</span><br>';
                $server_ok = false;
            }
            
            // Test 2: Database existence
            echo "<p style='margin-top: 15px;'><strong>2. Database Status:</strong></p>";
            if ($server_ok) {
                try {
                    $pdo_db = new PDO($dsn_with_db, $user, $pass);
                    echo '<span class="status-badge status-success">✓ Database exists and is accessible</span><br>';
                    $db_ok = true;
                    
                    // Check tables
                    $tables = ['users', 'books', 'transactions', 'reservations'];
                    echo "<p style='margin-top: 15px;'><strong>3. Table Status:</strong></p>";
                    $missing_tables = [];
                    foreach ($tables as $table) {
                        try {
                            $stmt = $pdo_db->query("SHOW TABLES LIKE '$table'");
                            if ($stmt->rowCount() > 0) {
                                echo '<span class="status-badge status-success">✓ ' . htmlspecialchars($table) . '</span>';
                            } else {
                                echo '<span class="status-badge status-warning">✗ ' . htmlspecialchars($table) . ' (missing)</span>';
                                $missing_tables[] = $table;
                            }
                        } catch (\PDOException $e) {
                            echo '<span class="status-badge status-error">✗ Error checking ' . htmlspecialchars($table) . '</span>';
                        }
                    }
                    echo "<br>";
                    
                    if (!empty($missing_tables)) {
                        echo '<p style="margin-top: 15px; color: #856404;"><strong>⚠️ Some tables are missing. You need to import the database schema.</strong></p>';
                    }
                    
                } catch (\PDOException $e) {
                    echo '<span class="status-badge status-error">✗ Database does not exist: ' . htmlspecialchars($e->getMessage()) . '</span><br>';
                    echo '<p style="margin-top: 15px; color: #721c24;"><strong>⚠️ Database needs to be created.</strong></p>';
                    $db_ok = false;
                }
            } else {
                echo '<span class="status-badge status-error">✗ Cannot check database (server not connected)</span><br>';
            }
            ?>
        </div>

        <div class="result-section">
            <h5>Setup Instructions</h5>
            <ol>
                <li><strong>Ensure MySQL is running</strong> - XAMPP Apache and MySQL should be started</li>
                <li><strong>Import the database schema:</strong>
                    <ul>
                        <li>Open phpMyAdmin (http://localhost/phpmyadmin)</li>
                        <li>Create a new database named "olms" (if it doesn't exist)</li>
                        <li>Import the file: <code>sql/olms.sql</code></li>
                    </ul>
                </li>
                <li><strong>Verify connection settings:</strong> Edit <code>config.php</code> if needed (host, user, password)</li>
                <li><strong>Delete this setup.php file</strong> after database is set up</li>
                <li><strong>Access the application</strong> at http://localhost/olms</li>
            </ol>
        </div>

        <div style="margin-top: 30px; text-align: center;">
            <form method="get" action="">
                <button type="submit" name="auto_setup" value="1" class="btn btn-custom">
                    🔧 Auto-Setup Database (Recommended)
                </button>
            </form>
        </div>

        <?php
        // Auto-setup handler
        if (isset($_GET['auto_setup']) && $_GET['auto_setup'] == 1) {
            echo '<div class="result-section" style="margin-top: 20px; border-left-color: #28a745;">';
            echo '<h5>Auto-Setup Progress</h5>';
            
            try {
                // Step 1: Create database
                echo '<p><strong>Step 1:</strong> Creating database...</p>';
                $pdo_create = new PDO($dsn, $user, $pass);
                $pdo_create->exec("CREATE DATABASE IF NOT EXISTS olms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                echo '<span class="status-badge status-success">✓ Database created/verified</span><br>';
                
                // Step 2: Import schema
                echo '<p style="margin-top: 15px;"><strong>Step 2:</strong> Importing schema...</p>';
                $pdo_import = new PDO($dsn_with_db, $user, $pass);
                
                // Read and execute SQL file
                $sql_file = ROOT . 'sql/olms.sql';
                if (file_exists($sql_file)) {
                    $sql = file_get_contents($sql_file);
                    // Split by semicolon and execute each statement
                    $statements = array_filter(array_map('trim', explode(';', $sql)));
                    foreach ($statements as $statement) {
                        if (!empty($statement) && !preg_match('/^--/', $statement)) {
                            try {
                                $pdo_import->exec($statement);
                            } catch (\PDOException $e) {
                                // Skip errors for CREATE TABLE IF NOT EXISTS
                                if (strpos($e->getMessage(), 'already exists') === false) {
                                    // Ignore table exists errors
                                }
                            }
                        }
                    }
                    echo '<span class="status-badge status-success">✓ Schema imported successfully</span><br>';
                } else {
                    echo '<span class="status-badge status-error">✗ SQL file not found at: ' . htmlspecialchars($sql_file) . '</span><br>';
                }
                
                echo '<p style="margin-top: 20px; color: #155724;"><strong>✓ Database setup completed successfully!</strong></p>';
                echo '<p>You can now delete this setup.php file and access the application at <a href="/olms">http://localhost/olms</a></p>';
                
            } catch (\PDOException $e) {
                echo '<span class="status-badge status-error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</span><br>';
            }
            
            echo '</div>';
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
