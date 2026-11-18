<?php
include('db_connect.php');
$message = "";

// Get student_number, title, and type from GET if coming from Loans page
$student_number = $_GET['student_number'] ?? '';
$title = $_GET['title'] ?? '';
$type = $_GET['type'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_number = trim($_POST['student_number']);
    $title = trim($_POST['title']);
    $type = $_POST['type'];

    if (!empty($student_number) && !empty($title) && !empty($type)) {
        if ($type === 'Book') {
            $conn->query("UPDATE borrowed_books bb
                         JOIN books b ON bb.book_id = b.id
                         SET bb.status='Returned', bb.return_date=NOW()
                         WHERE bb.student_number='$student_number' AND b.title='$title' AND bb.status='Borrowed'");
        } elseif ($type === 'Thesis') {
            $conn->query("UPDATE borrowed_thesis bt
                         JOIN thesis t ON bt.thesis_id = t.id
                         SET bt.status='Returned', bt.return_date=NOW()
                         WHERE bt.student_number='$student_number' AND t.title='$title' AND bt.status='Borrowed'");
        }

        $message = "The <strong>$title</strong> ($type) borrowed by student <strong>$student_number</strong> has been returned.";
    } else {
        $message = "Invalid data. Please provide student number, item title, and type.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Return Borrowed Item | UDM Library</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
body { font-family: 'Inter', sans-serif; margin: 0; background: #e5f5f4; color: #222; }
header { display: flex; justify-content: space-between; align-items: center; background: #0b2f2f; color: white; padding: 15px 40px; }
.logo { display: flex; align-items: center; gap: 10px; }
.logo img { height: 45px; }
.nav-links { display: flex; gap: 25px; }
.nav-links a { color: white; text-decoration: none; font-weight: 700; padding: 8px 18px; border-radius: 25px; transition: 0.3s; }
.nav-links a.active, .nav-links a:hover { background: #1d6b54; }
.container { max-width: 600px; margin: 50px auto; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
h2 { text-align: center; color: #0b2f2f; }
form { display: flex; flex-direction: column; gap: 15px; margin-top: 30px; }
input[type="text"] { padding: 12px 15px; border-radius: 8px; border: 1px solid #ccc; font-size: 16px; }
button { padding: 12px; background: #0b2f2f; color: white; border: none; border-radius: 25px; font-weight: 700; cursor: pointer; transition: 0.3s; }
button:hover { background: #174b3b; }
.message { text-align: center; font-weight: 700; margin-bottom: 20px; color: #28a745; }
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
    <a href="librarian_borrow.php">Borrow</a>
    <a href="librarian_loans.php">Loans</a>
    <form action="logout.php" method="POST" style="display:inline;">
      <button type="submit">Logout</button>
    </form>
  </div>
</header>

<div class="container">
    <h2>Return Borrowed Item</h2>

    <?php if($message): ?>
      <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="student_number" placeholder="Enter Student Number" value="<?= htmlspecialchars($student_number) ?>" required>
        <input type="text" name="title" placeholder="Enter Book/Thesis Title" value="<?= htmlspecialchars($title) ?>" required>
        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
        <button type="submit">Return Item</button>
    </form>
</div>

</body>
</html>
