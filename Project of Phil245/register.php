<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "health_project";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get and sanitize form data
    $first_name = mysqli_real_escape_string($conn, trim($_POST['fname']));
    $middle_name = mysqli_real_escape_string($conn, trim($_POST['2name']));
    $last_name = mysqli_real_escape_string($conn, trim($_POST['lname']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $birth_date = mysqli_real_escape_string($conn, $_POST['bDate']);
    $civil_id = mysqli_real_escape_string($conn, trim($_POST['Civil-ID']));
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $terms = $_POST['terms'];
    
    // Convert terms to 1 or 0
    $tos = ($terms === 'agree') ? 1 : 0;
    
    // Validation errors array
    $errors = [];
    
    // Check if terms agreed
    if ($terms !== 'agree') {
        $errors[] = "You must agree to the terms and conditions";
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Validate password strength (at least 6 characters)
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    // Validate Civil ID (12 digits)
    if (!preg_match('/^\d{12}$/', $civil_id)) {
        $errors[] = "Civil ID must be exactly 12 digits";
    }
    
    // Check if email already exists
    $check_email = "SELECT civil_ID FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $check_email);
    if (mysqli_num_rows($result) > 0) {
        $errors[] = "This email is already registered";
    }
    
    // Check if Civil ID already exists
    $check_civil = "SELECT civil_ID FROM users WHERE civil_ID = '$civil_id'";
    $result = mysqli_query($conn, $check_civil);
    if (mysqli_num_rows($result) > 0) {
        $errors[] = "This Civil ID is already registered";
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert into database
        $sql = "INSERT INTO users (first_name, middle_name, last_name, email, password, date, civil_ID, Role, TOS) 
                VALUES ('$first_name', '$middle_name', '$last_name', '$email', '$hashed_password', '$birth_date', '$civil_id', '$role', $tos)";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['registration_success'] = true;
            $_SESSION['registered_email'] = $email;
            $_SESSION['success_message'] = "Registration successful! You can now log in.";
            mysqli_close($conn);
            header("Location: login.html");
            exit();
        } else {
            $errors[] = "Registration failed: " . mysqli_error($conn);
        }
    }
    
    // If there are errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['registration_errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        mysqli_close($conn);
        header("Location: signup.html");
        exit();
    }
}

mysqli_close($conn);
?>