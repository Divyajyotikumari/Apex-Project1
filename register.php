<?php
include "db.php";

if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$message = "";

if (isset($_POST["register"])) {
    checkConnection($conn);
    
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $errors = [];

    if ($username === "") {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = "Username must be between 3 and 20 characters.";
    }

    if ($password === "") {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if (empty($errors)) {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        if ($check === false) {
            die("Prepare failed: " . $conn->error);
        }
        
        $check->bind_param("s", $username);
        if (!$check->execute()) {
            die("Query failed: " . $check->error);
        }
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Username already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = "editor";
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("sss", $username, $hash, $role);
            if (!$stmt->execute()) {
                die("Execute failed: " . $stmt->error);
            }
            $stmt->close();
            $message = "Registration successful. Please login.";
        }

        $check->close();
    } else {
        $message = implode(" ", $errors);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CRUD Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .auth-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
            padding: 40px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .auth-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .auth-header p {
            color: #6b7280;
            font-size: 14px;
            margin: 0;
        }

        .auth-icon {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
            display: inline-block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #374151;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .form-control:focus {
            background: white;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .password-requirements {
            background: #f3f4f6;
            border-left: 3px solid #667eea;
            padding: 12px;
            border-radius: 6px;
            margin-top: 12px;
            font-size: 13px;
            color: #6b7280;
        }

        .requirement-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 6px 0;
        }

        .requirement-item i {
            font-size: 12px;
        }

        .requirement-item.valid {
            color: #059669;
        }

        .requirement-item.invalid {
            color: #dc2626;
        }

        .btn-register {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .auth-divider {
            text-align: center;
            margin: 25px 0;
            color: #9ca3af;
            font-size: 14px;
            position: relative;
        }

        .auth-divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e5e7eb;
        }

        .auth-divider span {
            position: relative;
            background: white;
            padding: 0 10px;
        }

        .auth-footer {
            text-align: center;
            margin-top: 25px;
        }

        .auth-footer p {
            color: #6b7280;
            font-size: 14px;
            margin: 0;
        }

        .auth-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .auth-footer a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .alert i {
            font-size: 18px;
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 30px 20px;
            }

            .auth-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1>Create Account</h1>
            <p>Join CRUD application today</p>
        </div>

        <?php if ($message !== ""): ?>
            <?php 
            $alert_class = strpos($message, "successful") !== false ? "alert-success" : "alert-danger";
            $icon = strpos($message, "successful") !== false ? "fa-check-circle" : "fa-exclamation-circle";
            ?>
            <div class="alert <?php echo $alert_class; ?>">
                <i class="fas <?php echo $icon; ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Choose a username (3-20 characters)" minlength="3" maxlength="20" required>
                <div class="password-requirements">
                    <div class="requirement-item">
                        <i class="fas fa-info-circle"></i>
                        <span>3-20 characters</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Create a strong password" minlength="6" required>
                <div class="password-requirements">
                    <div class="requirement-item" id="req-length">
                        <i class="fas fa-times-circle"></i>
                        <span>At least 6 characters</span>
                    </div>
                </div>
            </div>

            <button type="submit" name="register" class="btn-register">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <div class="auth-divider">
            <span>Already a member?</span>
        </div>

        <div class="auth-footer">
            <p>Have an account? <a href="login.php">Sign in here</a></p>
        </div>
    </div>
</div>

<script>
    // Real-time password validation
    const passwordInput = document.getElementById('password');
    const reqLength = document.getElementById('req-length');

    passwordInput.addEventListener('input', function() {
        if (this.value.length >= 6) {
            reqLength.classList.remove('invalid');
            reqLength.classList.add('valid');
            reqLength.innerHTML = '<i class="fas fa-check-circle"></i><span>At least 6 characters</span>';
        } else {
            reqLength.classList.remove('valid');
            reqLength.classList.add('invalid');
            reqLength.innerHTML = '<i class="fas fa-times-circle"></i><span>At least 6 characters</span>';
        }
    });
</script>
</body>
</html>
