<?php
 /*  
    I certify that the html file I am submitting is all my own and group work. 
    None of it is copied from any source or any person. 
    Signed: Philip Lee
    Date: 12/10/2025
    Author: Philip Lee
    Date: 12/09/2025
    Class: CSS 305
    File Name: newUser.php
    Assignment: Final project
    Description: Inserts Username, Pass and Email. Default Employee ROle is Tier 1
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim(htmlspecialchars($_POST['newUser'] ?? ''));
    $newPass = $_POST['newPass'] ?? '';   // allow ANY characters

    if ($name === '' || $newPass === '') {
        echo "Username and password are required.";
        exit;
    }

    // Hash the password securely
    $hashedPassword = password_hash($newPass, PASSWORD_DEFAULT);

    // No email field in the form yet, so store empty string
    $email = trim(htmlspecialchars($_POST['newEmail'] ?? ''));

    $sql = "INSERT INTO users (username, email, password, role)
            VALUES (?, ?, ?, 'Tier1')";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    if ($stmt->execute()) {
        header("Location: index.html");   // back to login form
        exit;
    } else {
        echo "Database error: " . $stmt->error;
    }

} else {
    header("Location: newUser.html");
    exit;
}
?>
