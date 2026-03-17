<?php
/*
I certify that the PHP file I am submitting is all my own work.
None of it is copied from any source or any person.
Signed: 
Date: 12/10/2025
Class: CSS 305
File Name: logout.php
Assignment: Final Project 
Description: Logs out the current user by destroying the session.
*/

session_start();

// Destroy all session data
$_SESSION = [];
session_unset();
session_destroy();

// Redirect back to login page
header("Location: index.html");
exit;
?>
