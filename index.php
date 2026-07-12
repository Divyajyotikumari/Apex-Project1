<?php
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$userRole = isset($_SESSION["role"]) ? $_SESSION["role"] : "editor";
$canManagePosts = in_array($userRole, ["admin", "editor"], true);

$limit = 5;
$page = isset($_GET["page"]) ? max(1, (int) $_GET["page"]) : 1;
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$offset = ($page - 1) * $limit;

$where = "";
$params = [];

if ($search !== "") {
    $where = " WHERE title LIKE ? OR content LIKE ? ";
    $like = "%{$search}%";
    $params = [$like, $like];
}

$countSql = "SELECT COUNT(*) AS total FROM posts{$where}";
$countStmt = $conn->prepare($countSql);
if ($search !== "") {
    $countStmt->bind_param("ss", $params[0], $params[1]);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$totalPosts = (int) $countRow["total"];
$totalPages = max(1, (int) ceil($totalPosts / $limit));
$countStmt->close();

$sql = "SELECT * FROM posts{$where} ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($search !== "") {
    $stmt->bind_param("ssii", $params[0], $params[1], $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
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
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="text-success mb-0">Advanced CRUD Blog</h2>
            <p class="text-muted mb-0">Logged in as <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> (Role: <strong><?php echo htmlspecialchars($userRole); ?></strong>)</p>
        </div>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>

    <div class="row mb-4 align-items-end">
        <div class="col-md-6">
            <form method="get" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Search posts" value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-primary">Search</button>
            </form>
        </div>
        <div class="col-md-6 text-md-end">
            <?php if ($canManagePosts): ?>
                <a href="add.php" class="btn btn-primary">Add New Post</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
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
                        <?php if ($canManagePosts): ?>
                            <a href="edit.php?id=<?php echo (int) $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete.php?id=<?php echo (int) $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this post?');">Delete</a>
                        <?php else: ?>
                            <span class="text-muted">Read-only</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <nav aria-label="Page navigation" class="mt-3">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="index.php?page=<?php echo $i; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php else: ?>
        <div class="alert alert-info">No posts found yet.</div>
    <?php endif; ?>
</div>
</body>
</html>