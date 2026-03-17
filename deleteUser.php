<?php
/*  
    I certify that the html file I am submitting is all my own and group work. 
    None of it is copied from any source or any person. 
    Signed: Philip Lee
    Date: 12/09/2025
    Author: Philip Lee
    Date: 12/09/2025
    Class: CSS 305
    File Name: deleteUser.php
    Assignment: Final project
    Description: Deletes User in login and user DB
*/

/*
Admin-only delete user
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'session_check.php';
require 'csrf.php';
require 'db.php';

// Admin protection
if ($_SESSION['role'] !== 'Admin') {
    echo "Access Denied: Only Admins may delete users.";
    exit;
}

function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$error = "";
$flash = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        echo "Invalid request token.";
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    if ($username === '' || $email === '') {
        $error = "Username and email required.";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE username = ? AND email = ?");
        $stmt->bind_param("ss", $username, $email);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            header("Location: users.php?msg=" . urlencode("User deleted."));
            exit;
        } else {
            $error = "No matching user found.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Delete User</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="body">

<header class="site-header">
    <span class="brand">
        <span class="brand-mark"></span>
        <span class="brand-name">BoyeLeeNaga Shop</span>
    </span>
    <nav class="nav-links">
        <a href="dashboard.php" class="nav-link">Home</a>
        <a href="users.php" class="nav-link">Users</a>
        <a href="logout.php" class="nav-link">Logout</a>
    </nav>
</header>

<main class="layout">
    <section class="panel panel-login">
        <h2 class="panel-title">Delete User</h2>

        <?php if ($error): ?>
            <p class="alert error"><?= h($error) ?></p>
        <?php endif; ?>

        <form method="post" action="deleteUser.php" class="form">

            <label class="field">
                <span>Username</span>
                <input type="text" name="username" required>
            </label>

            <label class="field">
                <span>Email</span>
                <input type="email" name="email" required>
            </label>

            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

            <section class="form-actions">
                <button type="submit" class="btn btn-danger">Delete</button>
                <a href="users.php" class="btn btn-ghost">Cancel</a>
            </section>
        </form>
    </section>
</main>

<footer class="site-footer">
    <span>© BoyeLeeNaga · CSS 305 Final Project</span>
</footer>

</body>
</html>
