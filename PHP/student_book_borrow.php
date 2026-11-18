<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_number = $_SESSION['student_number'] ?? "123456";
include('db_connect.php');

// Handle borrow action (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_book_id'])) {
    $book_id = intval($_POST['borrow_book_id']);

    // Check if already borrowed
    $check = $conn->query("SELECT * FROM borrowed_books WHERE book_id=$book_id AND student_number='$student_number' AND status='Borrowed'");
    if ($check->num_rows > 0) { echo "Already borrowed"; exit; }

    // Check available copies
    $book = $conn->query("SELECT available_copies FROM books WHERE id=$book_id")->fetch_assoc();
    if ($book['available_copies'] <= 0) { echo "No copies available"; exit; }

    // Insert into borrowed_books
    $conn->query("INSERT INTO borrowed_books (book_id, student_number, borrow_date, status) VALUES ($book_id,'$student_number', NOW(), 'Borrowed')");

    // Decrease available_copies
    $conn->query("UPDATE books SET available_copies = available_copies - 1 WHERE id=$book_id");

    echo "success";
    exit;
}

// ✅ Fetch all books + check if the student has borrowed them
$sql = "
    SELECT 
        b.*,
        (SELECT COUNT(*) FROM borrowed_books 
         WHERE borrowed_books.book_id = b.id 
         AND borrowed_books.student_number = '$student_number' 
         AND borrowed_books.status = 'Borrowed') AS is_borrowed
    FROM books b
    ORDER BY b.id DESC
";
$result = $conn->query($sql);
$books = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}

// Default categories
$default_categories = [
    "Fantasy", "Science Fiction", "Mystery and Thriller",
    "Romance", "Horror", "Historical Fiction",
    "Action and Adventure", "Biography and Memoir", "History"
];

// Merge DB categories with defaults
$db_categories = array_unique(array_column($books, 'category'));
$all_categories = array_unique(array_merge($default_categories, $db_categories));
sort($all_categories);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UDM University Library | Book Catalog</title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
<style>
body {
  margin: 0;
  font-family: 'Rajdhani', sans-serif;
  background: linear-gradient(to bottom, #1a7a60, #0e3e2f);
  min-height: 100vh;
  color: #fff;
}
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

.content-wrapper { padding:25px 40px; }
.toolbar { display:flex; gap:10px; margin-bottom:20px; }
.search, .filter { padding:12px; border-radius:6px; border:none; }
.search { flex:1; }

.book-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:18px; }
.book-card { background:white; border-radius:12px; padding:15px; display:flex; flex-direction:column; gap:10px; box-shadow:0 2px 6px rgba(0,0,0,0.15); cursor:pointer; transition:transform 0.2s ease, opacity 0.2s ease; color:#000; }
.book-card:hover { transform:scale(1.02); }
.cover { width:100%; border-radius:8px; object-fit:cover; }
.details h3 { font-size:16px; font-weight:bold; margin:5px 0; }
.details p { font-size:12px; margin:5px 0; color:#333; }
.tag { display:inline-block; font-size:10px; padding:3px 6px; margin-right:6px; background:#222; color:white; border-radius:5px; }
.status { padding:5px 8px; font-size:10px; border-radius:6px; }
.status.available { background:#15d18b; color:white; }
.status.borrowed { background:#d11515; color:white; }
.ref { font-size:10px; color:#555; font-weight:bold; margin-bottom:4px; }
.toggle-buttons { display:flex; gap:10px; margin-bottom:20px; }
.toggle-buttons button { flex:1; padding:10px; font-weight:700; border:none; border-radius:8px; cursor:pointer; transition:0.3s; }
.toggle-active { background:#174b3b; color:#f7b500; }
.toggle-inactive { background:#ccc; color:#333; }
.book-card.unavailable { filter: grayscale(100%); opacity:0.6; cursor:not-allowed; }

@media (max-width:1000px){.book-grid{grid-template-columns:repeat(2,1fr);} }
@media (max-width:600px){.book-grid{grid-template-columns:1fr;} }

.modal-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); display:none; justify-content:center; align-items:center; z-index:999; }
.modal-content { background:white; padding:25px; border-radius:12px; max-width:600px; width:90%; text-align:center; box-shadow:0 4px 20px rgba(0,0,0,0.3); animation:fadeIn 0.3s ease; color:#000; }
.modal-content img { width:200px; border-radius:8px; margin-bottom:15px; }
.close-btn { border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:bold; margin-top:15px; background:#174b3b; color:#f7b500; }
@keyframes fadeIn { from{opacity:0; transform:scale(0.95);} to{opacity:1; transform:scale(1);} }
</style>
</head>
<body>

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
<div style="display:flex; justify-content:flex-end; margin-bottom:15px;">
<a href="student_purpose.php" style="text-decoration:none; background:#174b3b; color:#f7b500; padding:10px 20px; border-radius:8px; font-weight:700;">← Back</a>
</div>

<div class="toggle-buttons">
    <button id="catalogBtn" class="toggle-active">Book Catalog</button>
    <button id="myBooksBtn" class="toggle-inactive">My Books</button>
</div>

<div class="toolbar">
    <input type="text" class="search" placeholder="Search by title..." id="searchInput">
    <select class="filter" id="categoryFilter"><option value="">All Categories</option>
        <?php foreach($all_categories as $cat){ echo "<option value='$cat'>$cat</option>"; } ?>
    </select>
</div>

<div class="book-grid" id="bookGrid">
<?php
foreach($books as $book){
    $borrowed = $book['is_borrowed'] > 0 ? 1 : 0;
    $available_copies = $book['available_copies'] ?? 0;
    $unavailable = ($available_copies <= 0 && !$borrowed) ? "unavailable" : "";
    $status_text = $borrowed ? "Borrowed" : ($available_copies > 0 ? "Available" : "Unavailable");
    $status_class = $borrowed ? "borrowed" : ($available_copies > 0 ? "available" : "borrowed");

    echo "
    <div class='book-card $unavailable' 
        data-book-id='{$book['id']}'
        data-title='{$book['title']}' 
        data-category='{$book['category']}' 
        data-borrowed='{$borrowed}' 
        data-author='{$book['author']}'
        data-publisher='{$book['publisher']}'
        data-date='".date('M d, Y',strtotime($book['publish_date']))."'
        data-status='{$status_text}'
        data-cover='{$book['cover_image']}'
        data-available='{$available_copies}'>
        <div class='ref'>Ref: {$book['id']}</div>
        <img src='{$book['cover_image']}' class='cover'>
        <div class='details'>
            <h3>{$book['title']}</h3>
            <p>By {$book['author']}<br>{$book['publisher']}<br>Published: ".date('M d, Y',strtotime($book['publish_date']))."</p>
            <span class='tag'>{$book['category']}</span>
            <span class='status {$status_class}'>$status_text</span>
            <p style='font-size:11px; margin-top:5px;'>Available Copies: <strong>{$available_copies}</strong></p>
        </div>
    </div>";
}
?>
</div>
</section>

<div class="modal-overlay" id="bookModal">
    <div class="modal-content" id="modalContent">
        <img id="modalCover"><h2 id="modalTitle"></h2>
        <p><strong>Author:</strong> <span id="modalAuthor"></span></p>
        <p><strong>Publisher:</strong> <span id="modalPublisher"></span></p>
        <p><strong>Published:</strong> <span id="modalDate"></span></p>
        <p><strong>Category:</strong> <span id="modalCategory"></span></p>
        <p><strong>Status:</strong> <span id="modalStatus"></span></p>
        <p><strong>Available Copies:</strong> <span id="modalAvailable"></span></p>
        <button class="close-btn" id="closeModal">Close</button>
    </div>
</div>

<script>
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const books = document.querySelectorAll('.book-card');

function filterBooks(){
    const searchText = searchInput.value.toLowerCase();
    const category = categoryFilter.value;
    books.forEach(book=>{
        const title = book.dataset.title.toLowerCase();
        const bookCat = book.dataset.category;
        book.style.display = (title.includes(searchText) && (!category||bookCat===category))?'flex':'none';
    });
}
searchInput.addEventListener('input',filterBooks);
categoryFilter.addEventListener('change',filterBooks);

const catalogBtn=document.getElementById('catalogBtn');
const myBooksBtn=document.getElementById('myBooksBtn');
catalogBtn.addEventListener('click',()=>{
    catalogBtn.classList.add('toggle-active'); catalogBtn.classList.remove('toggle-inactive');
    myBooksBtn.classList.add('toggle-inactive'); myBooksBtn.classList.remove('toggle-active');
    books.forEach(book=>book.style.display='flex');
});
myBooksBtn.addEventListener('click',()=>{
    myBooksBtn.classList.add('toggle-active'); myBooksBtn.classList.remove('toggle-inactive');
    catalogBtn.classList.add('toggle-inactive'); catalogBtn.classList.remove('toggle-active');
    books.forEach(book=>{
        if(book.dataset.borrowed==='1') book.style.display='flex';
        else book.style.display='none';
    });
});

const modal=document.getElementById('bookModal');
const closeModalBtn=document.getElementById('closeModal');
const modalCover=document.getElementById('modalCover');
const modalTitle=document.getElementById('modalTitle');
const modalAuthor=document.getElementById('modalAuthor');
const modalPublisher=document.getElementById('modalPublisher');
const modalDate=document.getElementById('modalDate');
const modalCategory=document.getElementById('modalCategory');
const modalStatus=document.getElementById('modalStatus');
const modalAvailable=document.getElementById('modalAvailable');

document.querySelectorAll('.book-card').forEach(card=>{
    card.addEventListener('click',()=>{
        modalCover.src = card.dataset.cover;
        modalTitle.textContent = card.dataset.title;
        modalAuthor.textContent = card.dataset.author;
        modalPublisher.textContent = card.dataset.publisher;
        modalDate.textContent = card.dataset.date;
        modalCategory.textContent = card.dataset.category;
        modalStatus.textContent = card.dataset.status;
        modalAvailable.textContent = card.dataset.available;
        modal.style.display='flex';
    });
});

closeModalBtn.addEventListener('click',()=>modal.style.display='none');
window.addEventListener('click',e=>{if(e.target===modal) modal.style.display='none';});
</script>
</body>
</html>
