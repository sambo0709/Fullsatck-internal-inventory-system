<?php
/*
I certify that the PHP file I am submitting is all my own work.
None of it is copied from any source or any person.
Signed: Philip Lee
Date: 12/06/2025
Class: CSS 305
File Name: userUpdate.php
Assignment: Final Project – Car Parts Catalog
Description: Updates an existing user's username, email, and (admin-only) role.
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'session_check.php';
require 'db.php';

$isAdmin = ($_SESSION['role'] ?? '') === 'Admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $currentName  = trim($_POST['currentUser'] ?? '');
    $newName      = trim($_POST['newUser'] ?? '');
    $currentEmail = trim($_POST['currentEmail'] ?? '');
    $newEmail     = trim($_POST['newEmail'] ?? '');
    $currentRole  = trim($_POST['currentRole'] ?? '');
    $newRole      = trim($_POST['newRole'] ?? '');

    if ($currentName === '' || $currentEmail === '') {
        $_SESSION['error'] = "Current username and email are required.";
        header("Location: account_edit.php");
        exit;
    }

    $fields = [];
    $params = [];
    $types  = '';

    if ($newName !== '') {
        $fields[] = "username = ?";
        $params[] = $newName;
        $types   .= 's';
    }

    if ($newEmail !== '') {
        $fields[] = "email = ?";
        $params[] = $newEmail;
        $types   .= 's';
    }

    // Role only allowed for admins
    if ($isAdmin && $newRole !== '') {
        $fields[] = "role = ?";
        $params[] = $newRole;
        $types   .= 's';
    }

    if (empty($fields)) {
        $_SESSION['error'] = "No new values to update.";
        header("Location: account_edit.php");
        exit;
    }

    $sql = "UPDATE users SET " . implode(', ', $fields) .
           " WHERE username = ? AND email = ?";

    $params[] = $currentName;
    $params[] = $currentEmail;
    $types   .= 'ss';

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: account_edit.php");
        exit;
    }

    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {

        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "Account updated successfully!";

            // If the user updated their own username, update session too
            if (isset($_SESSION['username']) &&
                $_SESSION['username'] === $currentName &&
                $newName !== '') {
                $_SESSION['username'] = $newName;
            }

        } else {
            $_SESSION['error'] = "No matching user found or no changes applied.";
        }
    } else {
        $_SESSION['error'] = "Database error: " . $stmt->error;
    }

    header("Location: account_edit.php");
    exit;

} else {
    header("Location: account_edit.php");
    exit;
}
?>
