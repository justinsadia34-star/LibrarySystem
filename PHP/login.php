<?php
session_start();
include('db_connect.php');

// Redirect logged-in users to the correct dashboard
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'student') {
        header("Location: student_purpose.php");
        exit();
    } elseif ($_SESSION['role'] === 'librarian') {
        header("Location: librarian_dashboard.php");
        exit();
    }
}


$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_number = trim($_POST['student_number']);
    $password = trim($_POST['password']);

    // 🟢 SPECIAL LOGIN: Librarian Access
    // If someone logs in using password 'librarian', go to librarian dashboard
    if ($student_number === 'admin' && $password === 'librarian') {
        // You can restrict to a specific ID if you want (example below)
        // if ($student_number === 'admin' || $student_number === 'LIB001')
        // For now, any student_number with password librarian works.
        $_SESSION['role'] = 'librarian';
        $_SESSION['student_number'] = $student_number;
        header("Location: librarian_dashboard.php");
        exit();
    }

    // 🟦 Normal Student Login
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_number = ?");
    $stmt->bind_param("s", $student_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verify password (hashed or plaintext fallback)
        if (password_verify($password, $row['password']) || $password === $row['password']) {
            // Create session variables
            $_SESSION['student_id'] = $row['id'];
            $_SESSION['student_name'] = $row['first_name'] . ' ' . $row['last_name'];
            $_SESSION['student_number'] = $row['student_number'];
            $_SESSION['role'] = 'student';

            header("Location: student_purpose.php");
            exit();
        } else {
            $error_message = "❌ Invalid Password.";
        }
    } else {
        $error_message = "❌ Student Number not found.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>University Library | Login</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700&display=swap');
  @import url('https://fonts.googleapis.com/css2?family=Khand:wght@400;700&display=swap');
  @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');

  body {
    margin: 0;
    font-family: 'Rajdhani', sans-serif;
    background: url('background.png') no-repeat center center fixed;
    background-size: cover;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
  }

  .container {
    display: flex;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    overflow: hidden;
    width: 950px;
    max-width: 95%;
  }

  .left {
    flex: 1.2;
    padding: 40px 50px;
    text-align: center;
  }

  .left h2 {
    color: #FECA37;
    font-size: 38px;
    margin-bottom: 25px;
    font-weight: 700;
    letter-spacing: 1px;
  }

  .input-box {
    margin: 15px 0;
    position: relative;
  }

  .input-box input {
    width: 100%;
    padding: 12px 12px;
    border-radius: 4px;
    border: 1px solid #ccc;
    font-size: 16px;
    background-color: #DBDBDB;
    outline: none;
    box-sizing: border-box;
    color: #222;
    font-family: 'Khand', 'Rajdhani', sans-serif;
    font-weight: 500;
    box-shadow: inset 0 6px 8px -6px rgba(0,0,0,0.29), inset 0 -6px 10px -8px rgba(0,0,0,0.12);
    transition: box-shadow 150ms ease, border-color 150ms ease;
  }

  .input-box input::placeholder {
    color: #8E8588;
    opacity: 1;
  }

  .input-box input:focus {
    border-color: #174b3b;
    box-shadow: inset 0 8px 14px -8px rgba(0,0,0,0.29), inset 0 -8px 18px -10px rgba(0,0,0,0.12);
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

  .submit-btn:hover {
    background-color: #113a2d;
  }

  .register {
    margin-top: 15px;
    font-size: 15px;
  }

  .register a {
    color: #FECA37;
    text-decoration: none;
    font-weight: 600;
  }

  .register a:hover {
    text-decoration: underline;
  }

  .right {
    flex: 1;
    background-color: #174b3b;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    flex-direction: row;
    color: #fff;
    padding: 30px;
  }

  .right img {
    width: 120px;
    margin-bottom: 0;
  }

  .right h3 {
    color: #FECA37;
    font-size: 26px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-align: left;
  }

  @media (max-width: 800px) {
    .container { flex-direction: column; }
    .right { display: none; }
  }

  .error-message {
    color: red;
    font-weight: bold;
    margin-bottom: 15px;
  }

  /* Autofill styling */
  input:-webkit-autofill,
  textarea:-webkit-autofill,
  select:-webkit-autofill {
    -webkit-box-shadow: inset 0 6px 8px -6px rgba(0,0,0,0.29), inset 0 0 0 1000px #DBDBDB !important;
    box-shadow: inset 0 6px 8px -6px rgba(0,0,0,0.29), inset 0 0 0 1000px #DBDBDB !important;
    background-color: #DBDBDB !important;
    -webkit-text-fill-color: #222 !important;
  }

  input:-webkit-autofill:focus {
    border-color: #174b3b !important;
  }
</style>
</head>

<body>
<div class="container">
  <div class="left">
    <h2>LOG IN</h2>

    <form method="POST" action="">
      <div class="input-box">
        <input type="text" name="student_number" placeholder="Student Number" required>
      </div>
      <div class="input-box">
        <input type="password" name="password" placeholder="Password" required>
      </div>

      <?php if (!empty($error_message)): ?>
        <p class="error-message"><?= $error_message ?></p>
      <?php endif; ?>

      <div class="register">
        Don’t have an account? <a href="sign_up.php">Click Here.</a>
      </div>

      <button type="submit" name="submit" class="submit-btn">LOGIN</button>
    </form>
  </div>

  <div class="right">
    <img src="../Assets/yudiem.png" alt="University Logo">
    <h3>UNIVERSITY LIBRARY</h3>
  </div>
</div>
</body>
</html>
