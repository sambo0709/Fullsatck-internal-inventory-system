
<?php
/*  
    I certify that the html file I am submitting is all my own work. 
    None of it is copied from any source or any person. 
    Signed: Philip Lee
    Date: 11/25/2025
    Author: Philip Lee
    Date: 11/25/2025
    Class: CSS 305
    File Name: index.html
    Description: Login Form
*/
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BoyeLeeNaga Shop - Car Parts Catalog</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="body">

<header class="site-header">

    <span class="brand">
        <span class="brand-mark"></span>
        <span class="brand-name">BoyeLeeNaga Shop</span>
    </span>

    <nav class="nav-links">
        <a href="index.php" class="nav-link active">Home</a>
        <a href="catalog.php" class="nav-link">Catalog</a>
        <a href="suppliers.php" class="nav-link">Suppliers</a>
        <a href="users.php" class="nav-link">Users</a>
    </nav>

</header>

<main class="layout">
    
<!-- left hero section-->
    <section class="hero">

        <h1 class="hero-title">Car Parts Catalog</h1>

        <p class="hero-subtitle">
            A clean, simple catalog for Boye Lee Naga's mechanic shop.
            Sign in to track inventory, view suppliers, and find the right
            part in seconds.
        </p>

    </section>

    <!-- RIGHT LOGIN CARD -->
    <section class="panel panel-login">

        <h2 class="panel-title">Log In</h2>
        <p class="panel-text">Sign in to manage parts and view supplier details.</p>

        <!-- HOW LOGIN ERROR MESSAGE IF EXISTS -->
        <?php if (!empty($_SESSION['login_error'])): ?>
            <p style="
                background:#fee2e2;
                color:#b91c1c;
                padding:0.75rem 1rem;
                border-radius:10px;
                font-weight:600;
                border:1px solid #fca5a5;
                margin-bottom:1rem;
            ">
                <?= htmlspecialchars($_SESSION['login_error']) ?>
            </p>
            <?php unset($_SESSION['login_error']); ?>
        <?php endif; ?>

        <form method="post" action="login.php" class="form">

            <label class="field">
                <span class="field-label">Username</span>
                <input type="text" name="username" autocomplete="username" required>
            </label>

            <label class="field">
                <span class="field-label">Password</span>
                <input type="password" name="password" autocomplete="current-password" required>
            </label>

            <section class="form-actions">
                <button type="submit" class="btn btn-primary full">Sign In</button>
            </section>

            <section class="form-secondary-actions">
                <button type="button"
                        class="link-button"
                        onclick="location.href='newUser.html'">
                    Create account
                </button>

                <button type="button"
                        class="link-button"
                        onclick="location.href='account_edit.php'">
                    Update account
                </button>
            </section>

        </form>

        <p class="panel-footnote">
            This system is for internal use at the mechanic shop as part of
            the CSS 305 final project.
        </p>

    </section>

</main>

<footer class="site-footer">
    <span>© BoyeLeeNaga · CSS 305 Final Project ·</span>
</footer>

</body>
</html>
