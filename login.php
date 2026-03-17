 <?php
 /*  
    I certify that the html file I am submitting is all my own and group work. 
    None of it is copied from any source or any person. 
    Signed: Philip Lee
    Date: 12/09/2025
    Author: Philip Lee
    Date: 12/09/2025
    Class: CSS 305
    File Name: login.php
    Assignment: Final project
    Description: Login Form
*/

session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && password_verify($password, $row['password'])) {

        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];

        header("Location: dashboard.php");
        exit;

    } else {

        // Save the error so index.html can display it
        $_SESSION['login_error'] = "Invalid username or password.";

        header("Location: index.html");
        exit;
    }
}
?>
