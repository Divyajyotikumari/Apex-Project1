<?php
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";

if (isset($_POST["save"])) {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);

    if ($title !== "" && $content !== "") {
        $stmt = $conn->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $content);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php");
        exit();
    }

    $message = "Please enter both title and content.";
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
        <input type="text" name="title" class="form-control mb-3" placeholder="Title" required>
        <textarea name="content" class="form-control mb-3" placeholder="Content" rows="6" required></textarea>
        <button class="btn btn-success" name="save">Save</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>