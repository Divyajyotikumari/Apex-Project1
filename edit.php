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

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
checkConnection($conn);

$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    header("Location: index.php");
    exit();
}

if (isset($_POST["update"])) {
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
        $updateStmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        if ($updateStmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        
        $updateStmt->bind_param("ssi", $title, $content, $id);
        if (!$updateStmt->execute()) {
            die("Execute failed: " . $updateStmt->error);
        }
        $updateStmt->close();
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container mt-5">
    <h3>Edit Post</h3>
    <form method="post">
        <input type="text" name="title" class="form-control mb-3" value="<?php echo htmlspecialchars($row['title']); ?>" minlength="3" maxlength="200" required>
        <textarea name="content" class="form-control mb-3" rows="6" minlength="1" maxlength="5000" required><?php echo htmlspecialchars($row['content']); ?></textarea>
        <button class="btn btn-warning" name="update">Update</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>