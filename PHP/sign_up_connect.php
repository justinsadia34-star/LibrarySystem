<?php
include('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect all form inputs
    $student_number = $_POST['student_number'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $section = $_POST['section'];
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_initial = $_POST['middle_initial'];
    $suffix = $_POST['suffix'];
    $birthday = $_POST['birthday'];
    $sex = $_POST['sex'];
    $contact_no = $_POST['contact_no'];
    $home_address = $_POST['home_address'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate passwords
    if ($password !== $confirm_password) {
        die("<h3 style='color:red; text-align:center;'>❌ Passwords do not match!</h3>");
    }

    // Hash password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if student or email already exists
    $check_sql = "SELECT * FROM students WHERE student_number='$student_number' OR email='$email'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo "<h3 style='color:red; text-align:center;'>⚠️ Student number or email already registered!</h3>";
    } else {
        // Insert new record
        $sql = "INSERT INTO students
        (student_number, course, year_level, section, last_name, first_name, middle_initial, suffix, birthday, sex, contact_no, home_address, email, password, confirm_password)
        VALUES 
        ('$student_number', '$course', '$year_level', '$section', '$last_name', '$first_name', '$middle_initial', '$suffix', '$birthday', '$sex', '$contact_no', '$home_address', '$email', '$hashed_password', '$hashed_password')";

        if ($conn->query($sql) === TRUE) {
            echo "<h2 style='color:green; text-align                      :center;'>✅ Registration Successful!</h2>";
            echo "<p style='text-align:center;'><a href='login.php' style='color:#174b3b; font-weight:bold;'>Back to Login</a></p>";
        } else {
            echo "<h3 style='color:red; text-align:center;'>Error: " . $conn->error . "</h3>";
        }
    }
}                                                                      

$conn->close();
?>
