<?php
/*
I certify that the PHP file I am submitting is all my own work.
None of it is copied from any source or any person.
Signed: 
Date: 12/06/2025
Class: CSS 305
File Name: part-edit.php
Assignment: Final Project – Car Parts Catalog
Description: Edit (update) an existing part.
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
$part  = null;

if ($id <= 0) {
    $error = 'Invalid part ID.';
} else {
    $sql = "
        SELECT id, part_name, description, price, quantity,
               image_path, category_id, supplier_id
        FROM parts
        WHERE id = ?
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $part = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$part) {
        $error = 'Part not found.';
    }
}

/* Load dropdown data */
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {

    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request token.';
    } else {
        $part_name   = trim($_POST['part_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = (float) ($_POST['price'] ?? 0);
        $quantity    = (int)   ($_POST['quantity'] ?? 0);
        $image_path  = trim($_POST['image_path'] ?? '');
        $category_id = (int)   ($_POST['category_id'] ?? 0);
        $supplier_id = (int)   ($_POST['supplier_id'] ?? 0);

        if ($part_name === '') {
            $error = 'Part name is required.';
        } else {
            $upd = $conn->prepare(
                'UPDATE parts
                 SET part_name = ?, description = ?, price = ?,
                     quantity = ?, image_path = ?,
                     category_id = ?, supplier_id = ?
                 WHERE id = ?'
            );
            if (!$upd) {
                die('Prepare failed: ' . $conn->error);
            }
            $upd->bind_param(
                'ssdissii',
                $part_name,
                $description,
                $price,
                $quantity,
                $image_path,
                $category_id,
                $supplier_id,
                $id
            );

            if ($upd->execute()) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
                header('Location: catalog.php?msg=' .
                       urlencode('Part updated.'));
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
    <title>Edit part – BoyeLeeNaga Shop</title>
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
        <h1 class="hero-title">Edit part</h1>
        <p class="hero-subtitle">
            Change the price, quantity, description, or supplier for this part.
        </p>

        <?php if ($error): ?>
            <p class="panel-text" style="color:#b91c1c;"><?= h($error) ?></p>
        <?php endif; ?>
    </section>

    <section class="panel panel-auth">
        <?php if ($part): ?>
            <h2 class="panel-title"><?= h($part['part_name']) ?></h2>

            <form method="post" action="part-edit.php?id=<?= (int)$id ?>"
                  class="form">
                <input type="hidden"
                       name="csrf_token"
                       value="<?= csrf_token() ?>">

                <label class="field">
                    <span>Part name</span>
                    <input type="text"
                           name="part_name"
                           required
                           value="<?= h($part['part_name']) ?>">
                </label>

                <label class="field">
                    <span>Description</span>
                    <input type="text"
                           name="description"
                           value="<?= h($part['description']) ?>">
                </label>

                <label class="field">
                    <span>Price</span>
                    <input type="number"
                           step="0.01"
                           min="0"
                           name="price"
                           value="<?= h((string)$part['price']) ?>">
                </label>

                <label class="field">
                    <span>Quantity</span>
                    <input type="number"
                           min="0"
                           name="quantity"
                           value="<?= h((string)$part['quantity']) ?>">
                </label>

                <label class="field">
                    <span>Category</span>
                    <select name="category_id">
                        <option value="0">None</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int)$c['id'] ?>"
                                <?= $c['id'] == $part['category_id'] ? 'selected' : '' ?>>
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
                            <option value="<?= (int)$s['id'] ?>"
                                <?= $s['id'] == $part['supplier_id'] ? 'selected' : '' ?>>
                                <?= h($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <section class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        Save changes
                    </button>
                    <button type="button"
                            class="btn btn-ghost"
                            onclick="location.href='catalog.php'">
                        Cancel
                    </button>
                </section>
            </form>
        <?php else: ?>
            <p>Part not found.</p>
        <?php endif; ?>
    </section>

</main>

<footer class="site-footer">
    <span>© BoyeLeeNaga · CSS 305 Final Project · Samuel Boye</span>
</footer>

</body>
</html>
