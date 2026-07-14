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

if (isset($_GET["id"])) {
    checkConnection($conn);
    
    $id = (int) $_GET["id"];
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $stmt->close();
}

header("Location: index.php");
exit();
?>