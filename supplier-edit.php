<?php
/*
I certify that the PHP file I am submitting is all my own work.
None of it is copied from any source or any person.
Signed: 
Date: 12/06/2025
Class: CSS 305
File Name: supplier-edit.php
Assignment: Final Project – Car Parts Catalog
Description: Edit (update) an existing supplier.
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'session_check.php';
require 'db.php';
require 'csrf.php';

function h(?string $v): string {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$id    = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error = '';
$info  = '';
$supplier = null;

if ($id <= 0) {
    $error = 'Invalid supplier ID.';
} else {
    $stmt = $conn->prepare(
        'SELECT id, name, contact_name, phone, email
         FROM suppliers WHERE id = ?'
    );
    if (!$stmt) {
        die('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $supplier = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$supplier) {
        $error = 'Supplier not found.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {

    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request token.';
    } else {
        $name         = trim($_POST['name'] ?? '');
        $contact_name = trim($_POST['contact_name'] ?? '');
        $phone        = trim($_POST['phone'] ?? '');
        $email_raw    = trim($_POST['email'] ?? '');
        $email        = $email_raw !== '' ?
            filter_var($email_raw, FILTER_VALIDATE_EMAIL) : null;

        if ($name === '') {
            $error = 'Supplier name is required.';
        } elseif ($email_raw !== '' && $email === false) {
            $error = 'Email is not valid.';
        } else {
            $upd = $conn->prepare(
                'UPDATE suppliers
                 SET name = ?, contact_name = ?, phone = ?, email = ?
                 WHERE id = ?'
            );
            if (!$upd) {
                die('Prepare failed: ' . $conn->error);
            }
            $upd->bind_param(
                'ssssi',
                $name,
                $contact_name,
                $phone,
                $email,
                $id
            );

            if ($upd->execute()) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
                header('Location: suppliers.php?msg=' .
                       urlencode('Supplier updated.'));
                exit;
            } else {
                $error = 'Update failed: ' . h($upd->error);
            }
            $upd->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit supplier – BoyeLeeNaga Shop</title>
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
        <a href="suppliers.php" class="nav-link active">Suppliers</a>
        <a href="users.php" class="nav-link">Users</a>
        <a href="logout.php" class="nav-link">Logout</a>
    </nav>
</header>

<main class="layout">

    <section class="hero">
        <h1 class="hero-title">Edit supplier</h1>
        <p class="hero-subtitle">
            Update contact information and email for an existing supplier.
        </p>

        <?php if ($error): ?>
            <p class="panel-text" style="color:#b91c1c;"><?= h($error) ?></p>
        <?php endif; ?>
    </section>

    <section class="panel panel-auth">
        <?php if ($supplier): ?>
            <h2 class="panel-title"><?= h($supplier['name']) ?></h2>
            <p class="panel-text">
                Make your changes below and click <strong>Save</strong>.
            </p>

            <form method="post" action="supplier-edit.php?id=<?= (int)$id ?>"
                  class="form">
                <input type="hidden"
                       name="csrf_token"
                       value="<?= csrf_token() ?>">

                <label class="field">
                    <span>Supplier name</span>
                    <input type="text"
                           name="name"
                           required
                           value="<?= h($supplier['name']) ?>">
                </label>

                <label class="field">
                    <span>Contact person</span>
                    <input type="text"
                           name="contact_name"
                           value="<?= h($supplier['contact_name']) ?>">
                </label>

                <label class="field">
                    <span>Phone</span>
                    <input type="text"
                           name="phone"
                           value="<?= h($supplier['phone']) ?>">
                </label>

                <label class="field">
                    <span>Email</span>
                    <input type="email"
                           name="email"
                           value="<?= h($supplier['email']) ?>">
                </label>

                <section class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        Save changes
                    </button>
                    <button type="button"
                            class="btn btn-ghost"
                            onclick="location.href='suppliers.php'">
                        Cancel
                    </button>
                </section>
            </form>
        <?php else: ?>
            <p>Supplier not found.</p>
        <?php endif; ?>
    </section>

</main>

<footer class="site-footer">
    <span>© BoyeLeeNaga · CSS 305 Final Project · Samuel Boye</span>
</footer>

</body>
</html>
