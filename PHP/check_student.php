
<?php
include('db_connect.php');

if (isset($_POST['student_number'])) {
    $student_number = $conn->real_escape_string($_POST['student_number']);
    $result = $conn->query("SELECT * FROM students WHERE student_number = '$student_number'");

    if ($result && $result->num_rows > 0) {
        $student = $result->fetch_assoc();
        echo json_encode(['success' => true, 'student' => $student]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
