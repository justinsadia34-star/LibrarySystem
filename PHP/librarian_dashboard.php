  <?php
  include('db_connect.php');

  /* -------------------------------------------
    📚 BOOKS OVERVIEW QUERIES
  --------------------------------------------*/
  $lowStockBooks = $conn->query("
    SELECT title, category, available_copies, total_copies
    FROM books
    WHERE available_copies <= 2
    ORDER BY available_copies ASC
  ");

  $totalBooks = $conn->query("SELECT COUNT(*) AS total FROM books")->fetch_assoc()['total'];
  $borrowedBooks = $conn->query("SELECT COUNT(*) AS total FROM borrowed_books WHERE status='Borrowed'")->fetch_assoc()['total'];
  $availableBooks = $conn->query("SELECT SUM(available_copies) AS total FROM books")->fetch_assoc()['total'];
  $overdueBooks = $conn->query("SELECT COUNT(*) AS total FROM borrowed_books WHERE status='Overdue'")->fetch_assoc()['total'];

  /* 📊 Weekly Borrowing Activity */
  $days = [];
  $bookBorrowData = [];
  $bookReturnData = [];
  for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $days[] = date('D', strtotime($date));

    $borrowed = $conn->query("SELECT COUNT(*) AS total FROM borrowed_books WHERE DATE(borrow_date)='$date'")->fetch_assoc()['total'];
    $returned = $conn->query("SELECT COUNT(*) AS total FROM borrowed_books WHERE DATE(return_date)='$date'")->fetch_assoc()['total'];

    $bookBorrowData[] = $borrowed;
    $bookReturnData[] = $returned;
  }

  /* -------------------------------------------
    🎓 THESIS OVERVIEW QUERIES
  --------------------------------------------*/
  $lowStockThesis = $conn->query("
    SELECT title, category, available_copies, total_copies
    FROM thesis
    WHERE available_copies <= 2
    ORDER BY available_copies ASC
  ");

  $totalThesis = $conn->query("SELECT COUNT(*) AS total FROM thesis")->fetch_assoc()['total'];
  $borrowedThesis = $conn->query("SELECT COUNT(*) AS total FROM borrowed_thesis WHERE status='Borrowed'")->fetch_assoc()['total'];
  $availableThesis = $conn->query("SELECT SUM(available_copies) AS total FROM thesis")->fetch_assoc()['total'];
  $overdueThesis = $conn->query("SELECT COUNT(*) AS total FROM borrowed_thesis WHERE status='Overdue'")->fetch_assoc()['total'];

  /* 📊 Weekly Borrowing Activity (Thesis) */
  $thesisBorrowData = [];
  $thesisReturnData = [];
  for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $thesisBorrowData[] = $conn->query("SELECT COUNT(*) AS total FROM borrowed_thesis WHERE DATE(borrow_date)='$date'")->fetch_assoc()['total'];
    $thesisReturnData[] = $conn->query("SELECT COUNT(*) AS total FROM borrowed_thesis WHERE DATE(return_date)='$date'")->fetch_assoc()['total'];
  }
  ?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UDM Librarian Dashboard</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; margin: 0; background: linear-gradient(135deg, #0b2f2f 0%, #174b3b 100%); color: #222; }
    header { display: flex; justify-content: space-between; align-items: center; background: #0b2f2f; color: white; padding: 15px 40px; }
    .logo { display: flex; align-items: center; gap: 10px; }
    .logo img { height: 45px; }
    .nav-links { display: flex; gap: 25px; }
    .nav-links a { color: white; text-decoration: none; font-weight: 700; padding: 8px 18px; border-radius: 25px; transition: 0.3s; }
    .nav-links a.active, .nav-links a:hover { background: #1d6b54; }
    .dashboard-container { background: #e5f5f4; margin: 30px auto; padding: 30px; border-radius: 20px; width: 90%; max-width: 1200px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
    .tab-container { display: flex; justify-content: space-around; background: #d2d8d8; border-radius: 12px; padding: 5px; margin-bottom: 25px; }
    .tab { flex: 1; text-align: center; padding: 12px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s; color: #333; }
    .tab.active { background: white; color: #0b2f2f; }
    .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 30px; }
    .card { background: white; border-radius: 14px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .card h3 { margin: 0 0 10px 0; color: #0b2f2f; }
    .card .value { font-size: 30px; font-weight: 700; color: #174b3b; }
    .bottom-section { display: grid; grid-template-columns: 2fr 1.5fr; gap: 25px; }
    table { width: 100%; border-collapse: collapse; border-radius: 10px; overflow: hidden; background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    th, td { text-align: left; padding: 12px 15px; border-bottom: 1px solid #eee; }
    th { background: #f5f7f7; }
    td.status { font-weight: 700; text-align: center; }
    .low { color: #b38b00; } .out { color: #b50000; }
    .chart-card { background: white; border-radius: 14px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    @media (max-width: 900px) { .bottom-section { grid-template-columns: 1fr; } }
  </style>
  </head>
  <body>

  <header>
    <div class="logo">
      <img src="yudiem.png" alt="UDM Logo">
      <h2>UDM UNIVERSITY LIBRARY</h2>
    </div>

    <div class="nav-links">
      <a href="#" class="active">Dashboard</a>
      <a href="librarian_borrow.php">Borrow</a>
      <a href="librarian_loans.php">Loans</a>
      <form action="logout.php" method="POST" style="display:inline; margin:0;">
        <button type="submit" style="
          background: #b50000; 
          color: white; 
          border: none; 
          padding: 8px 16px; 
          border-radius: 25px; 
          font-weight: 700; 
          cursor: pointer;
          transition: 0.3s;
          margin-left: 15px;
        ">Logout</button>
      </form>
    </div>
  </header>


  <div class="dashboard-container">
    <div class="tab-container">
      <div class="tab active" onclick="showTab('books')">Book Overview</div>
      <div class="tab" onclick="showTab('thesis')">Thesis Overview</div>
    </div>

    <!-- BOOK OVERVIEW -->
    <div id="books" class="overview-section">
      <div class="card-grid">
        <div class="card"><h3>Total Books</h3><div class="value"><?= $totalBooks ?></div></div>
        <div class="card"><h3>Currently Borrowed</h3><div class="value"><?= $borrowedBooks ?></div></div>
        <div class="card"><h3>Available Books</h3><div class="value"><?= $availableBooks ?></div></div>
        <div class="card"><h3>Overdue Books</h3><div class="value"><?= $overdueBooks ?></div></div>
      </div>

      <div class="bottom-section">
        <div>
          <h3>Low Stock Books</h3>
          <table>
            <thead><tr><th>Title</th><th>Category</th><th>Avail</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
              <?php while ($row = $lowStockBooks->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= $row['available_copies'] ?></td>
                <td><?= $row['total_copies'] ?></td>
                <td class="status <?= $row['available_copies']==0 ? 'out' : 'low' ?>">
                  <?= $row['available_copies']==0 ? 'Out of Stock' : 'Low Stock' ?>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
        <div class="chart-card">
          <h4>Weekly Book Activity</h4>
          <canvas id="bookChart"></canvas>
        </div>
      </div>
    </div>

    <!-- THESIS OVERVIEW -->
    <div id="thesis" class="overview-section" style="display:none;">
      <div class="card-grid">
        <div class="card"><h3>Total Thesis</h3><div class="value"><?= $totalThesis ?></div></div>
        <div class="card"><h3>Currently Borrowed</h3><div class="value"><?= $borrowedThesis ?></div></div>
        <div class="card"><h3>Available Thesis</h3><div class="value"><?= $availableThesis ?></div></div>
        <div class="card"><h3>Overdue Thesis</h3><div class="value"><?= $overdueThesis ?></div></div>
      </div>

      <div class="bottom-section">
        <div>
          <h3>Low Stock Thesis</h3>
          <table>
            <thead><tr><th>Title</th><th>Category</th><th>Avail</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
              <?php while ($row = $lowStockThesis->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= $row['available_copies'] ?></td>
                <td><?= $row['total_copies'] ?></td>
                <td class="status <?= $row['available_copies']==0 ? 'out' : 'low' ?>">
                  <?= $row['available_copies']==0 ? 'Out of Stock' : 'Low Stock' ?>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
        <div class="chart-card">
          <h4>Weekly Thesis Activity</h4>
          <canvas id="thesisChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Chart Data
    const labels = <?= json_encode($days) ?>;
    const bookBorrow = <?= json_encode($bookBorrowData) ?>;
    const bookReturn = <?= json_encode($bookReturnData) ?>;
    const thesisBorrow = <?= json_encode($thesisBorrowData) ?>;
    const thesisReturn = <?= json_encode($thesisReturnData) ?>;

    // Book Chart
    new Chart(document.getElementById('bookChart'), {
      type: 'line',
      data: {
        labels,
        datasets: [
          { label: 'Borrowed', data: bookBorrow, borderColor: '#3a7bd5', tension: 0.3 },
          { label: 'Returned', data: bookReturn, borderColor: '#28a745', tension: 0.3 }
        ]
      },
      options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
    });

    // Thesis Chart
    new Chart(document.getElementById('thesisChart'), {
      type: 'line',
      data: {
        labels,
        datasets: [
          { label: 'Borrowed', data: thesisBorrow, borderColor: '#007bff', tension: 0.3 },
          { label: 'Returned', data: thesisReturn, borderColor: '#ffc107', tension: 0.3 }
        ]
      },
      options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
    });

    // Tab switching
    function showTab(tabName) {
      document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.overview-section').forEach(sec => sec.style.display = 'none');
      document.getElementById(tabName).style.display = 'block';
      event.target.classList.add('active');
    }
  </script>

  </body>
  </html>
