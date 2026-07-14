<?php
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (!in_array($_SESSION["role"] ?? "editor", ["admin", "editor"], true)) {
    header("Location: index.php");
    exit();
}

$message = "";

if (isset($_POST["save"])) {
    checkConnection($conn);
    
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $errors = [];

    if ($title === "") {
        $errors[] = "Title is required.";
    } elseif (strlen($title) > 200) {
        $errors[] = "Title must be 200 characters or less.";
    }

    if ($content === "") {
        $errors[] = "Content is required.";
    } elseif (strlen($content) > 5000) {
        $errors[] = "Content must be 5000 characters or less.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("ss", $title, $content);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->close();
        header("Location: index.php");
        exit();
    }

    $message = implode(" ", $errors);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container mt-5">
    <h3>Add New Post</h3>
    <?php if ($message !== ""): ?>
        <div class="alert alert-warning"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="title" class="form-control mb-3" placeholder="Title" minlength="3" maxlength="200" required>
        <textarea name="content" class="form-control mb-3" placeholder="Content" rows="6" minlength="1" maxlength="5000" required></textarea>
        <button class="btn btn-success" name="save">Save</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>