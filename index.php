<?php
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Posts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-success mb-0">Basic CRUD Blog</h2>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>

    <div class="mb-3">
        <a href="add.php" class="btn btn-primary">Add New Post</a>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Content</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row["id"]); ?></td>
                <td><?php echo htmlspecialchars($row["title"]); ?></td>
                <td><?php echo nl2br(htmlspecialchars($row["content"])); ?></td>
                <td><?php echo htmlspecialchars($row["created_at"]); ?></td>
                <td>
                    <a href="edit.php?id=<?php echo (int) $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="delete.php?id=<?php echo (int) $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this post?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="alert alert-info">No posts found yet.</div>
    <?php endif; ?>
</div>
</body>
</html>