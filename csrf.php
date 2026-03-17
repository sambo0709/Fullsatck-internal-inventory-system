<?php
/*
I certify that the PHP file I am submitting is all my own work.
None of it is copied from any source or any person.
Signed: 
Date: 12/06/2025
Class: CSS 305
File Name: csrf.php
Assignment: Final Project – Car Parts Catalog
Description: Minimal CSRF helper for forms.
*/

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Generate or return the current CSRF token.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a submitted CSRF token.
 */
function csrf_check(?string $token): bool {
    return isset($_SESSION['csrf_token']) &&
           $token !== null &&
           hash_equals($_SESSION['csrf_token'], $token);
}
