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

checkConnection($conn);

$where = "";
$params = [];

if ($search !== "") {
    $where = " WHERE title LIKE ? OR content LIKE ? ";
    $like = "%{$search}%";
    $params = [$like, $like];
}

$countSql = "SELECT COUNT(*) AS total FROM posts{$where}";
$countStmt = $conn->prepare($countSql);
if ($countStmt === false) {
    die("Prepare failed: " . $conn->error);
}
if ($search !== "") {
    $countStmt->bind_param("ss", $params[0], $params[1]);
}
if (!$countStmt->execute()) {
    die("Execute failed: " . $countStmt->error);
}
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$totalPosts = (int) $countRow["total"];
$totalPages = max(1, (int) ceil($totalPosts / $limit));
$countStmt->close();

$sql = "SELECT * FROM posts{$where} ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
if ($search !== "") {
    $stmt->bind_param("ssii", $params[0], $params[1], $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Blog - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 30%, #f093fb 70%, #4facfe 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: moveBackground 20s linear infinite;
            z-index: 0;
        }

        @keyframes moveBackground {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        .main-container {
            position: relative;
            z-index: 1;
            padding: 20px;
        }

        /* Glass Morphism Header */
        .glass-header {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            animation: slideDown 0.6s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .glass-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.95rem;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .header-controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        /* Glass Buttons */
        .btn-glass {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            color: white;
        }

        .btn-glass-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-glass-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
        }

        .btn-glass-danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
        }

        .btn-glass-danger:hover {
            background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
            color: white;
        }

        /* Search Bar Glass */
        .search-glass {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-glass input {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            flex: 1;
            min-width: 200px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .search-glass input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
            transform: scale(1.02);
        }

        /* Posts Container */
        .posts-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        /* Glass Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 25px;
            color: white;
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease-out;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            animation: moveGlassCard 30s linear infinite;
        }

        @keyframes moveGlassCard {
            0% { transform: translate(0, 0); }
            100% { transform: translate(30px, 30px); }
        }

        .glass-card:hover {
            transform: translateY(-8px);
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .card-header-glass {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .card-id {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin: 15px 0 10px 0;
            color: white;
            word-break: break-word;
        }

        .card-content {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 15px;
            max-height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            line-height: 1.5;
        }

        .card-date {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            position: relative;
            z-index: 2;
        }

        .card-btn {
            flex: 1;
            min-width: 80px;
            padding: 8px 12px;
            border: none;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .card-btn-edit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .card-btn-edit:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .card-btn-delete {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .card-btn-delete:hover {
            background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: white;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.8;
        }

        .empty-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .empty-text {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        /* Pagination */
        .pagination-glass {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .pagination-glass a,
        .pagination-glass span {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 14px;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .pagination-glass a:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        .pagination-glass .active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .glass-header {
                padding: 20px;
            }

            .glass-header h1 {
                font-size: 1.8rem;
            }

            .posts-container {
                grid-template-columns: 1fr;
            }

            .search-glass {
                flex-direction: column;
            }

            .search-glass input {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="main-container">
    <!-- Header -->
    <div class="glass-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div>
                <h1><i class="fas fa-blog"></i> Blog Dashboard</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <div style="font-size: 0.85rem; opacity: 0.9;">Role: <?php echo htmlspecialchars($userRole); ?></div>
                    </div>
                </div>
            </div>
            <a href="logout.php" class="btn-glass btn-glass-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="search-glass">
        <form method="get" style="display: flex; gap: 10px; flex: 1; min-width: 100%; flex-wrap: wrap;">
            <input type="text" name="search" placeholder="🔍 Search posts by title or content..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; min-width: 150px;">
            <button type="submit" class="btn-glass btn-glass-primary" style="white-space: nowrap;">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if ($canManagePosts): ?>
                <a href="add.php" class="btn-glass btn-glass-primary" style="white-space: nowrap;">
                    <i class="fas fa-plus-circle"></i> New Post
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Posts Grid -->
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="posts-container">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="glass-card">
                    <div class="card-header-glass">
                        <span class="card-id">#<?php echo htmlspecialchars($row["id"]); ?></span>
                    </div>
                    <h3 class="card-title"><?php echo htmlspecialchars($row["title"]); ?></h3>
                    <p class="card-content"><?php echo htmlspecialchars(substr($row["content"], 0, 150)); ?>...</p>
                    <div class="card-date">
                        <i class="fas fa-calendar-alt"></i>
                        <?php echo date('M d, Y', strtotime($row["created_at"])); ?>
                    </div>
                    <div class="card-actions">
                        <?php if ($canManagePosts): ?>
                            <a href="edit.php?id=<?php echo (int) $row['id']; ?>" class="card-btn card-btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete.php?id=<?php echo (int) $row['id']; ?>" class="card-btn card-btn-delete" onclick="return confirm('Delete this post?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        <?php else: ?>
                            <div style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem;"><i class="fas fa-lock"></i> Read-only</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-glass">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="index.php?page=<?php echo $i; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>" <?php echo $i === $page ? 'class="active"' : ''; ?>>
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <div class="empty-title">No Posts Yet</div>
            <div class="empty-text">
                <?php if ($search !== ''): ?>
                    No posts found matching your search. Try a different search term!
                <?php else: ?>
                    Start creating amazing content now!
                <?php endif; ?>
            </div>
            <?php if ($canManagePosts): ?>
                <a href="add.php" class="btn-glass btn-glass-primary">
                    <i class="fas fa-plus-circle"></i> Create First Post
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>