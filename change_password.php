<?php
/*
I certify that the PHP file I am submitting is all my own work.
None of it is copied from any source or any person.
Signed: Samuel Boye
Date: 12/10/2025
Class: CSS 305
File Name: change_password.php
Assignment: Final Project – Car Parts Catalog
Description: Allows a logged-in user to change their own password.
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'session_check.php';
require 'db.php';
require 'csrf.php';

function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$userId   = $_SESSION['user_id'] ?? 0;
$username = $_SESSION['username'] ?? '';

if (!$userId) {
    echo "You must be logged in to change your password.";
    exit;
}

$flash = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request token.";
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Basic validation
        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $error = "All fields are required.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "New password and confirmation do not match.";
        } elseif (strlen($newPassword) < 8) {
            $error = "New password must be at least 8 characters.";
        } else {
            // Load current password hash from DB
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
            if (!$stmt) {
                $error = "Database error (prepare).";
            } else {
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();

                if (!$row) {
                    $error = "User record not found.";
                } else {
                    $hash = $row['password'];

                    // Verify current password
                    if (!password_verify($currentPassword, $hash)) {
                        $error = "Current password is incorrect.";
                    } else {
                        // Hash new password
                        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

                        $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                        if (!$upd) {
                            $error = "Database error (update).";
                        } else {
                            $upd->bind_param("si", $newHash, $userId);

                            if ($upd->execute()) {
                                $flash = "Password changed successfully.";
                                // Rotate token for safety
                                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
                            } else {
                                $error = "Unable to change password.";
                            }
                            $upd->close();
                        }
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password – BoyeLeeNaga Shop</title>
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
        <a href="catalog.php" class="nav-link">Catalog</a>
        <a href="suppliers.php" class="nav-link">Suppliers</a>
        <a href="users.php" class="nav-link">Users</a>
        <a href="logout.php" class="nav-link">Logout</a>
    </nav>
</header>

<main class="page-main">
    <section class="panel panel-login" style="max-width:420px; width:100%;">
        <h1 class="panel-title">Change Password</h1>
        <p class="panel-text">
            Signed in as <strong><?= h($username) ?></strong>.
            Enter your current password and choose a new one.
        </p>

        <?php if ($flash): ?>
            <p class="alert success"><?= h($flash) ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="alert error"><?= h($error) ?></p>
        <?php endif; ?>

        <form method="post" action="change_password.php" class="form">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

            <label class="field">
                <span class="field-label">Current password</span>
                <input type="password" name="current_password" required>
            </label>

            <label class="field">
                <span class="field-label">New password</span>
                <input type="password" name="new_password" required>
            </label>

            <label class="field">
                <span class="field-label">Confirm new password</span>
                <input type="password" name="confirm_password" required>
            </label>

            <section class="form-actions" style="margin-top:0.75rem;">
                <button type="submit" class="btn btn-primary">
                    Change password
                </button>
                <a href="users.php" class="btn btn-ghost">Cancel</a>
            </section>
        </form>
    </section>
</main>

<footer class="site-footer">
    <span>© <?= date('Y') ?> BoyeLeeNaga · CSS 305 Final Project</span>
</footer>

</body>
</html>
