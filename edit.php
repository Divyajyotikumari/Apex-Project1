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
    <title>Edit Post - CRUD Blog</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            padding: 40px;
            max-width: 700px;
            width: 100%;
            color: white;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.6s ease-out;
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

        .form-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .form-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            display: inline-block;
            opacity: 0.9;
        }

        .form-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .form-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .post-meta {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 25px;
            font-size: 0.9rem;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: #f093fb;
        }

        .form-input,
        .form-textarea {
            width: 100%;
            background: rgba(255, 255, 255, 0.9);
            border: 1.5px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 0.95rem;
            color: #1f2937;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-input::placeholder,
        .form-textarea::placeholder {
            color: #9ca3af;
        }

        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            background: white;
            border-color: rgba(102, 126, 234, 0.5);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .form-textarea {
            resize: vertical;
            min-height: 180px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .char-count {
            font-size: 0.85rem;
            opacity: 0.8;
            margin-top: 5px;
        }

        .char-count.warning {
            color: #fbbf24;
        }

        .char-count.danger {
            color: #f87171;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 35px;
            flex-wrap: wrap;
        }

        .btn-update,
        .btn-cancel {
            flex: 1;
            min-width: 140px;
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-update {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-update:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-cancel {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
        }

        @media (max-width: 600px) {
            .form-container {
                padding: 25px;
                border-radius: 20px;
            }

            .form-header h1 {
                font-size: 1.5rem;
            }

            .post-meta {
                flex-direction: column;
                gap: 10px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-update,
            .btn-cancel {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="form-container">
    <div class="form-header">
        <div class="form-icon">
            <i class="fas fa-edit"></i>
        </div>
        <h1>Edit Post</h1>
        <p>Update your post content</p>
    </div>

    <div class="post-meta">
        <div class="meta-item">
            <i class="fas fa-hashtag"></i>
            <span>Post #<?php echo htmlspecialchars($row['id']); ?></span>
        </div>
        <div class="meta-item">
            <i class="fas fa-calendar-alt"></i>
            <span><?php echo date('M d, Y', strtotime($row["created_at"])); ?></span>
        </div>
    </div>

    <form method="post">
        <div class="form-group">
            <label for="title" class="form-label">
                <i class="fas fa-heading"></i> Post Title
            </label>
            <input type="text" id="title" name="title" class="form-input" value="<?php echo htmlspecialchars($row['title']); ?>" minlength="3" maxlength="200" required>
            <div class="char-count"><span id="titleCount"><?php echo strlen($row['title']); ?></span>/200 characters</div>
        </div>

        <div class="form-group">
            <label for="content" class="form-label">
                <i class="fas fa-pen-fancy"></i> Content
            </label>
            <textarea id="content" name="content" class="form-textarea" minlength="1" maxlength="5000" required><?php echo htmlspecialchars($row['content']); ?></textarea>
            <div class="char-count"><span id="contentCount"><?php echo strlen($row['content']); ?></span>/5000 characters</div>
        </div>

        <div class="form-actions">
            <button type="submit" name="update" class="btn-update">
                <i class="fas fa-check-circle"></i> Update Post
            </button>
            <a href="index.php" class="btn-cancel">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<script>
    // Real-time character counter
    document.getElementById('title').addEventListener('input', function() {
        document.getElementById('titleCount').textContent = this.value.length;
    });

    document.getElementById('content').addEventListener('input', function() {
        const count = this.value.length;
        const counter = document.getElementById('contentCount');
        counter.textContent = count;
        
        const parent = counter.parentElement;
        if (count > 4500) {
            parent.classList.add('danger');
            parent.classList.remove('warning');
        } else if (count > 4000) {
            parent.classList.add('warning');
            parent.classList.remove('danger');
        } else {
            parent.classList.remove('warning', 'danger');
        }
    });
</script>
</body>
</html>