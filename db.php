<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "blog";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    if ($conn->connect_errno === 1049) {
        $conn = new mysqli($host, $user, $pass);
        if ($conn->connect_error) {
            die("Database connection failed: " . $conn->connect_error);
        }
        $conn->query("CREATE DATABASE IF NOT EXISTS blog");
        $conn->select_db($dbname);
    } else {
        die("Database connection failed: " . $conn->connect_error);
    }
}

$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$usersColumns = $conn->query("SHOW COLUMNS FROM users");
$usersFields = [];
while ($col = $usersColumns->fetch_assoc()) {
    $usersFields[] = $col['Field'];
}

if (!in_array('username', $usersFields, true)) {
    if (in_array('users name', $usersFields, true)) {
        $conn->query("ALTER TABLE users CHANGE COLUMN `users name` username VARCHAR(50) NOT NULL");
    } else {
        $conn->query("ALTER TABLE users ADD COLUMN username VARCHAR(50) NOT NULL");
    }
}

if (!in_array('password', $usersFields, true)) {
    $conn->query("ALTER TABLE users ADD COLUMN password VARCHAR(255) NOT NULL");
}

if (!in_array('role', $usersFields, true)) {
    $conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'editor'");
}

if (!in_array('created_at', $usersFields, true)) {
    $conn->query("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

$postsColumns = $conn->query("SHOW COLUMNS FROM posts");
$postsFields = [];
while ($col = $postsColumns->fetch_assoc()) {
    $postsFields[] = $col['Field'];
}

if (!in_array('created_at', $postsFields, true)) {
    if (in_array('created at', $postsFields, true)) {
        $conn->query("ALTER TABLE posts CHANGE COLUMN `created at` created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    } else {
        $conn->query("ALTER TABLE posts ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }
}

if (!in_array('content', $postsFields, true)) {
    $conn->query("ALTER TABLE posts ADD COLUMN content TEXT NOT NULL");
} else {
    $conn->query("ALTER TABLE posts MODIFY content TEXT NOT NULL");
}

if (!in_array('title', $postsFields, true)) {
    $conn->query("ALTER TABLE posts ADD COLUMN title VARCHAR(200) NOT NULL");
}
?>