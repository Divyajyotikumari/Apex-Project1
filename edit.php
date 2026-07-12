<?php
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    header("Location: index.php");
    exit();
}

if (isset($_POST["update"])) {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);

    if ($title !== "" && $content !== "") {
        $updateStmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $updateStmt->bind_param("ssi", $title, $content, $id);
        $updateStmt->execute();
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
        <input type="text" name="title" class="form-control mb-3" value="<?php echo htmlspecialchars($row['title']); ?>" required>
        <textarea name="content" class="form-control mb-3" rows="6" required><?php echo htmlspecialchars($row['content']); ?></textarea>
        <button class="btn btn-warning" name="update">Update</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>