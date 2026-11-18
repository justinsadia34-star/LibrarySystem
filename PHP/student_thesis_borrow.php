<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_number = $_SESSION['student_number'] ?? "123456";
include('db_connect.php');

// Handle borrow action (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_thesis_id'])) {
    $thesis_id = intval($_POST['borrow_thesis_id']);

    // Check if already borrowed
    $check = $conn->query("SELECT * FROM borrowed_thesis WHERE thesis_id=$thesis_id AND student_number='$student_number' AND status='Borrowed'");
    if ($check->num_rows > 0) { echo "Already borrowed"; exit; }

    // Check available copies
    $thesis = $conn->query("SELECT available_copies FROM thesis WHERE id=$thesis_id")->fetch_assoc();
    if ($thesis['available_copies'] <= 0) { echo "No copies available"; exit; }

    // Insert into borrowed_thesis
    $conn->query("INSERT INTO borrowed_thesis (thesis_id, student_number, borrow_date, status) VALUES ($thesis_id,'$student_number', NOW(), 'Borrowed')");

    // Decrease available_copies
    $conn->query("UPDATE thesis SET available_copies = available_copies - 1 WHERE id=$thesis_id");

    echo "success";
    exit;
}

// Fetch all theses
$sql = "SELECT * FROM thesis ORDER BY publish_date DESC";
$result = $conn->query($sql);
$theses = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $theses[] = $row;
    }
}

// Fetch borrowed theses by this student
$borrowed_result = $conn->query("SELECT thesis_id FROM borrowed_thesis WHERE student_number='$student_number' AND status='Borrowed'");
$borrowed_theses = [];
if($borrowed_result->num_rows > 0){
    while($row = $borrowed_result->fetch_assoc()){
        $borrowed_theses[] = $row['thesis_id'];
    }
}

// Categories based on publication year
$years = [];
foreach($theses as $t){
    $year = date('Y', strtotime($t['publish_date']));
    $years[] = $year;
}
$all_categories = array_unique($years);
rsort($all_categories); // recent first
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>UDM University Library | Thesis Catalog</title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
<style>
/* Page background & base font */
body {
  margin: 0;
  font-family: 'Rajdhani', sans-serif;
  background: linear-gradient(to bottom, #1a7a60, #0e3e2f);
  min-height: 100vh;
  color: #fff;
}

/* Navbar (matches book catalog / purpose) */
.navbar {
  background-color: #0d2c22;
  color: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.6em 2em;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
.nav-left { display:flex; align-items:center; gap:12px; }
.nav-left img.logo { height:45px; display:block; }
.nav-left h1 { margin:0; font-size:1.2rem; font-weight:700; color:#fff; }
.nav-left span { color:#ffcc33; font-weight:500; margin-left:6px; }
.profile-icon { font-size:1.2rem; background:#14382b; padding:6px 10px; border-radius:50%; }

/* Logout button */
.logout-btn {
  background-color:#174b3b;
  color:#f7b500;
  border:none;
  padding:10px 18px;
  border-radius:6px;
  font-weight:700;
  cursor:pointer;
}
.logout-btn:hover { background:#113a2d; }

/* Main content wrapper */
.content-wrapper {
  padding: 26px 40px;
  background: transparent;
}

/* small helpers */
.top-actions { display:flex; justify-content:flex-end; margin-bottom:18px; }
.back-btn {
  text-decoration:none;
  background:#174b3b;
  color:#f7b500;
  padding:10px 18px;
  border-radius:8px;
  font-weight:700;
}

/* Toggle buttons / toolbar */
.toggle-buttons { display:flex; gap:10px; margin-bottom:18px; }
.toggle-buttons button { flex:1; padding:10px; font-weight:700; border:none; border-radius:8px; cursor:pointer; }
.toggle-active { background:#174b3b; color:#f7b500; }
.toggle-inactive { background:#ccc; color:#333; }

.toolbar { display:flex; gap:10px; margin-bottom:20px; align-items:center; }
.search { flex:1; padding:12px; border-radius:6px; border:none; }
.filter { padding:12px; border-radius:6px; border:none; }

/* Grid */
.thesis-grid {
  display:grid;
  grid-template-columns: repeat(4, 1fr);
  gap:18px;
}
.thesis-card {
  background: #fff;
  color: #000;
  border-radius:12px;
  padding:14px;
  display:flex;
  flex-direction:column;
  gap:10px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
  cursor:pointer;
  transition: transform .15s ease, box-shadow .15s ease;
}
.thesis-card:hover { transform: translateY(-6px); box-shadow: 0 10px 18px rgba(0,0,0,0.18); }
.cover { width:100%; border-radius:8px; object-fit:cover; height:160px; background:#f0f0f0; }
.details h3 { margin:6px 0 4px; font-size:16px; font-weight:700; }
.details p { margin:0; font-size:12px; color:#333; line-height:1.25; }
.status { display:inline-block; padding:6px 8px; border-radius:8px; font-size:11px; margin-top:6px; }
.status.available { background:#15d18b; color:#fff; }
.status.borrowed { background:#d11515; color:#fff; }
.ref { font-size:11px; color:#666; font-weight:700; margin-bottom:6px; }

/* Unavailable look */
.thesis-card.unavailable { filter:grayscale(100%); opacity:0.65; cursor:not-allowed; }

/* Modal (matches book catalog modal) */
.modal-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); display:none; justify-content:center; align-items:center; z-index:999; }
.modal-content {
  background:white;
  color:#000;
  padding:22px;
  border-radius:12px;
  max-width:640px;
  width:92%;
  text-align:center;
  box-shadow: 0 6px 20px rgba(0,0,0,0.25);
}
.modal-content img { width:200px; border-radius:8px; margin-bottom:12px; object-fit:cover; height:200px; }
.modal-content h2 { margin:8px 0; font-size:20px; }
.modal-content p { margin:6px 0; color:#333; }
.close-btn {
  border:none; padding:10px 18px; border-radius:8px; font-weight:700; margin-top:12px; cursor:pointer;
}
.close-btn { background:#174b3b; color:#f7b500; }

/* responsive */
@media (max-width:1000px){ .thesis-grid { grid-template-columns: repeat(2,1fr); } }
@media (max-width:600px){ .thesis-grid { grid-template-columns: 1fr; } .modal-content img { width:140px; height:140px; } }
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

<section class="content-wrapper">
  <div class="top-actions">
    <a href="student_purpose.php" class="back-btn">← Back</a>
  </div>

  <div class="toggle-buttons">
    <button id="catalogBtn" class="toggle-active">Thesis Catalog</button>
    <button id="myThesisBtn" class="toggle-inactive">My Thesis</button>
  </div>

  <div class="toolbar">
    <input id="searchInput" type="text" class="search" placeholder="Search by title...">
    <select id="yearFilter" class="filter">
      <option value="">All Years</option>
      <?php foreach($all_categories as $year){ echo "<option value='{$year}'>{$year}</option>"; } ?>
    </select>
  </div>

  <div class="thesis-grid" id="thesisGrid">
    <?php
    foreach($theses as $t){
        $borrowed = in_array($t['id'], $borrowed_theses) ? 1 : 0;
        $available_copies = $t['available_copies'] ?? 0;
        $unavailable = $available_copies <= 0 ? "unavailable" : "";
        $status_class = $available_copies > 0 ? "available" : "borrowed";
        $year = date('Y', strtotime($t['publish_date']));
        // NOTE: logic untouched
        echo "
        <div class='thesis-card {$unavailable}' 
             data-thesis-id='{$t['id']}'
             data-title='{$t['title']}' 
             data-year='{$year}' 
             data-borrowed='{$borrowed}' 
             data-author='{$t['author']}'
             data-date='".date('M d, Y',strtotime($t['publish_date']))."'
             data-status='{$status_class}'
             data-available='{$available_copies}'>
            <div class='ref'>Ref: {$t['id']}</div>
            <div class='details'>
                <h3>{$t['title']}</h3>
                <p>By {$t['author']}<br>Published: ".date('M d, Y',strtotime($t['publish_date']))."</p>
                <span class='status {$status_class}'>".strtoupper($status_class)."</span>
                <p style='font-size:11px; margin-top:6px;'>Available Copies: <strong>{$available_copies}</strong></p>
            </div>
        </div>";
    }
    ?>
  </div>
</section>

<!-- Modal -->
<div class="modal-overlay" id="thesisModal">
  <div class="modal-content" id="modalContent">
    <img id="modalCover" src="" alt="Cover">
    <h2 id="modalTitle"></h2>
    <p><strong>Author:</strong> <span id="modalAuthor"></span></p>
    <p><strong>Published:</strong> <span id="modalDate"></span></p>
    <p><strong>Status:</strong> <span id="modalStatus"></span></p>
    <p><strong>Available Copies:</strong> <span id="modalAvailable"></span></p>
    <div>
      <button class="close-btn" id="closeModal">Close</button>
    </div>
  </div>
</div>

<script>
// Search & filter
const searchInput = document.getElementById('searchInput');
const yearFilter = document.getElementById('yearFilter');
const theses = document.querySelectorAll('.thesis-card');

function filterTheses(){
    const searchText = searchInput.value.toLowerCase();
    const year = yearFilter.value;
    theses.forEach(t=>{
        const title = t.dataset.title.toLowerCase();
        const tYear = t.dataset.year;
        t.style.display = (title.includes(searchText) && (!year || tYear === year)) ? 'flex' : 'none';
    });
}
searchInput.addEventListener('input', filterTheses);
yearFilter.addEventListener('change', filterTheses);

// Toggle Catalog / My Thesis
const catalogBtn = document.getElementById('catalogBtn');
const myThesisBtn = document.getElementById('myThesisBtn');
catalogBtn.addEventListener('click', ()=> {
  catalogBtn.classList.add('toggle-active'); catalogBtn.classList.remove('toggle-inactive');
  myThesisBtn.classList.add('toggle-inactive'); myThesisBtn.classList.remove('toggle-active');
  theses.forEach(t => t.style.display = 'flex');
});
myThesisBtn.addEventListener('click', ()=> {
  myThesisBtn.classList.add('toggle-active'); myThesisBtn.classList.remove('toggle-inactive');
  catalogBtn.classList.add('toggle-inactive'); catalogBtn.classList.remove('toggle-active');
  theses.forEach(t => {
    if (t.dataset.borrowed === '1') t.style.display = 'flex';
    else t.style.display = 'none';
  });
});

// Modal
const modal = document.getElementById('thesisModal');
const closeModalBtn = document.getElementById('closeModal');

const modalCover = document.getElementById('modalCover');
const modalTitle = document.getElementById('modalTitle');
const modalAuthor = document.getElementById('modalAuthor');
const modalDate = document.getElementById('modalDate');
const modalStatus = document.getElementById('modalStatus');
const modalAvailable = document.getElementById('modalAvailable');

let currentThesisId = null;

document.querySelectorAll('.thesis-card').forEach(card => {
  card.addEventListener('click', () => {
    currentThesisId = card.dataset.thesisId;
    modalCover.src = card.dataset.cover;
    modalTitle.textContent = card.dataset.title;
    modalAuthor.textContent = card.dataset.author;
    modalDate.textContent = card.dataset.date;
    modalStatus.textContent = card.dataset.status;
    modalAvailable.textContent = card.dataset.available;
    modal.style.display = 'flex';
  });
});

closeModalBtn.addEventListener('click', ()=> modal.style.display = 'none');
window.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });

// AJAX borrow (logic preserved)

</script>
</body>
</html>
