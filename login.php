<?php
include "db.php";

if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$message = "";

if (isset($_POST["login"])) {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if ($username !== "" && $password !== "") {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];
            header("Location: index.php");
            exit();
        }

        $message = "Invalid username or password.";
    } else {
        $message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container mt-5">
    <div class="text-center mb-4">
        <img src="../image1.jpg" alt="Login illustration" class="img-fluid rounded" style="max-height: 180px;">
    </div>
    <h3 class="text-center">Login</h3>
    <?php if ($message !== ""): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="username" class="form-control mb-3" placeholder="Username" minlength="3" maxlength="20" required>
        <input type="password" name="password" class="form-control mb-3" placeholder="Password" minlength="6" required>
        <button class="btn btn-success" name="login">Login</button>
        <a href="register.php" class="btn btn-secondary">Register</a>
    </form>
</div>
</body>
</html>
