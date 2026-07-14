<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "blog";

// Function to establish database connection
function connectDB() {
    global $host, $user, $pass, $dbname;
    
    $attempts = 0;
    $maxAttempts = 3;
    
    while ($attempts < $maxAttempts) {
        $conn = @new mysqli($host, $user, $pass, $dbname);
        
        if (!$conn->connect_error) {
            // Connection successful
            $conn->set_charset("utf8mb4");
            $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
            return $conn;
        }
        
        // Handle different connection errors
        if ($conn->connect_errno === 1049) {
            // Database doesn't exist, try to create it
            $conn = @new mysqli($host, $user, $pass);
            if (!$conn->connect_error) {
                $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
                $conn->select_db($dbname);
                $conn->set_charset("utf8mb4");
                return $conn;
            }
        } elseif ($conn->connect_errno === 2002 || $conn->connect_errno === 111) {
            // MySQL server not running - wait and retry
            $attempts++;
            if ($attempts < $maxAttempts) {
                sleep(1);
                continue;
            }
        }
        
        // If we get here, display error message
        ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
        }
        .error-icon {
            font-size: 48px;
            color: #dc2626;
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 15px;
        }
        .error-message {
            color: #6b7280;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .solution-box {
            background: #f3f4f6;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
        }
        .solution-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .solution-steps {
            color: #6b7280;
            font-size: 14px;
        }
        .solution-steps li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">
            <i class="fas fa-database"></i>
        </div>
        <div class="error-title">Database Connection Failed</div>
        <div class="error-message">
            Unable to connect to MySQL server. The server is either not running or unreachable.
        </div>
        <div class="error-message" style="font-size: 13px; color: #9ca3af;">
            <strong>Error Code:</strong> <?php echo $conn->connect_errno; ?><br>
            <strong>Error Message:</strong> <?php echo htmlspecialchars($conn->connect_error); ?>
        </div>
        <div class="solution-box">
            <div class="solution-title">🔧 How to Fix:</div>
            <div class="solution-steps">
                <ol>
                    <li><strong>Open XAMPP Control Panel</strong></li>
                    <li><strong>Click "Start" for MySQL</strong> - Wait for it to show "Running"</li>
                    <li><strong>Refresh this page</strong></li>
                </ol>
            </div>
        </div>
        <div class="error-message" style="font-size: 13px; margin-top: 20px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 15px;">
            If MySQL still won't start:
            <ul style="margin-top: 8px; padding-left: 20px;">
                <li>Check if port 3306 is already in use</li>
                <li>Check XAMPP MySQL error logs</li>
                <li>Try restarting your computer</li>
            </ul>
        </div>
    </div>
</body>
</html>
        <?php
        exit();
    }
}

// Establish connection
$conn = connectDB();

// Check connection status function
function checkConnection(&$conn) {
    global $host, $user, $pass, $dbname;
    
    if (!$conn->ping()) {
        $conn->close();
        $conn = connectDB();
    }
}

$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$usersColumns = $conn->query("SHOW COLUMNS FROM users");
$usersFields = [];
while ($col = $usersColumns->fetch_assoc()) {
    $usersFields[] = $col['Field'];
}

if (!in_array('username', $usersFields, true)) {
    if (in_array('users name', $usersFields, true)) {
        $conn->query("ALTER TABLE users CHANGE COLUMN `users name` username VARCHAR(50) NOT NULL");
    } else {
        $conn->query("ALTER TABLE users ADD COLUMN username VARCHAR(50) NOT NULL");
    }
}

if (!in_array('password', $usersFields, true)) {
    $conn->query("ALTER TABLE users ADD COLUMN password VARCHAR(255) NOT NULL");
}

if (!in_array('role', $usersFields, true)) {
    $conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'editor'");
}

if (!in_array('created_at', $usersFields, true)) {
    $conn->query("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

$postsColumns = $conn->query("SHOW COLUMNS FROM posts");
$postsFields = [];
while ($col = $postsColumns->fetch_assoc()) {
    $postsFields[] = $col['Field'];
}

if (!in_array('created_at', $postsFields, true)) {
    if (in_array('created at', $postsFields, true)) {
        $conn->query("ALTER TABLE posts CHANGE COLUMN `created at` created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    } else {
        $conn->query("ALTER TABLE posts ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }
}

if (!in_array('content', $postsFields, true)) {
    $conn->query("ALTER TABLE posts ADD COLUMN content TEXT NOT NULL");
} else {
    $conn->query("ALTER TABLE posts MODIFY content TEXT NOT NULL");
}

if (!in_array('title', $postsFields, true)) {
    $conn->query("ALTER TABLE posts ADD COLUMN title VARCHAR(200) NOT NULL");
}
?>