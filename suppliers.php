<?php
/*
I certify that the PHP file I am submitting is all my own work.
None of it is copied from any source or any person.
Signed:Samuel Boye
Date: 12/06/2025
Class: CSS 305
File Name: suppliers.php
Assignment: Final Project – Car Parts Catalog
Description: Suppliers CRUD page (create, list, delete) with CSRF protection.
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'session_check.php';
require 'db.php';
require 'csrf.php';

$flash_msg   = '';
$flash_error = '';

/**
 * Small HTML escape helper.
 */
function h(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check CSRF on *any* modifying action
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $flash_error = 'Invalid request token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        /* ========== CREATE SUPPLIER ========== */
        if ($action === 'create') {
            $name         = trim($_POST['name'] ?? '');
            $contact_name = trim($_POST['contact_name'] ?? '');
            $phone        = trim($_POST['phone'] ?? '');
            $email_raw    = trim($_POST['email'] ?? '');
            $email        = $email_raw !== '' ?
                filter_var($email_raw, FILTER_VALIDATE_EMAIL) : null;

            if ($name === '') {
                $flash_error = 'Supplier name is required.';
            } elseif ($email_raw !== '' && $email === false) {
                $flash_error = 'Email address is not valid.';
            } else {
                // Check for duplicate name
                $dup = $conn->prepare(
                    'SELECT COUNT(*) AS cnt FROM suppliers WHERE name = ?'
                );
                $dup->bind_param('s', $name);
                $dup->execute();
                $dup_res = $dup->get_result()->fetch_assoc();
                $dup->close();

                if (!empty($dup_res['cnt']) && (int)$dup_res['cnt'] > 0) {
                    $flash_error = 'A supplier with that name already exists.';
                } else {
                    $stmt = $conn->prepare(
                        'INSERT INTO suppliers (name, contact_name, phone, email)
                         VALUES (?, ?, ?, ?)'
                    );
                    $stmt->bind_param(
                        'ssss',
                        $name,
                        $contact_name,
                        $phone,
                        $email
                    );

                    if ($stmt->execute()) {
                        $flash_msg = 'Supplier created successfully.';
                        // rotate token after successful change
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
                    } else {
                        $flash_error =
                            'Failed to create supplier: ' .
                            h($stmt->error);
                    }
                    $stmt->close();
                }
            }
        }

        /* ========== DELETE SUPPLIER ========== */
        if ($action === 'delete') {
            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

            if ($id <= 0) {
                $flash_error = 'Invalid supplier ID.';
            } else {
                // Check if any parts still reference this supplier
                $check = $conn->prepare(
                    'SELECT COUNT(*) AS cnt FROM parts WHERE supplier_id = ?'
                );
                $check->bind_param('i', $id);
                $check->execute();
                $row = $check->get_result()->fetch_assoc();
                $check->close();

                if (!empty($row['cnt']) && (int)$row['cnt'] > 0) {
                    $flash_error =
                        'Cannot delete supplier with existing parts.';
                } else {
                    $del = $conn->prepare(
                        'DELETE FROM suppliers WHERE id = ?'
                    );
                    $del->bind_param('i', $id);

                    if ($del->execute()) {
                        $flash_msg = 'Supplier deleted successfully.';
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
                    } else {
                        $flash_error =
                            'Delete failed: ' . h($del->error);
                    }
                    $del->close();
                }
            }
        }
    }
}

/* ========== FETCH SUPPLIERS WITH PART COUNTS ========== */
$sql = "
    SELECT s.id,
           s.name,
           s.contact_name,
           s.phone,
           s.email,
           COUNT(p.id) AS part_count
    FROM suppliers s
    LEFT JOIN parts p ON p.supplier_id = s.id
    GROUP BY s.id, s.name, s.contact_name, s.phone, s.email
    ORDER BY s.name
";

$result    = $conn->query($sql);
$suppliers = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BoyeLeeNaga Shop – Suppliers</title>
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

    <!-- LEFT: Hero -->
    <section class="hero">
        <h1 class="hero-title">Suppliers</h1>
        <p class="hero-subtitle">
            Manage the companies that provide parts for the BoyeLeeNaga
            catalog. Create new suppliers and remove ones that are no
            longer used.
        </p>

        <?php if ($flash_msg): ?>
            <p class="panel-text" style="color: #059669;">
                <?= h($flash_msg) ?>
            </p>
        <?php endif; ?>

        <?php if ($flash_error): ?>
            <p class="panel-text" style="color: #b91c1c;">
                <?= h($flash_error) ?>
            </p>
        <?php endif; ?>
    </section>

    <!-- RIGHT: Form + table -->
    <section class="panel suppliers-panel">

        <h2 class="panel-title">Add supplier</h2>
        <p class="panel-text">
            Fill in the details below to add a new supplier to the system.
        </p>

        <form method="post" action="suppliers.php" class="form">
            <input type="hidden"
                   name="csrf_token"
                   value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="create">

            <label class="field">
                <span>Supplier name</span>
                <input type="text" name="name" required>
            </label>

            <label class="field">
                <span>Contact person</span>
                <input type="text" name="contact_name">
            </label>

            <label class="field">
                <span>Phone</span>
                <input type="text" name="phone">
            </label>

            <label class="field">
                <span>Email</span>
                <input type="email" name="email">
            </label>

            <section class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Create supplier
                </button>
            </section>
        </form>

        <h2 class="panel-title" style="margin-top: 2rem;">All suppliers</h2>
        <p class="panel-text">
            Each supplier shows the number of parts currently assigned in
            the catalog.
        </p>

        <?php if (empty($suppliers)): ?>
            <p>No suppliers found.</p>
        <?php else: ?>

            <section class="table-wrapper">
                <table>
                    <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Contact</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th># of Parts</th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($suppliers as $row): ?>
                        <tr>
                            <td><?= h($row['name']) ?></td>
                            <td><?= h($row['contact_name']) ?></td>
                            <td><?= h($row['phone']) ?></td>
                            <td><?= h($row['email']) ?></td>
                            <td style="text-align:center;">
                                <?= (int) $row['part_count'] ?>
                            </td>
                            <td style="text-align:right;">
                                <a href="supplier-edit.php?id=<?= (int)$row['id'] ?>"
                                   class="link-button">
                                    Edit
                                </a>
                            </td>
                            <td style="text-align:right;">
                                <form method="post"
                                      action="suppliers.php"
                                      onsubmit="return confirm('Delete this supplier?');">
                                    <input type="hidden"
                                           name="csrf_token"
                                           value="<?= csrf_token() ?>">
                                    <input type="hidden"
                                           name="action"
                                           value="delete">
                                    <input type="hidden"
                                           name="id"
                                           value="<?= (int)$row['id'] ?>">
                                    <button type="submit"
                                            class="btn btn-ghost">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>

    </section>

</main>

<footer class="site-footer">
    <span>© BoyeLeeNaga · CSS 305 Final Project · Samuel Boye</span>
</footer>

</body>
</html>
