<?php
/*
I certify that the PHP file I am submitting is all my own and group work.
None of it is copied from any source or any person.
Signed:Samuel boye
Date: 12/10/2025
Class: CSS 305
File Name: account_edit.php
Assignment: Final Project – Car Parts Catalog
Description: Account update page.
             - Admins can update any user's username, email, and role.
             - Non-admins can update only their own username and email.
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'session_check.php';   // checks login + starts session
require 'db.php';

function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$isAdmin   = ($_SESSION['role'] ?? '') === 'Admin';
$userId    = $_SESSION['user_id'] ?? 0;

// For non-admins, load their own account info
$currentUser = null;
if (!$isAdmin && $userId) {
    $stmt = $conn->prepare("SELECT username, email, role FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $currentUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update account – BoyeLeeNaga Shop</title>
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
        <a href="catalog.php"   class="nav-link">Catalog</a>
        <a href="suppliers.php" class="nav-link">Suppliers</a>
        <a href="users.php"     class="nav-link active">Users</a>
        <a href="logout.php"    class="nav-link">Logout</a>
    </nav>

</header>

<main class="layout">

    <!-- LEFT HERO SECTION -->
    <section class="hero">
        <?php if ($isAdmin): ?>
            <h1 class="hero-title">Update user account</h1>
            <p class="hero-subtitle">
                As an Admin, you can update any user's username, email, and role
                for this internal parts and suppliers system.
            </p>
        <?php else: ?>
            <h1 class="hero-title">Update my account</h1>
            <p class="hero-subtitle">
                You can update your own username and email. Role changes are
                managed by an administrator.
            </p>
        <?php endif; ?>
    </section>

    <!-- RIGHT UPDATE ACCOUNT CARD -->
    <section class="panel panel-auth">

        <h2 class="panel-title">Update account</h2>

        <!-- Inline client-side error message -->
        <div id="inlineError" class="alert error" style="display:none;">
            <!-- Filled by JavaScript if user submits with no new values -->
        </div>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert error">
                <?= h($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert success">
                <?= h($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if ($isAdmin): ?>

            <p class="panel-text">
                Enter the user's current information and any new values you want to save.
            </p>

            <!-- ADMIN FORM: can target any user + change role -->
            <form method="post" action="userUpdate.php" class="form" id="accountForm">

                <label class="field">
                    <span class="field-label">Current username</span>
                    <input type="text" name="currentUser" required>
                </label>

                <label class="field">
                    <span class="field-label">New username</span>
                    <input type="text" name="newUser">
                </label>

                <label class="field">
                    <span class="field-label">Current email</span>
                    <input type="email" name="currentEmail" required>
                </label>

                <label class="field">
                    <span class="field-label">New email</span>
                    <input type="email" name="newEmail">
                </label>

                <label class="field">
                    <span class="field-label">Current role (optional)</span>
                    <input type="text" name="currentRole" placeholder="e.g., Tier1, Tier2, Admin">
                </label>

                <label class="field">
                    <span class="field-label">New role</span>
                    <select name="newRole">
                        <option value="">Select role…</option>
                        <option value="Tier1">Tier1</option>
                        <option value="Tier2">Tier2</option>
                        <option value="Tier3">Tier3</option>
                        <option value="Admin">Admin</option>
                    </select>
                </label>

                <section class="form-actions">
                    <button type="submit" class="btn btn-primary full">
                        Update account
                    </button>
                </section>

                <section class="form-secondary-actions">
                    <button type="button"
                            class="link-button"
                            onclick="location.href='users.php'">
                        Back to Users
                    </button>
                </section>
            </form>

        <?php else: ?>

            <p class="panel-text">
                Update your own username and email. Your role is managed by an Admin.
            </p>

            <?php if ($currentUser): ?>
                <!-- NON-ADMIN FORM: only own username/email, no role fields -->
                <form method="post" action="userUpdate.php" class="form" id="accountForm">

                    <label class="field">
                        <span class="field-label">Current username</span>
                        <input type="text"
                               name="currentUser"
                               value="<?= h($currentUser['username']) ?>"
                               readonly>
                    </label>

                    <label class="field">
                        <span class="field-label">New username</span>
                        <input type="text"
                               name="newUser"
                               placeholder="Leave blank to keep current">
                    </label>

                    <label class="field">
                        <span class="field-label">Current email</span>
                        <input type="email"
                               name="currentEmail"
                               value="<?= h($currentUser['email']) ?>"
                               readonly>
                    </label>

                    <label class="field">
                        <span class="field-label">New email</span>
                        <input type="email"
                               name="newEmail"
                               placeholder="Leave blank to keep current">
                    </label>

                    <!-- No role inputs for non-admins -->

                    <section class="form-actions">
                        <button type="submit" class="btn btn-primary full">
                            Update my account
                        </button>
                    </section>

                    <section class="form-secondary-actions">
                        <button type="button"
                                class="link-button"
                                onclick="location.href='users.php'">
                            Back to Users
                        </button>
                    </section>
                </form>

                <p class="panel-text" style="margin-top:0.75rem;">
                    Current role: <strong><?= h($currentUser['role']) ?></strong>
                    (managed by an Admin)
                </p>

            <?php else: ?>
                <p>Unable to load your account information.</p>
            <?php endif; ?>

        <?php endif; ?>

    </section>

</main>

<footer class="site-footer">
    <span>© BoyeLeeNaga · CSS 305 Final Project ·</span>
</footer>

<!-- Simple client-side check so clicking submit with no new values shows a message -->
<script>
    (function () {
        const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;
        const form = document.getElementById('accountForm');
        const inlineError = document.getElementById('inlineError');

        if (!form || !inlineError) return;

        form.addEventListener('submit', function (e) {
            // Grab the "new" fields depending on role
            const newUser  = form.querySelector('input[name="newUser"]');
            const newEmail = form.querySelector('input[name="newEmail"]');
            const newRole  = form.querySelector('select[name="newRole"]');

            const newUserVal  = newUser  ? newUser.value.trim()  : "";
            const newEmailVal = newEmail ? newEmail.value.trim() : "";
            const newRoleVal  = (isAdmin && newRole) ? newRole.value.trim() : "";

            let empty = false;

            if (isAdmin) {
                // Admin: allow update if ANY of username, email, or role is provided
                empty = (newUserVal === "" && newEmailVal === "" && newRoleVal === "");
            } else {
                // Non-admin: only username/email
                empty = (newUserVal === "" && newEmailVal === "");
            }

            if (empty) {
                e.preventDefault();
                inlineError.style.display = "block";
                inlineError.textContent = "Please enter at least one new value before updating.";
            }
        });
    })();
</script>

</body>
</html>
