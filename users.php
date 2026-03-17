<?php
/*
I certify that the PHP file I am submitting is all my own work.
None of it is copied from any source or any person.
Signed: Philip Lee
Date: 12/06/2025
Class: CSS 305
File Name: users.php
Assignment: Final Project – Car Parts Catalog
Description: Displays user accounts. Admins can view/manage all users;
             non-admins only see their own account details.
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'session_check.php';
require 'db.php';
require 'csrf.php';

function h($v) { return htmlspecialchars($v ?? "", ENT_QUOTES, 'UTF-8'); }

$isAdmin       = ($_SESSION['role'] ?? 'Tier1') === 'Admin';
$currentUserId = $_SESSION['user_id'] ?? 0;

$users       = [];
$currentUser = null;

// Admin → get all users
if ($isAdmin) {
    $result = $conn->query("SELECT id, username, email, role FROM users ORDER BY username");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
} else {
    // Non-admin → only their own record
    if ($currentUserId) {
        $stmt = $conn->prepare("SELECT username, email, role FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $currentUserId);
        $stmt->execute();
        $currentUser = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>BoyeLeeNaga Shop – Users</title>
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
        <a href="catalog.php"  class="nav-link">Catalog</a>
        <a href="suppliers.php" class="nav-link">Suppliers</a>
        <a href="users.php"   class="nav-link active">Users</a>
        <a href="logout.php"  class="nav-link">Logout</a>
    </nav>
</header>

<main class="layout">

    <!-- HERO / INTRO -->
    <section class="hero">
        <?php if ($isAdmin): ?>
            <h1 class="hero-title">Users</h1>
            <p class="hero-subtitle">
                Manage all internal user accounts for the system.
            </p>

            <!-- Admin buttons -->
            <section style="display:flex; gap:0.6rem; margin-top:1rem; flex-wrap:wrap;">
                <a href="newUser.html" class="btn btn-primary">Create user</a>

                <!-- CHANGED: User.php → account_edit.php -->
                <a href="account_edit.php" class="btn btn-ghost">Update user</a>

                <a href="deleteUser.php" class="btn btn-ghost">Delete user</a>
                <a href="change_password.php" class="btn btn-ghost">
                    Change my password
                </a>
            </section>

        <?php else: ?>
            <h1 class="hero-title">My account</h1>
            <p class="hero-subtitle">
                View your account information and update your username or email.
            </p>
        <?php endif; ?>
    </section>

    <?php if ($isAdmin): ?>

        <!-- ADMIN VIEW: all users -->
        <section class="panel panel-table">
            <h2 class="panel-title">User list</h2>
            <p class="panel-text">
                This table shows all users who can sign in to manage parts and suppliers.
            </p>

            <section class="table-wrapper">
                <table>
                    <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="4">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= h($u['username']) ?></td>
                                <td><?= h($u['email']) ?></td>
                                <td><?= h($u['role']) ?></td>
                                <td>
                                    <form method="post"
                                          action="deleteUser.php"
                                          onsubmit="return confirm('Delete this user?');"
                                          style="display:inline;">
                                        <input type="hidden" name="username" value="<?= h($u['username']) ?>">
                                        <input type="hidden" name="email"    value="<?= h($u['email']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                        <button type="submit" class="btn btn-danger small">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </section>

    <?php else: ?>

        <!-- NON-ADMIN VIEW -->
        <section class="panel panel-login">
            <h2 class="panel-title">My account details</h2>

            <?php if ($currentUser): ?>
                <p><strong>Username:</strong> <?= h($currentUser['username']) ?></p>
                <p><strong>Email:</strong> <?= h($currentUser['email']) ?></p>
                <p><strong>Role:</strong> <?= h($currentUser['role']) ?></p>

                <p style="margin-top:1.25rem;">
                    <button type="button"
                            class="btn btn-primary"
                            onclick="location.href='account_edit.php'">
                        Update my information
                    </button>
                </p>

                <p style="margin-top:0.5rem;">
                    <button type="button"
                            class="btn btn-ghost"
                            onclick="location.href='change_password.php'">
                        Change my password
                    </button>
                </p>
            <?php else: ?>
                <p>Unable to load your account information.</p>
            <?php endif; ?>
        </section>

    <?php endif; ?>

</main>

<footer class="site-footer">
    <span>© BoyeLeeNaga · CSS 305 Final Project</span>
</footer>

</body>
</html>
