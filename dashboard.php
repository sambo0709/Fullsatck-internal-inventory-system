<?php
/*
I certify that the PHP file I am submitting is all my own and group work.
None of it is copied from any source or any person.
Signed: Philip Lee
Date: 12/06/2025
Class: CSS 305
File Name: dashBoardAdmin.php
Assignment: Final Project 
Description: Admin interface not for any other roles like Tier1 and Tier 2
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'session_check.php';
require 'db.php';

// Default counts
$partsCount     = 0;
$suppliersCount = 0;
$usersCount     = 0;

// Count parts
if ($result = $conn->query("SELECT COUNT(*) AS cnt FROM parts")) {
    $row        = $result->fetch_assoc();
    $partsCount = (int)($row['cnt'] ?? 0);
    $result->free();
}

// Count suppliers
if ($result = $conn->query("SELECT COUNT(*) AS cnt FROM suppliers")) {
    $row            = $result->fetch_assoc();
    $suppliersCount = (int)($row['cnt'] ?? 0);
    $result->free();
}

// Count users
if ($result = $conn->query("SELECT COUNT(*) AS cnt FROM users")) {
    $row        = $result->fetch_assoc();
    $usersCount = (int)($row['cnt'] ?? 0);
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BoyeLeeNaga Shop – Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="body">

<header class="site-header">
    <span class="brand">
        <span class="brand-mark"></span>
        <span class="brand-name">BoyeLeeNaga Shop</span>
    </span>

    <nav class="nav-links">
        <a href="dashboard.php" class="nav-link active">Home</a>
        <a href="catalog.php" class="nav-link">Catalog</a>
        <a href="suppliers.php" class="nav-link">Suppliers</a>
        <a href="users.php" class="nav-link">Users</a>
        <a href="logout.php" class="nav-link">Logout</a>
    </nav>
</header>

<main class="layout">

    <!-- LEFT SIDE: Welcome hero -->
    <section class="hero">
        <h1 class="hero-title">
            Welcome back,
            <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!
        </h1>

        <p class="hero-subtitle">
            This is our internal dashboard for the BoyeLeeNaga mechanic shop.
            From here you can quickly jump into the parts catalog, view suppliers,
            or manage user accounts.
        </p>

        <section class="hero-stats">

            <section class="stat">
                <span class="stat-number"><?= $partsCount ?></span>
                <span class="stat-label">Parts in catalog</span>
            </section>

            <section class="stat">
                <span class="stat-number"><?= $suppliersCount ?></span>
                <span class="stat-label">Suppliers</span>
            </section>

            <section class="stat">
                <span class="stat-number"><?= $usersCount ?></span>
                <span class="stat-label">Users</span>
            </section>

        </section>
    </section>

    <!-- RIGHT SIDE: Quick links panel -->
    <section class="panel panel-table parts-panel">
        <h2 class="panel-title">Quick actions</h2>
        <p class="panel-text">
            Use these shortcuts to move around the internal system.
        </p>

     
        <section class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Description</th>
                        <th style="text-align:right;">Go</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Browse parts catalog</td>
                        <td>View all parts, quantities, and suppliers.</td>
                        <td style="text-align:right;">
                            <a href="catalog.php" class="link-button">Open catalog</a>
                        </td>
                    </tr>
                    <tr>
                        <td>View suppliers</td>
                        <td>See supplier contact info and part counts.</td>
                        <td style="text-align:right;">
                            <a href="suppliers.php" class="link-button">Open suppliers</a>
                        </td>
                    </tr>
                    <tr>
                        <td>Manage users</td>
                        <td>Review internal accounts and roles.</td>
                        <td style="text-align:right;">
                            <a href="users.php" class="link-button">Open users</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </section>

</main>

<footer class="site-footer">
    <span>© BoyeLeeNaga · CSS 305 Final Project ·</span>
</footer>

</body>
</html>
