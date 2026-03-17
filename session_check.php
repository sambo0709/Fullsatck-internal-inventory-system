<?php
/*  
    I certify that the html file I am submitting is all my own and group work. 
    None of it is copied from any source or any person. 
    Signed: Samuel Boye
    Date: 12/09/2025
    Author: Samuel Boye
    Date: 12/09/2025
    Class: CSS 305
    File Name: db.php
    Assignment: Final project
    Description: Session check for admin and user pages
*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require login
if (empty($_SESSION['username'])) {
    header("Location: index.html");
    exit;
}

// Guarantee role is always set (for older users)
if (empty($_SESSION['role'])) {
    $_SESSION['role'] = "Tier1";
}
?>
