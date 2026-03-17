
<?php
/*  
    I certify that the html file I am submitting is all my own and group work. 
    None of it is copied from any source or any person. 
    Signed: Philip Lee
    Date: 12/09/2025
    Author: Philip Lee
    Date: 12/09/2025
    Class: CSS 305
    File Name: db.php
    Assignment: Final project
    Description: Login Form
*/

$host     = 'localhost';
$username = 'u431967787_Atwruz9hW_userLogin';     
$password = 'GrapeShot9000#';     
$database = 'u431967787_Atwruz9hW_login';     

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?>
