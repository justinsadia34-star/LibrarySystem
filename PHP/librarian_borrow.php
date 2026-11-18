<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'librarian') {
  header("Location: login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UDM Librarian | Borrow</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
  body {
    font-family: 'Inter', sans-serif;
    margin: 0;
    background: linear-gradient(135deg, #0b2f2f 0%, #174b3b 100%);
    color: #fff;
  }

  /* Navbar (from dashboard) */
  header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #0b2f2f;
    color: white;
    padding: 15px 40px;
  }
  .logo { display: flex; align-items: center; gap: 10px; }
  .logo img { height: 45px; }
  .logo h2 { margin: 0; font-weight: 700; }

  .nav-links {
    display: flex;
    gap: 25px;
    align-items: center;
  }
  .nav-links a {
    color: white;
    text-decoration: none;
    font-weight: 700;
    padding: 8px 18px;
    border-radius: 25px;
    transition: 0.3s;
  }
  .nav-links a.active, .nav-links a:hover { background: #1d6b54; }
  .nav-links button {
    background: #b50000;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 25px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
  }
  .nav-links button:hover { background: #7a0000; }

  /* Main Content */
  .main-wrapper {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 60px 20px;
    gap: 40px;
  }

  .main-wrapper h2 {
    font-size: 2em;
    font-weight: 700;
    color: #ffcc33;
    text-align: center;
  }

  .cards-container {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
    justify-content: center;
  }

  .card {
    background: #e5f5f4;
    color: #0b2f2f;
    border-radius: 15px;
    padding: 2em 2.5em;
    text-align: center;
    width: 180px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    transition: transform 0.3s, box-shadow 0.3s;
  }
  .card:hover {
    transform: translateY(-10px);
    box-shadow: 0 12px 20px rgba(0,0,0,0.35);
  }
  .card img { width: 100px; margin-bottom: 15px; }

  .card button {
    background-color: #174b3b;
    color: #ffcc33;
    border: none;
    font-weight: 700;
    font-size: 1em;
    padding: 8px 20px;
    border-radius: 30px;
    cursor: pointer;
    transition: 0.3s;
  }
  .card button:hover { background-color: #0f362b; }
</style>
</head>
<body>

<header>
  <div class="logo">
    <img src="yudiem.png" alt="UDM Logo">
    <h2>UDM UNIVERSITY LIBRARY</h2>
  </div>

  <div class="nav-links">
    <a href="librarian_dashboard.php">Dashboard</a>
    <a href="#" class="active">Borrow</a>
    <a href="librarian_loans.php">Loans</a>
    <form action="logout.php" method="POST" style="display:inline; margin:0;">
      <button type="submit">Logout</button>
    </form>
  </div>
</header>

<!-- Main Content -->
<div class="main-wrapper">
  <h2>Select Your Purpose</h2>
  <div class="cards-container">
    
    <!-- Borrow Books -->
    <div class="card">
      <img src="book_icon.png" alt="Books Icon">
      <form action="librarian_book_borrow.php" method="POST">
        <input type="hidden" name="purpose" value="Borrow Books">
        <button type="submit">BOOKS</button>
      </form>
    </div>

    <!-- Borrow Thesis -->
    <div class="card">
      <img src="thesis_icon.png" alt="Thesis Icon">
      <form action="librarian_thesis_borrow.php" method="POST">
        <input type="hidden" name="purpose" value="Borrow Thesis">
        <button type="submit">THESIS</button>
      </form>
    </div>

  </div>
</div>

</body>
</html>
