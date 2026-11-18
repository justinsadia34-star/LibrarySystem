<?php
session_start();
include('db_connect.php');

// Only allow librarian
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'librarian') {
    header("Location: login.php");
    exit();
}

// Get thesis ID
$thesis_id = $_GET['thesis_id'] ?? null;
if (!$thesis_id) {
    die("Invalid thesis.");
}

// Fetch thesis details
$thesis = $conn->query("SELECT * FROM thesis WHERE id=$thesis_id")->fetch_assoc();

$error_message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_number = trim($_POST['student_number']);

    // Fetch student info
    $student_query = $conn->query("SELECT * FROM students WHERE student_number = '$student_number'");
    $student = $student_query ? $student_query->fetch_assoc() : null;

    if (!$student) {
        $error_message = "Student not found. Please check the student number.";
    } elseif ($thesis['available_copies'] <= 0) {
        $error_message = "No copies available for this thesis.";
    } else {
        // Construct full name (without suffix)
        $student_name = trim($student['first_name'] . ' ' . $student['middle_initial'] . ' ' . $student['last_name']);
        $course = $student['course'];
        $borrow_date = date('Y-m-d H:i:s');
        $return_date = date('Y-m-d H:i:s', strtotime('+7 days'));

        // Insert into borrowed_thesis
        $stmt = $conn->prepare("INSERT INTO borrowed_thesis (thesis_id, student_number, student_name, course, borrow_date, return_date, status) VALUES (?, ?, ?, ?, ?, ?, 'Borrowed')");
        $stmt->bind_param("isssss", $thesis_id, $student_number, $student_name, $course, $borrow_date, $return_date);
        $stmt->execute();

        // Decrease thesis copies
        $conn->query("UPDATE thesis SET available_copies = available_copies - 1 WHERE id=$thesis_id");

        // Redirect with success message
        header("Location: librarian_thesis_borrow.php?borrow_success=1&student_number={$student_number}&student_name=" . urlencode($student_name) . "&course=" . urlencode($course) . "&borrow_date=" . urlencode($borrow_date) . "&return_date=" . urlencode($return_date));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Librarian Borrow Thesis</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700&display=swap');
  @import url('https://fonts.googleapis.com/css2?family=Khand:wght@400;700&display=swap');

  body {
    margin: 0;
    font-family: 'Rajdhani', sans-serif;
    background: url('background.png') no-repeat center center fixed;
    background-size: cover;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
  }

  .container {
    display: flex;
    background: rgba(255,255,255,0.95);
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    overflow: hidden;
    width: 950px;
    max-width: 95%;
  }

  .left {
    flex: 1.2;
    padding: 40px 50px;
  }

  .left h2 {
    color: #FECA37;
    font-size: 32px;
    margin-bottom: 20px;
    font-weight: 700;
  }

  .input-box { 
    margin: 15px 0; 
    position: relative; 
  }

  .input-box input {
    width: 100%;
    padding: 16px 12px 12px 12px;
    border-radius: 4px;
    border: 1px solid #ccc;
    font-size: 16px;
    background-color: #DBDBDB;
    outline: none;
    box-sizing: border-box;
    color: #222;
    font-family: 'Khand', 'Rajdhani', sans-serif;
    font-weight: 500;
  }

  .input-box label {
    position: absolute;
    top: 5px;
    left: 12px;
    font-size: 12px;
    color: #555;
    pointer-events: none;
  }

  .submit-btn {
    background-color: #174b3b;
    color: #FECA37;
    border: none;
    padding: 12px 30px;
    font-size: 18px;
    font-weight: 700;
    border-radius: 4px;
    cursor: pointer;
    transition: 0.3s;
    margin-top: 10px;
  }
  .submit-btn:hover { background-color: #113a2d; }

  .right {
    flex: 1;
    background-color: #174b3b;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    color: #fff;
    padding: 30px;
    text-align: center;
  }

  .right img {
    width: 150px;
    height: 200px;
    margin-bottom: 15px;
    border-radius: 8px;
    object-fit: cover;
  }

  .right h3 {
    color: #FECA37;
    font-size: 22px;
    font-weight: 700;
    margin: 5px 0;
  }

  .right p {
    font-size: 14px;
    margin: 2px 0;
  }

  .error-message { color: red; font-weight: bold; margin-bottom: 15px; }
</style>
</head>

<body>
<div class="container">
  <div class="left">
    <h2>Borrow Thesis</h2>

    <?php if (!empty($error_message)): ?>
        <p class="error-message"><?= $error_message ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="input-box">
        <label>Student Number</label>
        <input type="text" name="student_number" placeholder="Enter student number" required>
      </div>

      <button type="submit" class="submit-btn">Confirm Borrow</button>
    </form>
  </div>

  <div class="right">
    <h3><?= htmlspecialchars($thesis['title']) ?></h3>
    <p><strong>Author:</strong> <?= htmlspecialchars($thesis['author']) ?></p>
    <p><strong>Category:</strong> <?= htmlspecialchars($thesis['category']) ?></p>
    <p><strong>Published:</strong> <?= htmlspecialchars(date('M d, Y', strtotime($thesis['publish_date']))) ?></p>
    <p><strong>Available Copies:</strong> <?= htmlspecialchars($thesis['available_copies']) ?></p>
  </div>
</div>
</body>
</html>
