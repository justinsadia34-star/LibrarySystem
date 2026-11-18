<?php
include('db_connect.php');
$message = "";

// --- Handle Return submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_item'])) {
    $student_number = trim($_POST['student_number']);
    $title = trim($_POST['title']);
    $type = $_POST['type'];

    if (!empty($student_number) && !empty($title) && !empty($type)) {

        if ($type === 'Book') {
            // --- Update borrowed_books and mark as returned ---
            $updateBorrow = $conn->query("
                UPDATE borrowed_books bb
                JOIN books b ON bb.book_id = b.id
                SET bb.status='Returned', bb.return_date=NOW()
                WHERE bb.student_number='$student_number'
                AND b.title='$title'
                AND bb.status='Borrowed'
            ");

            // --- Increase available copies if returned successfully ---
            if ($conn->affected_rows > 0) {
                $conn->query("
                    UPDATE books
                    SET available_copies = available_copies + 1
                    WHERE title = '$title' AND available_copies < total_copies
                ");
            }

        } elseif ($type === 'Thesis') {
            // --- Update borrowed_thesis and mark as returned ---
            $updateThesis = $conn->query("
                UPDATE borrowed_thesis bt
                JOIN thesis t ON bt.thesis_id = t.id
                SET bt.status='Returned', bt.return_date=NOW()
                WHERE bt.student_number='$student_number'
                AND t.title='$title'
                AND bt.status='Borrowed'
            ");

            // --- Increase available copies if thesis has availability tracking ---
            if ($conn->affected_rows > 0) {
                $conn->query("
                    UPDATE thesis
                    SET available_copies = available_copies + 1
                    WHERE title = '$title' AND available_copies < total_copies
                ");
            }
        }

        $message = "The <strong>$title</strong> ($type) borrowed by student <strong>$student_number</strong> has been returned successfully.";

    } else {
        $message = "Invalid data. Please provide student number, item title, and type.";
    }
}

// --- Fetch all borrowed books ---
$bookLoans = $conn->query("
    SELECT bb.id, b.title, 'Book' AS type, bb.student_number, bb.borrow_date, bb.return_date, bb.status
    FROM borrowed_books bb
    JOIN books b ON bb.book_id = b.id
");

// --- Fetch all borrowed thesis ---
$thesisLoans = $conn->query("
    SELECT bt.id, t.title, 'Thesis' AS type, bt.student_number, bt.borrow_date, bt.return_date, bt.status
    FROM borrowed_thesis bt
    JOIN thesis t ON bt.thesis_id = t.id
");

// --- Merge into a single array (Borrowed items only) ---
$loans = [];
while ($row = $bookLoans->fetch_assoc()) {
    if ($row['status'] === 'Borrowed') {
        $loans[] = $row;
    }
}
while ($row = $thesisLoans->fetch_assoc()) {
    if ($row['status'] === 'Borrowed') {
        $loans[] = $row;
    }
}

// --- Default loan types ---
$loanTypes = ['All', 'Book', 'Thesis'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UDM Librarian | Loans & Returns</title>
<style>
/* --- Your existing styles --- */
body { font-family: 'Inter', sans-serif; margin:0; background:#e5f5f4; color:#222; }
header { display:flex; justify-content:space-between; align-items:center; background:#0b2f2f; color:white; padding:15px 40px; }
  .logo { display: flex; align-items: center; gap: 10px; }
  .logo img { height: 45px; }
  .logo h2 { margin: 0; font-weight: 700; }
.nav-links a { color:white; text-decoration:none; font-weight:700; padding:8px 18px; border-radius:25px; transition:0.3s; }
.nav-links a.active, .nav-links a:hover { background:#1d6b54; }
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
.container { max-width:1200px; margin:30px auto; padding:20px; }
.filter-tabs { display:flex; gap:10px; margin-bottom:20px; }
.filter-tabs button { flex:1; padding:10px; font-weight:700; border:none; border-radius:10px; cursor:pointer; transition:0.3s; background:#d2d8d8; }
.filter-tabs button.active { background:#0b2f2f; color:white; }
.search-bar { margin-bottom:15px; }
.search-bar input { width:100%; padding:10px 12px; border-radius:8px; border:1px solid #cdd; }
table { width:100%; border-collapse: collapse; border-radius:10px; overflow:hidden; background:white; box-shadow:0 5px 15px rgba(0,0,0,0.1); }
th, td { text-align:left; padding:12px 15px; border-bottom:1px solid #eee; }
th { background:#f5f7f7; }
.status { font-weight:700; text-align:center; padding:5px 10px; border-radius:12px; color:white; }
.status.Borrowed { background:#007bff; }
.status.Returned { background:#28a745; }
.status.Overdue { background:#d11515; }
.returnBtn { padding:6px 12px; background:#15d18b; color:white; border:none; border-radius:6px; cursor:pointer; }
.message { text-align:center; font-weight:700; margin-bottom:20px; color:#28a745; }
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
    <a href="#" class="active">Loans</a>
    <form action="logout.php" method="POST" style="display:inline;">
      <button type="submit">Logout</button>
    </form>
  </div>
</header>

<div class="container">

  <?php if($message): ?>
    <div class="message"><?= $message ?></div>
  <?php endif; ?>

  <div class="filter-tabs">
    <?php foreach($loanTypes as $type): ?>
      <button class="<?= $type=='All'?'active':'' ?>" data-filter="<?= $type ?>"><?= $type ?></button>
    <?php endforeach; ?>
  </div>

  <div class="search-bar">
    <input type="text" id="searchInput" placeholder="Search by title, student number, or type...">
  </div>

  <table id="loanTable">
    <thead>
      <tr>
        <th>Title</th>
        <th>Type</th>
        <th>Student Number</th>
        <th>Borrow Date</th>
        <th>Return Date</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($loans as $loan): ?>
      <tr data-type="<?= $loan['type'] ?>">
        <td><?= htmlspecialchars($loan['title']) ?></td>
        <td><?= $loan['type'] ?></td>
        <td><?= htmlspecialchars($loan['student_number']) ?></td>
        <td><?= $loan['borrow_date'] ?></td>
        <td><?= $loan['return_date'] ?: '-' ?></td>
        <td class="status <?= $loan['status'] ?>"><?= $loan['status'] ?></td>
        <td>
          <button class="returnBtn"
                  data-student="<?= htmlspecialchars($loan['student_number']) ?>"
                  data-title="<?= htmlspecialchars($loan['title']) ?>"
                  data-type="<?= $loan['type'] ?>">
            Return
          </button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Return Modal -->
<div id="returnModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000;">
  <div style="background:white; padding:25px; border-radius:15px; max-width:500px; width:90%; position:relative;">
    <span id="closeModal" style="position:absolute; top:10px; right:15px; font-size:22px; cursor:pointer;">&times;</span>
    <h2 style="text-align:center; color:#0b2f2f;">Return Borrowed Item</h2>
    <form method="POST" id="returnForm">
      <input type="hidden" name="return_item" value="1">
      <input type="text" name="student_number" id="modalStudentNumber" placeholder="Student Number" required style="width:100%; margin-bottom:12px; padding:10px; border-radius:6px; border:1px solid #ccc;">
      <input type="text" name="title" id="modalTitle" placeholder="Book/Thesis Title" required style="width:100%; margin-bottom:12px; padding:10px; border-radius:6px; border:1px solid #ccc;">
      <input type="hidden" name="type" id="modalType">
      <button type="submit" style="width:100%; padding:12px; background:#0b2f2f; color:white; border:none; border-radius:25px; font-weight:700; cursor:pointer;">Return Item</button>
    </form>
  </div>
</div>

<script>
// Filter tabs
const filterButtons = document.querySelectorAll('.filter-tabs button');
const loanRows = document.querySelectorAll('#loanTable tbody tr');
const searchInput = document.getElementById('searchInput');

filterButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    filterButtons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const filter = btn.dataset.filter;
    loanRows.forEach(row => {
      row.style.display = (filter==='All' || row.dataset.type===filter) ? '' : 'none';
    });
  });
});

searchInput.addEventListener('input', () => {
  const text = searchInput.value.toLowerCase();
  loanRows.forEach(row => {
    const title = row.cells[0].textContent.toLowerCase();
    const student = row.cells[2].textContent.toLowerCase();
    const type = row.cells[1].textContent.toLowerCase();
    row.style.display = (title.includes(text) || student.includes(text) || type.includes(text)) ? '' : 'none';
  });
});

// --- Modal logic ---
const returnModal = document.getElementById('returnModal');
const closeModal = document.getElementById('closeModal');
const modalStudentNumber = document.getElementById('modalStudentNumber');
const modalTitle = document.getElementById('modalTitle');
const modalType = document.getElementById('modalType');

document.querySelectorAll('.returnBtn').forEach(btn => {
  btn.addEventListener('click', () => {
    modalStudentNumber.value = btn.dataset.student;
    modalTitle.value = btn.dataset.title;
    modalType.value = btn.dataset.type;
    returnModal.style.display = 'flex';
  });
});

closeModal.addEventListener('click', () => {
  returnModal.style.display = 'none';
});
</script>

</body>
</html>
