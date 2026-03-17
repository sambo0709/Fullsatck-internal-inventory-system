<?php
/*
I certify that the PHP file I am submitting is all my own work.
None of it is copied from any source or any person.
Signed:
Date: 12/06/2025
Class: CSS 305
File Name: part-delete.php
Assignment: Final Project – Car Parts Catalog
Description: Delete an existing part using CSRF-protected POST.
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'session_check.php';
require 'db.php';
require 'csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: catalog.php');
    exit;
}

if (!csrf_check($_POST['csrf_token'] ?? null)) {
    header('Location: catalog.php?err=' .
           urlencode('Invalid request token.'));
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    header('Location: catalog.php?err=' .
           urlencode('Invalid part ID.'));
    exit;
}

$stmt = $conn->prepare('DELETE FROM parts WHERE id = ?');
if (!$stmt) {
    header('Location: catalog.php?err=' .
           urlencode('Prepare failed.'));
    exit;
}
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    header('Location: catalog.php?msg=' .
           urlencode('Part deleted.'));
} else {
    header('Location: catalog.php?err=' .
           urlencode('Delete failed.'));
}
$stmt->close();
exit;
