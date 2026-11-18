<?php
include('db_connect.php');

$student_number = $_GET['student_number'] ?? '';

if ($student_number) {
    $sql = "SELECT * FROM sign_up WHERE student_number='$student_number' ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login Confirmation</title>
<style>
  body { font-family: 'Rajdhani', sans-serif; background:#174b3b; color:#fff; display:flex; justify-content:center; align-items:center; height:100vh; flex-direction:column; text-align:center;}
  h1 { color:#f7b500; margin-bottom:20px;}
  a { color:#fff; background-color:#f7b500; padding:10px 20px; border-radius:6px; text-decoration:none; font-weight:bold;}
  a:hover { background-color:#d29b00;}
</style>
</head>
<body>
<h1>Login Successful </h1>
<?php if (!empty($row)) : ?>
  <p><strong>First Name:</strong> <?= htmlspecialchars($row['first_name']) ?><br></p>
<?php endif; ?>
<a href="student_dashboard.php ">Go to Dashboard</a>
</body>
</html>
    