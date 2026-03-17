<?php
/*
I certify that the PHP file I am submitting is all my own work.
None of it is copied from any source or any person.
Signed: 
Date: 12/06/2025
Class: CSS 305
File Name: part-create.php
Assignment: Final Project – Car Parts Catalog
Description: Create a new part with category and supplier relations.
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'session_check.php';
require 'db.php';
require 'csrf.php';

function h(?string $v): string {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$flash_error = '';
$flash_msg   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $flash_error = 'Invalid request token.';
    } else {
        $part_name   = trim($_POST['part_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = (float) ($_POST['price'] ?? 0);
        $quantity    = (int)   ($_POST['quantity'] ?? 0);
        $image_path  = trim($_POST['image_path'] ?? '');
        $category_id = (int)   ($_POST['category_id'] ?? 0);
        $supplier_id = (int)   ($_POST['supplier_id'] ?? 0);

        if ($part_name === '') {
            $flash_error = 'Part name is required.';
        } else {
            $stmt = $conn->prepare(
                'INSERT INTO parts
                 (part_name, description, price, quantity,
                  image_path, category_id, supplier_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            if (!$stmt) {
                die('Prepare failed: ' . $conn->error);
            }
            $stmt->bind_param(
                'ssdissi',
                $part_name,
                $description,
                $price,
                $quantity,
                $image_path,
                $category_id,
                $supplier_id
            );

            if ($stmt->execute()) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
                header('Location: catalog.php?msg=' .
                       urlencode('Part created.'));
                exit;
            } else {
                $flash_error =
                    'Create failed: ' . h($stmt->error);
            }
            $stmt->close();
        }
    }
}

/* Load categories & suppliers for dropdowns */
$categories = [];
$res = $conn->query('SELECT id, name FROM categories ORDER BY name');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $categories[] = $row;
    }
}

$suppliers = [];
$res = $conn->query('SELECT id, name FROM suppliers ORDER BY name');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $suppliers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create part – BoyeLeeNaga Shop</title>
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
        <a href="catalog.php" class="nav-link active">Catalog</a>
        <a href="suppliers.php" class="nav-link">Suppliers</a>
        <a href="users.php" class="nav-link">Users</a>
        <a href="logout.php" class="nav-link">Logout</a>
    </nav>
</header>

<main class="layout">

    <section class="hero">
        <h1 class="hero-title">Create new part</h1>
        <p class="hero-subtitle">
            Add a new part to the car parts catalog, including its price,
            stock quantity, category, and supplier.
        </p>

        <?php if ($flash_error): ?>
            <p class="panel-text" style="color:#b91c1c;"><?= h($flash_error) ?></p>
        <?php endif; ?>
    </section>

    <section class="panel panel-auth">
        <h2 class="panel-title">Part details</h2>
        <form method="post" action="part-create.php" class="form">
            <input type="hidden"
                   name="csrf_token"
                   value="<?= csrf_token() ?>">

            <label class="field">
                <span>Part name</span>
                <input type="text" name="part_name" required>
            </label>

            <label class="field">
                <span>Description</span>
                <input type="text" name="description">
            </label>

            <label class="field">
                <span>Price</span>
                <input type="number"
                       step="0.01"
                       min="0"
                       name="price">
            </label>

            <label class="field">
                <span>Quantity</span>
                <input type="number"
                       min="0"
                       name="quantity">
            </label>

            <label class="field">
                <span>Category</span>
                <select name="category_id">
                    <option value="0">None</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>">
                            <?= h($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Supplier</span>
                <select name="supplier_id">
                    <option value="0">None</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= (int)$s['id'] ?>">
                            <?= h($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <section class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Create part
                </button>
                <button type="button"
                        class="btn btn-ghost"
                        onclick="location.href='catalog.php'">
                    Cancel
                </button>
            </section>
        </form>
    </section>

</main>

<footer class="site-footer">
    <span>© BoyeLeeNaga · CSS 305 Final Project · Samuel Boye</span>
</footer>

</body>
</html>
