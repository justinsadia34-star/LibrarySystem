<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>University Library | Register</title>

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
      max-width: 90%;
    }
    .left {
      flex: 1.8;
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
    .form-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
    }
    .form-grid .full {
      grid-column: span 2;
    }
    .form-row {
      display: flex;
      gap: 10px;
      grid-column: span 2;
    }
    .form-row .input-box {
      flex: 1;
    }
    .form-row .input-box.wide { 
      flex: 1.5; 
    }
    .form-row .input-box.small { 
      flex: 1.0; 
    }
    .input-box {
      position: relative;
    }
    .input-box i {
      position: absolute;
      left: 10px;
      top: 50%;
      transform: translateY(-50%);
      color: #174b3b;
      font-size: 16px;
    }
    .input-box input,
    .input-box select {
      width: 100%;
      padding: 10px 10px 10px 33px;
      border-radius: 4px;
      border: 1px solid #ccc;
      background-color: #DBDBDB;
      font-size: 16px;
      outline: none;
      box-sizing: border-box;
      color: #222; 
      font-family: 'Khand', 'Rajdhani', sans-serif;
      font-weight: 500;
      box-shadow: inset 0 6px 8px -6px rgba(0, 0, 0, 0.29);
      transition: box-shadow 150ms ease, border-color 150ms ease;
    }
    .input-box input:focus,
    .input-box select:focus {
    box-shadow: inset 0 6px 8px -6px rgba(0, 0, 0, 0.29);
    border-color: #174b3b;
    }
    .input-box input::placeholder {
    color: #8E8588;
    opacity: 1; 
    }s
    .input-box select {
      color: #8E8588;
    }
    .input-box select:focus,
    .input-box select:not(:invalid) {
      color: #222;
    } 
    .input-box select option[disabled] { color: #9b9b9b; }
    .input-box input:focus,
    .input-box select:focus {
      border-color: #174b3b;
    }
    .input-box select {
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg width='12' height='12' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolygon points='0,0 12,0 6,7' fill='%23174b3b'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 10px center;
      background-size: 10px;
      color:  #8E8588;
    }
    .input-box select option { color: #222; }
    .input-box select option[disabled] { color: #9b9b9b; }
    .input-box select:focus,
    .input-box select:not(:invalid) { color: #222; }
    .register {
      margin-top: 10px;
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
    .submit-btn {
      background-color: #174b3b;
      color: #FECA37;
      border: none;
      padding: 12px 35px;
      font-size: 18px;
      font-weight: 700;
      border-radius: 4px;
      cursor: pointer;
      transition: 0.3s;
      margin-top: 15px;
    }
    .submit-btn:hover {
      background-color: #113a2d;
    }
    .right {
      flex: 0.4;
      background-color: #174b3b;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: row;
      gap: 20px;
      color: #080808ff;
      padding: 30px;
    }
    .right img {
      width: 150px;
      margin-bottom: 0;
    }
    .right h3 {
      color: #FECA37;
      font-size: 26px;
      font-weight: 700;
      letter-spacing: 1px;
      text-align: left;
    }
    /* Autofill: preserve background and keep inset 3D shadow */
    input:-webkit-autofill,
    textarea:-webkit-autofill,
    select:-webkit-autofill {
      -webkit-box-shadow: inset 0 6px 8px -6px rgba(0,0,0,0.29), inset 0 0 0 1000px #DBDBDB !important;
      box-shadow: inset 0 6px 8px -6px rgba(0,0,0,0.29), inset 0 0 0 1000px #DBDBDB !important;
      background-color: #DBDBDB !important;
      background-clip: padding-box !important;
      -webkit-text-fill-color: #222 !important;
    }

    /* Autofill + focus: stronger inset for focused depth */
    input:-webkit-autofill:focus,
    textarea:-webkit-autofill:focus,
    select:-webkit-autofill:focus {
      -webkit-box-shadow: inset 0 8px 14px -8px rgba(0,0,0,0.29), inset 0 0 0 1000px #DBDBDB !important;
      box-shadow: inset 0 8px 14px -8px rgba(0,0,0,0.29), inset 0 0 0 1000px #DBDBDB !important;
      background-color: #DBDBDB !important;
      border-color: #174b3b !important;
      -webkit-text-fill-color: #222 !important;
    }

    /* Firefox autofill */
    input:-moz-autofill {
      box-shadow: inset 0 6px 8px -6px rgba(0,0,0,0.29), 0 0 0 1000px #DBDBDB inset !important;
      background-color: #DBDBDB !important;
      -moz-text-fill-color: #222 !important;
    }
    @media (max-width: 800px) {
      .container { flex-direction: column; width: 90%; }
      .right { display: none; }
    }
  </style>
</head>
<body>

  <div class="container">
    <!-- Left Side: Registration Form -->
    <div class="left">
      <h2>SIGN UP</h2>
      <form action="sign_up_connect.php" method="POST">
        <div class="form-grid">

          <div class="input-box full">
            <i class="fa fa-id-card"></i>
            <input type="text" name="student_number" placeholder="Student Number" required>
          </div>
          
          <div class="form-row">
            <div class="input-box">
              <i class="fa fa-book"></i>
              <select name="course" required>
                <option value="" disabled selected>Course</option>
                <option value="COL">COL </option>
                <option value="CHS">CHS</option>
                <option value="CED">CED</option>
                <option value="CCS">CCS</option>
                <option value="CBA">CBA</option>
                <option value="CAS">CAS</option>
              </select>
            </div>
            <div class="input-box">
              <i class="fa fa-calendar"></i>
              <input type="text" name="year_level" placeholder="Year" required>
            </div>
            <div class="input-box">
              <i class="fa fa-users"></i>
              <input type="text" name="section" placeholder="Section" required>
            </div>
          </div>

          <div class="form-row">
            <div class="input-box wide">
              <i class="fa fa-user"></i>
              <input type="text" name="last_name" placeholder="Last Name" required>
            </div>
            <div class="input-box wide">
              <i class="fa fa-user"></i>
              <input type="text" name="first_name" placeholder="First Name" required>
            </div>
            <div class="input-box small">
              <i class="fa fa-user"></i>
              <input type="text" name="middle_initial" placeholder="M.I.">
            </div>
            <div class="input-box small">
              <i class="fa fa-user"></i>
              <input type="text" name="suffix" placeholder="Suffix">
            </div>
          </div>

          <div class="form-row">
            <div class="input-box wide">
              <i class="fa fa-birthday-cake"></i>
              <input id="birthday" type="text" name="birthday" placeholder="Birthday" required> 
            </div>
            <div class="input-box small">
              <i class="fa fa-venus-mars"></i>
              <select name="sex" required>
                <option value="" disabled selected>Sex</option>
                <option>Male</option>
                <option>Female</option>
              </select>
            </div>
            <div class="input-box wide">
              <i class="fa fa-phone"></i>
              <input type="text" name="contact_no" placeholder="Contact No" required>
            </div>
          </div>

          <div class="form-row">
            <div class="input-box ">
              <i class="fa fa-home"></i>
              <input type="text" name="home_address" placeholder="Home Address" required>
            </div>
            <div class="input-box ">
              <i class="fa fa-envelope"></i>
              <input type="email" name="email" placeholder="Email Address" required>
            </div>
          </div>

          <div class="form-row">
            <div class="input-box">
              <i class="fa fa-lock"></i>
              <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="input-box">
              <i class="fa fa-lock"></i>
              <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
          </div>
        </div>

        <div class="register">
          Already have an account? <a href="login.php">Click Here.</a>
        </div>

        <button type="submit" class="submit-btn">SUBMIT</button>
      </form>
    </div>

    <!-- Right Side: Logo Section -->
    <div class="right">
      <img src="yudiem.png" alt="University Logo">
      <h3>UNIVERSITY LIBRARY</h3>
    </div>
  </div>

  <script>
  // Switch the birthday field to date picker on focus, revert to text (so placeholder shows) on blur if empty
    (function(){
      const b = document.getElementById('birthday');
      if (!b) return;
      b.addEventListener('focus', function(){ this.type = 'date'; });
      b.addEventListener('blur', function(){ if (!this.value) this.type = 'text'; });
    })();
  </script>
</body>
</html>
