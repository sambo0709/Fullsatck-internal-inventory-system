<?php
/*
I certify that the PHP file I am submitting is all my own work.
None of it is copied from any source or any person.
Signed: Samuel Boye
Date: 12/06/2025
Class: CSS 305
File Name: catalog.php
Assignment: Final Project – Car Parts Catalog
Description: Main catalog page listing all car parts with search and filtering.
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'session_check.php';
require 'db.php';
require 'csrf.php';   // for Delete button tokens

// Optional messages (?msg= / ?err= in query string)
$flash_msg   = isset($_GET['msg']) ? trim($_GET['msg']) : '';
$flash_error = isset($_GET['err']) ? trim($_GET['err']) : '';

// Read optional supplier filter
$supplierId = isset($_GET['supplier_id']) ? (int) $_GET['supplier_id'] : 0;

// Read search value
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// Base SQL
$sql = "
    SELECT p.id,
           p.part_name,
           p.price,
           p.quantity,
           c.name AS category_name,
           s.name AS supplier_name
    FROM parts p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN suppliers s  ON p.supplier_id = s.id
    WHERE 1=1
";

// Add supplier filter if provided
$params = [];
$types  = "";

if ($supplierId > 0) {
    $sql      .= " AND p.supplier_id = ?";
    $params[]  = $supplierId;
    $types    .= "i";
}

// Add search filter if not empty
if ($search !== "") {
    $sql      .= " AND p.part_name LIKE ?";
    $params[]  = "%" . $search . "%";
    $types    .= "s";
}

$sql .= " ORDER BY p.part_name";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$parts  = [];

while ($row = $result->fetch_assoc()) {
    $parts[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BoyeLeeNaga Shop – Catalog</title>
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

    <!-- LEFT: page intro -->
    <section class="hero">
        <h1 class="hero-title">Parts Catalog</h1>
        <p class="hero-subtitle">
            Browse all available parts for the shop. Select a part to see more
            details, including price, quantity, supplier, and category.
        </p>

        <?php if ($flash_msg): ?>
            <p class="alert success">
                <?= htmlspecialchars($flash_msg) ?>
            </p>
        <?php endif; ?>

        <?php if ($flash_error): ?>
            <p class="alert error">
                <?= htmlspecialchars($flash_error) ?>
            </p>
        <?php endif; ?>
    </section>

    <!-- RIGHT: dashboard-style card with table -->
    <section class="panel panel-table parts-panel">

        <h2 class="panel-title">Available Parts</h2>
        <p class="panel-text">Click a button to view details or manage a part.</p>

        <!-- New part button -->
        <p style="text-align:right; margin:0 0 0.75rem 0;">
            <a href="part-create.php" class="btn btn-primary small">
                + Add Part
            </a>
        </p>

        <!-- 🔍 SEARCH BAR + CLEAR BUTTON -->
        <form method="get" action="catalog.php"
              style="margin-bottom: 1rem; display:flex; gap:0.6rem; align-items:center;">

            <!-- Keep supplier filter if applied -->
            <?php if ($supplierId > 0): ?>
                <input type="hidden" name="supplier_id"
                       value="<?= (int)$supplierId ?>">
            <?php endif; ?>

            <input type="text"
                   name="search"
                   placeholder="Search parts..."
                   value="<?= htmlspecialchars($search) ?>"
                   style="padding:0.55rem 1rem; border-radius:10px;
                          border:1px solid #d1d5db; width:240px; background:white;">

            <button type="submit" class="btn btn-primary small">Search</button>

            <!-- RESET SEARCH & SUPPLIER FILTER -->
            <a href="catalog.php" class="btn btn-ghost small">Clear</a>
        </form>


        <!-- TABLE -->
        <section class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>Part</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th style="width:220px;">Actions</th>
                </tr>
                </thead>

                <tbody>
                <?php if (empty($parts)): ?>
                    <tr>
                        <td colspan="6">No parts found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($parts as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['part_name']) ?></td>
                            <td><?= htmlspecialchars($row['category_name']) ?></td>
                            <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                            <td>$<?= number_format($row['price'], 2) ?></td>
                            <td><?= (int)$row['quantity'] ?></td>
                            <td style="display:flex; gap:0.45rem; flex-wrap:wrap;">

                                <!-- Details -->
                                <a href="parts-details.php?id=<?= (int)$row['id'] ?>"
                                   class="link-button">
                                    Details
                                </a>

                                <!-- Edit -->
                                <a href="part-edit.php?id=<?= (int)$row['id'] ?>"
                                   class="link-button">
                                    Edit
                                </a>

                                <!-- Delete (POST + CSRF) -->
                                <form method="post"
                                      action="part-delete.php"
                                      onsubmit="return confirm('Delete this part?');">
                                    <input type="hidden" name="id"
                                           value="<?= (int)$row['id'] ?>">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <button type="submit"
                                            class="btn btn-danger small">
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

</main>

<footer class="site-footer">
    <span>© <?= date('Y') ?> BoyeLeeNaga · CSS 305 Final Project</span>
</footer>

</body>
</html>
