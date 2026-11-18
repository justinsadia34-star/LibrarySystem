<?php
session_start();
include('db_connect.php');

// Only allow librarian
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'librarian') {
    header("Location: login.php");
    exit();
}

// Handle borrow action (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_thesis_id'])) {
    $thesis_id = intval($_POST['borrow_thesis_id']);
    $student_number = 'LIBRARY';

    // Check if already borrowed
    $check = $conn->query("SELECT * FROM borrowed_thesis WHERE thesis_id=$thesis_id AND student_number='$student_number' AND status='Borrowed'");
    if ($check->num_rows > 0) {
        echo "Already borrowed";
        exit;
    }

    // Check available copies
    $thesis = $conn->query("SELECT available_copies FROM thesis WHERE id=$thesis_id")->fetch_assoc();
    if ($thesis['available_copies'] <= 0) {
        echo "No copies available";
        exit;
    }

    // Insert into borrowed_thesis
    $conn->query("INSERT INTO borrowed_thesis (thesis_id, student_number, borrow_date, status) 
                  VALUES ($thesis_id,'$student_number', NOW(), 'Borrowed')");

    // Decrease available_copies
    $conn->query("UPDATE thesis SET available_copies = available_copies - 1 WHERE id=$thesis_id");

    echo "success";
    exit;
}

// Fetch all thesis
$sql = "SELECT * FROM thesis ORDER BY id DESC";
$result = $conn->query($sql);
$thesis_list = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $thesis_list[] = $row;
    }
}

// Fetch borrowed thesis
$borrowed_result = $conn->query("SELECT thesis_id FROM borrowed_thesis WHERE status='Borrowed'");
$borrowed_thesis = [];
if ($borrowed_result->num_rows > 0) {
    while ($row = $borrowed_result->fetch_assoc()) {
        $borrowed_thesis[] = $row['thesis_id'];
    }
}

$showPopup = false;
$popupStudent = '';
$popupName = '';
$popupCourse = '';

if (isset($_GET['borrow_success']) && $_GET['borrow_success'] == 1) {
    $showPopup = true;
    $popupStudent = htmlspecialchars($_GET['student_number']);
    $popupName = htmlspecialchars($_GET['student_name']);
    $popupCourse = htmlspecialchars($_GET['course']);
}

// Default categories
$default_categories = ["Education", "Science", "Engineering", "Arts", "Business"];
$db_categories = array_unique(array_column($thesis_list, 'category'));
$all_categories = array_unique(array_merge($default_categories, $db_categories));
sort($all_categories);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Librarian | Borrow Thesis</title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
<style>
body {
  margin: 0;
  font-family: 'Rajdhani', sans-serif;
  background: linear-gradient(to bottom, #1a7a60, #0e3e2f);
  color: #fff;
  min-height: 100vh;
}

/* ===== NAVBAR ===== */
.top-nav {
  width: 100%;
  background-color: #0d2c22;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5em 2em;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
.logo {
  display: flex;
  align-items: center;
  gap: 10px;
}
.logo img { height: 45px; }
.top-nav h2 { font-weight:700; font-size:1.3em; }

/* ===== CONTENT ===== */
.content-wrapper { padding:25px 40px; }
.toolbar { display:flex; gap:10px; margin-bottom:20px; }
.search, .filter { padding:12px; border-radius:6px; border:none; }
.search { flex:1; }

/* ===== THESIS GRID ===== */
.thesis-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:18px; }
.thesis-card {
  background:white;
  border-radius:12px;
  padding:15px;
  display:flex;
  flex-direction:column;
  gap:10px;
  box-shadow:0 2px 6px rgba(0,0,0,0.15);
  cursor:pointer;
  transition:transform 0.2s ease, opacity 0.2s ease;
  color:#000;
}
.thesis-card:hover { transform:scale(1.02); }
.details h3 { font-size:16px; font-weight:bold; margin:5px 0; }
.tag { display:inline-block; font-size:10px; padding:3px 6px; margin-right:6px; background:#222; color:white; border-radius:5px; }
.status { padding:5px 8px; font-size:10px; border-radius:6px; }
.status.available { background:#15d18b; color:white; }
.status.borrowed { background:#d11515; color:white; }
.ref { font-size:10px; color:#555; font-weight:bold; margin-bottom:4px; }
.thesis-card.unavailable { filter: grayscale(100%); opacity:0.6; cursor:not-allowed; }

/* ===== MODAL ===== */
.modal-overlay { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); display:none; justify-content:center; align-items:center; z-index:999; }
.modal-content { background:white; padding:25px; border-radius:12px; max-width:600px; width:90%; text-align:center; box-shadow:0 4px 20px rgba(0,0,0,0.3); animation:fadeIn 0.3s ease; color:#000; }
.modal-content h2 { font-size:22px; margin:10px 0; }
.close-btn, #borrowBtn { border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:bold; margin-top:15px; }
.close-btn { background:#174b3b; color:#f7b500; }
#borrowBtn { background:#15d18b; color:white; }
@keyframes fadeIn { from{opacity:0; transform:scale(0.95);} to{opacity:1; transform:scale(1);} }

@media (max-width:1000px){.thesis-grid{grid-template-columns:repeat(2,1fr);} }
@media (max-width:600px){.thesis-grid{grid-template-columns:1fr;} }
</style>
</head>
<body>

<header class="top-nav">
  <div class="logo">
    <img src="yudiem.png" alt="UDM Library Logo">
    <h2>UDM UNIVERSITY LIBRARY</h2>
  </div>

  <div class="nav-right">
    <button onclick="window.location.href='librarian_borrow.php'" 
        style="padding:10px 20px; border:none; border-radius:8px; background:#174b3b; color:#f7b500; font-weight:bold; cursor:pointer; font-size:14px;">
        &larr; Back
    </button>
</div>
</header>


<section class="content-wrapper">
  <div class="toolbar">
    <input type="text" class="search" placeholder="Search by title..." id="searchInput">
    <select class="filter" id="categoryFilter">
      <option value="">All Categories</option>
      <?php foreach ($all_categories as $cat) { echo "<option value='$cat'>$cat</option>"; } ?>
    </select>
  </div>

  <div class="thesis-grid" id="thesisGrid">
    <?php foreach ($thesis_list as $thesis) {
      $borrowed = in_array($thesis['id'], $borrowed_thesis) ? 1 : 0;
      $available_copies = $thesis['available_copies'] ?? 0;
      $unavailable = $available_copies <= 0 ? "unavailable" : "";
      $status_class = $available_copies > 0 ? "available" : "borrowed";

      echo "
      <div class='thesis-card $unavailable'
        data-thesis-id='{$thesis['id']}'
        data-title='{$thesis['title']}'
        data-category='{$thesis['category']}'
        data-borrowed='{$borrowed}'
        data-author='{$thesis['author']}'
        data-date='" . date('M d, Y', strtotime($thesis['publish_date'])) . "'
        data-status='{$status_class}'
        data-available='{$available_copies}'>
        
        <div class='ref'>Ref: {$thesis['id']}</div>
        <div class='details'>
          <h3>{$thesis['title']}</h3>
          <span class='tag'>{$thesis['category']}</span>
          <span class='status {$status_class}'>$status_class</span>
          <p style='font-size:11px; margin-top:5px;'>Available Copies: <strong>{$available_copies}</strong></p>
        </div>
      </div>";
    } ?>
  </div>
</section>

<!-- Modal -->
<div class="modal-overlay" id="thesisModal">
  <div class="modal-content">
    <h2 id="modalTitle"></h2>
    <p><strong>Author:</strong> <span id="modalAuthor"></span></p>
    <p><strong>Published:</strong> <span id="modalDate"></span></p>
    <p><strong>Category:</strong> <span id="modalCategory"></span></p>
    <p><strong>Status:</strong> <span id="modalStatus"></span></p>
    <p><strong>Available Copies:</strong> <span id="modalAvailable"></span></p>
    <button class="close-btn" id="closeModal">Close</button>
    <button id="borrowBtn">Borrow Thesis</button>
  </div>
</div>

<script>
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const thesisCards = document.querySelectorAll('.thesis-card');
const modal = document.getElementById('thesisModal');
const closeModalBtn = document.getElementById('closeModal');
const borrowBtn = document.getElementById('borrowBtn');
const modalTitle = document.getElementById('modalTitle');
const modalAuthor = document.getElementById('modalAuthor');
const modalDate = document.getElementById('modalDate');
const modalCategory = document.getElementById('modalCategory');
const modalStatus = document.getElementById('modalStatus');
const modalAvailable = document.getElementById('modalAvailable');
let currentThesisId = null;

// Filter thesis
function filterThesis() {
  const searchText = searchInput.value.toLowerCase();
  const category = categoryFilter.value;
  thesisCards.forEach(card => {
    const title = card.dataset.title.toLowerCase();
    const cardCat = card.dataset.category;
    card.style.display = (title.includes(searchText) && (!category || cardCat === category)) ? 'flex' : 'none';
  });
}
searchInput.addEventListener('input', filterThesis);
categoryFilter.addEventListener('change', filterThesis);

// Modal open
thesisCards.forEach(card => {
  card.addEventListener('click', () => {
    currentThesisId = card.dataset.thesisId;
    modalTitle.textContent = card.dataset.title;
    modalAuthor.textContent = card.dataset.author;
    modalDate.textContent = card.dataset.date;
    modalCategory.textContent = card.dataset.category;
    modalStatus.textContent = card.dataset.status;
    modalAvailable.textContent = card.dataset.available;
    borrowBtn.style.display = (parseInt(card.dataset.available) > 0) ? 'inline-block' : 'none';
    modal.style.display = 'flex';
  });
});

closeModalBtn.addEventListener('click', () => modal.style.display = 'none');
window.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });

// Redirect Borrow button
borrowBtn.addEventListener('click', () => {
  if (!currentThesisId) return;
  window.location.href = `librarian_borrow_thesis_form.php?thesis_id=${currentThesisId}`;
});
</script>

<?php if ($showPopup): ?>
<div id="borrowSuccessModal" class="modal-overlay" style="display:flex; justify-content:center; align-items:center;">
  <div class="modal-content" style="max-width:400px; padding:20px; text-align:center;">
    <h2 style="color:#174b3b;">Borrow Successful!</h2>
    <p><strong>Student Number:</strong> <?= htmlspecialchars($popupStudent) ?></p>
    <p><strong>Name:</strong> <?= htmlspecialchars($popupName) ?></p>
    <p><strong>Course:</strong> <?= htmlspecialchars($popupCourse) ?></p>
    <button id="closeSuccessModal" style="padding:10px 20px; margin-top:15px; border:none; border-radius:6px; background:#174b3b; color:#f7b500; font-weight:bold; cursor:pointer;">OK</button>
  </div>
</div>

<script>
window.addEventListener('load', () => {
  const modal = document.getElementById('borrowSuccessModal');
  const closeBtn = document.getElementById('closeSuccessModal');
  if (modal) {
    modal.style.display = 'flex';
    closeBtn.addEventListener('click', () => {
      modal.style.display = 'none';
      const newUrl = window.location.origin + window.location.pathname;
      window.history.replaceState({}, document.title, newUrl);
    });
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.style.display = 'none';
        const newUrl = window.location.origin + window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
      }
    });
  }
});
</script>
<?php endif; ?>

</body>
</html>
