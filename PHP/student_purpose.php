<?php
session_start();
include('db_connect.php');

// Redirect to login if not logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Handle seat click and log it
if (isset($_GET['action']) && $_GET['action'] === 'seat_logout') {
    $student_id = $_SESSION['student_id'];

    // Fetch student details
    $query = $conn->prepare("SELECT student_number, course FROM students  WHERE id = ?");
    $query->bind_param("i", $student_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        $student_number = $student['student_number'];
        $course = $student['course'];
        $purpose = "Find Seat";
        $time_duration = "N/A";

        // Insert log entry with timestamp
        $insert = $conn->prepare("INSERT INTO activity_logs (student_number, course, purpose, time_duration, timestamp) VALUES (?, ?, ?, ?, NOW())");
        $insert->bind_param("ssss", $student_number, $course, $purpose, $time_duration);
        $insert->execute();
    }

    // Destroy session and logout
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UDM University Library | Purpose</title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
<style>
body {
  margin: 0;
  font-family: 'Rajdhani', sans-serif;
  background: linear-gradient(to bottom, #1a7a60, #0e3e2f);
  height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Navbar */
.navbar {
  background-color: #0d2c22;
  color: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5em 2em;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}
.nav-left { display: flex; align-items: center; }
.logo { height: 45px; margin-right: 10px; }
.nav-left h1 { font-weight: 700; font-size: 1.3em; }
.nav-left span { font-weight: 500; color: #ffcc33; }
.profile-icon { font-size: 1.5em; background: #14382b; padding: 5px 10px; border-radius: 50%; }
.logout-btn {
  background-color:#174b3b;
  color:#f7b500;
  border:none;
  padding:10px 20px;
  border-radius:5px;
  font-weight:bold;
  cursor:pointer;
  transition:0.3s;
}
.logout-btn:hover { background-color:#113a2d; }

/* Main Content */
.main-content-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 2em;
  gap: 2em;
}
.purpose-header {
  color: #ffcc33;
  font-size: 2em;
  font-weight: 700;
  text-align: center;
  margin: 0;
}
.main-content {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 2.5em;
  flex-wrap: wrap;
}
.card {
  background-color: #fff;
  border-radius: 15px;
  padding: 2em 3em;
  text-align: center;
  width: 180px;
  box-shadow: 0 6px 10px rgba(0, 0, 0, 0.25);
  transition: transform 0.3s, box-shadow 0.3s;
}
.card:hover {
  transform: translateY(-10px);
  box-shadow: 0 12px 20px rgba(0, 0, 0, 0.35);
}
.card-img { width: 100px; margin-bottom: 1em; }
.card-btn {
  background-color: #153a2d;
  color: #ffcc33;
  border: none;
  font-weight: 700;
  font-size: 1em;
  padding: 0.5em 1.5em;
  border-radius: 30px;
  cursor: pointer;
  transition: background-color 0.3s;
}
.card-btn:hover { background-color: #115e46; }
form { margin: 0; }

/* Popup Modal */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0; top: 0;
  width: 100%; height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  justify-content: center;
  align-items: center;
}
.modal-content {
  background-color: #0d2c22;
  color: #ffcc33;
  padding: 2em 3em;
  border-radius: 15px;
  text-align: center;
  box-shadow: 0 6px 15px rgba(0,0,0,0.3);
  animation: popup 0.3s ease-in-out;
}
@keyframes popup {
  from { transform: scale(0.8); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
.modal-content h3 {
  margin-bottom: 1em;
  font-size: 1.5em;
}
.ok-btn {
  background-color: #174b3b;
  color: #ffcc33;
  border: none;
  font-weight: bold;
  padding: 10px 25px;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.3s;
}
.ok-btn:hover { background-color: #115e46; }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <form action="logout.php" method="post" style="display:inline;">
    <button type="submit" class="logout-btn">Logout</button>
  </form>
  <div class="nav-left">
    <img src="yudiem.png" alt="UDM Logo" class="logo">
    <h1>UDM <span>UNIVERSITY LIBRARY</span></h1>
  </div>
  <div class="nav-right">
    <div class="profile-icon">👤</div>
  </div>
</nav>

<!-- Main Content -->
<main class="main-content-wrapper">
  <h2 class="purpose-header">Select Your Purpose</h2>

  <div class="main-content">
    <!-- Seats -->
    <div class="card">
      <img src="chair_icon.png" alt="Seats Icon" class="card-img">
      <button class="card-btn" onclick="openSeatPopup()">SEATS</button>
    </div>

    <!-- Books -->
    <div class="card">
      <img src="book_icon.png" alt="Books Icon" class="card-img">
      <form action="student_book_borrow.php" method="POST">
        <input type="hidden" name="purpose" value="Borrow Books">
        <button type="submit" class="card-btn">BOOKS</button>
      </form>
    </div>

    <!-- Thesis -->
    <div class="card">
      <img src="thesis_icon.png" alt="Thesis Icon" class="card-img">
      <form action="student_thesis_borrow.php" method="POST">
        <input type="hidden" name="purpose" value="Borrow Thesis">
        <button type="submit" class="card-btn">THESIS</button>
      </form>
    </div>
  </div>
</main>

<!-- Popup Modal -->
<div id="seatModal" class="modal">
  <div class="modal-content">
    <h3>Thank You for logging in, please find a seat.</h3>
    <p>Your activity has been recorded.<br>You will be logged out in 3 seconds...</p>
    <button class="ok-btn" onclick="logoutNow()">OK</button>
  </div>
</div>

<script>
function openSeatPopup() {
  const modal = document.getElementById('seatModal');
  modal.style.display = 'flex';
  // Log seat activity and then redirect to logout
  setTimeout(() => {
    window.location.href = 'student_purpose.php?action=seat_logout';
  }, 3000);
}

function logoutNow() {
  window.location.href = 'student_purpose.php?action=seat_logout';
}
</script>

</body>
</html>