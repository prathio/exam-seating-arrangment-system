<?php
require_once 'config.php';
if (isLoggedIn()) { header("Location: ".(isAdmin()?"index.php":"student_dashboard.php")); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $username = trim($_POST['username']);
    $password = md5(trim($_POST['password']));
    $stmt = $conn->prepare("SELECT u.*, s.name as student_name FROM users u LEFT JOIN students s ON s.id=u.student_id WHERE u.username=? AND u.password=?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user) {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['role']       = $user['role'];
        $_SESSION['name']       = $user['role']==='admin' ? 'Administrator' : $user['student_name'];
        $_SESSION['student_id'] = $user['student_id'];
        header("Location: ".($user['role']==='admin'?"index.php":"student_dashboard.php")); exit;
    } else {
        $error = "Invalid username or password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login — GGV CSE Exam Seating System</title>
<link rel="stylesheet" href="style.css">
<link rel="icon" href="logo.png">
<style>
body{background:#F0ECE3;min-height:100vh;display:flex;flex-direction:column;}
.login-page{flex:1;display:flex;align-items:center;justify-content:center;padding:24px;}
.login-wrap{display:grid;grid-template-columns:1.1fr 1fr;max-width:780px;width:100%;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 16px 48px rgba(139,26,26,0.15);border:0.5px solid #ddd;}
.login-left{background:linear-gradient(160deg,var(--maroon-deep),var(--maroon));padding:32px 28px;color:#fff;display:flex;flex-direction:column;}
.ll-logo{display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid rgba(255,255,255,0.15);}
.ll-logo img{width:56px;height:56px;object-fit:contain;filter:drop-shadow(0 2px 4px rgba(0,0,0,0.3));}
.ll-uni{font-family:'Noto Serif',Georgia,serif;font-size:14px;font-weight:700;color:#fff;line-height:1.3;}
.ll-uni span{color:var(--gold-light);display:block;font-size:11px;font-weight:600;margin-top:2px;}
.ll-title{font-family:'Noto Serif',Georgia,serif;font-size:17px;font-weight:700;margin-bottom:8px;line-height:1.3;}
.ll-title em{color:var(--gold-light);font-style:normal;}
.ll-desc{font-size:11px;opacity:0.75;line-height:1.7;margin-bottom:16px;}
.ll-features{list-style:none;margin-bottom:auto;}
.ll-features li{font-size:11px;opacity:0.8;margin-bottom:7px;display:flex;align-items:flex-start;gap:6px;line-height:1.4;}
.ll-feat-dot{width:6px;height:6px;border-radius:50%;background:var(--gold-light);flex-shrink:0;margin-top:4px;}
.ll-credit{border-top:1px solid rgba(255,255,255,0.15);padding-top:14px;margin-top:16px;}
.ll-credit .cr-label{font-size:10px;opacity:0.5;}
.ll-credit .cr-dept{font-size:11px;font-weight:700;color:var(--gold-light);margin-top:2px;}
.ll-credit .cr-uni{font-size:10px;opacity:0.55;margin-top:1px;}
.login-right{padding:32px 28px;}
.lr-title{font-family:'Noto Serif',Georgia,serif;font-size:18px;font-weight:700;color:var(--maroon-dark);margin-bottom:4px;}
.lr-sub{font-size:12px;color:var(--muted);margin-bottom:18px;}
.login-tabs{display:flex;background:var(--surface2);border-radius:6px;padding:3px;margin-bottom:16px;gap:2px;}
.login-tab{flex:1;text-align:center;padding:7px;border-radius:4px;font-size:11px;font-weight:700;cursor:pointer;color:var(--muted);transition:all 0.15s;}
.login-tab.active{background:var(--maroon);color:#fff;}
.hint-info{background:var(--surface2);border:0.5px solid var(--border);border-radius:6px;padding:10px 12px;font-size:11px;color:var(--muted);margin-top:12px;line-height:1.7;}
.hint-info strong{color:var(--text);}
@media(max-width:620px){.login-wrap{grid-template-columns:1fr;}.login-left{display:none;}}
</style>
</head>
<body>

<!-- GGV Header -->
<div class="ggv-header">
  <div class="header-topbar">
    <span>Guru Ghasidas Vishwavidyalaya — A Central University &nbsp;|&nbsp; Bilaspur, Chhattisgarh</span>
    <div style="display:flex;align-items:center;gap:6px;font-size:11px;">
      <a href="https://www.ggu.ac.in" target="_blank" style="color:var(--gold-light);text-decoration:none;">Official Website</a>
    </div>
  </div>
  <div class="header-main">
    <img src="logo.png" alt="GGV Logo" class="uni-logo-img" onerror="this.style.display='none'">
    <div class="uni-text">
      <div class="uni-hindi">गुरु घासीदास विश्वविद्यालय, बिलासपुर (केन्द्रीय विश्वविद्यालय)</div>
      <div class="uni-name-main">Guru Ghasidas <em>Vishwavidyalaya</em></div>
      <div class="uni-dept">Department of Computer Science &amp; Engineering (CSE) &nbsp;|&nbsp; Exam Seating Arrangement System</div>
      <div class="uni-address">Koni, Bilaspur, Chhattisgarh — 495009 &nbsp;|&nbsp; ☎ 07752-260283</div>
    </div>
    <div class="header-badges">
      <div class="hbadge">&#9733; NAAC A++</div>
      <div class="hbadge">Central University</div>
      <div class="hbadge">UGC Recognised</div>
      <div class="hbadge">Est. 1983</div>
    </div>
  </div>
</div>

<div class="notice-ticker">
  <div class="notice-tag">NOTICE</div>
  <div class="notice-scroll">B.Tech Semester V Seating Arrangement Published &nbsp;|&nbsp; NAAC A++ Accreditation Achieved &nbsp;|&nbsp; CUET UG 2026 Admissions Open &nbsp;|&nbsp; New CSE Automated Seating System Live &nbsp;|&nbsp; PhD VRET 2026 Registrations Open</div>
</div>

<!-- Login Content -->
<div class="login-page">
  <div class="login-wrap">

    <!-- Left Info Panel -->
    <div class="login-left">
      <div class="ll-logo">
        <img src="logo.png" alt="GGV" onerror="this.style.display='none'">
        <div class="ll-uni">
          Guru Ghasidas Vishwavidyalaya
          <span>Bilaspur, Chhattisgarh</span>
        </div>
      </div>
      <div class="ll-title">Exam Seating Arrangement <em>System</em></div>
      <div class="ll-desc">Automated, secure, and fair seating management for all university examinations — developed by the CSE Department, IT-GGV.</div>
      <ul class="ll-features">
        <li><div class="ll-feat-dot"></div> Automatic branch-wise interleaved seating</li>
        <li><div class="ll-feat-dot"></div> Bench seating — 1, 2, or 3 students per bench</li>
        <li><div class="ll-feat-dot"></div> Room-wise printable seating charts</li>
        <li><div class="ll-feat-dot"></div> Student portal — check own seat by roll number</li>
        <li><div class="ll-feat-dot"></div> Attendance sheet auto-generation</li>
        <li><div class="ll-feat-dot"></div> Multi-room configurable capacity</li>
      </ul>
      <div class="ll-credit">
        <div class="cr-label">Developed by</div>
        <div class="cr-dept">Dept. of Computer Science &amp; Engineering</div>
        <div class="cr-uni">IT-GGV, Bilaspur &nbsp;|&nbsp; Academic Year 2025–26</div>
      </div>
    </div>

    <!-- Right Login Form -->
    <div class="login-right">
      <div class="lr-title">&#128274; Sign In</div>
      <div class="lr-sub">Access your GGV Exam Seating account</div>

      <div class="login-tabs">
        <div class="login-tab active" id="tab-admin" onclick="switchTab('admin')">&#128100; Admin</div>
        <div class="login-tab" id="tab-student" onclick="switchTab('student')">&#127979; Student</div>
      </div>

      <?php if($error): ?>
        <div class="alert alert-error">&#10060; <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group" style="margin-bottom:12px;">
          <label id="lbl-user">Username / Employee ID</label>
          <input type="text" name="username" id="inp-user" placeholder="Enter username" required autocomplete="off" style="font-size:13px;">
        </div>
        <div class="form-group" style="margin-bottom:16px;">
          <label>Password</label>
          <input type="password" name="password" placeholder="Enter password" required style="font-size:13px;">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:10px;font-size:12px;letter-spacing:0.06em;">
          LOGIN TO SYSTEM &#8594;
        </button>
      </form>

      <div class="hint-info" id="hint-admin">
        <strong>Admin login:</strong><br>
        Username: <strong>admin</strong> &nbsp;|&nbsp; Password: <strong>admin123</strong>
      </div>
      <div class="hint-info" id="hint-student" style="display:none;">
        <strong>Student login:</strong><br>
        Username: <strong>Roll Number</strong> (e.g. CSE001)<br>
        Password: <strong>Same as Roll Number</strong>
      </div>

      <div style="text-align:center;margin-top:16px;font-size:10px;color:var(--muted);line-height:1.8;">
        Guru Ghasidas Vishwavidyalaya, Bilaspur (CG)<br>
        For technical support: <a href="mailto:exam.cell@ggu.ac.in" style="color:var(--maroon);">exam.cell@ggu.ac.in</a>
      </div>
    </div>

  </div>
</div>

<div class="ggv-footer">
  <strong>Guru Ghasidas Vishwavidyalaya (A Central University)</strong> — Koni, Bilaspur, Chhattisgarh 495009<br>
  <a href="https://www.ggu.ac.in">www.ggu.ac.in</a> &nbsp;|&nbsp; NAAC A++ &nbsp;|&nbsp; Exam Seating System &copy; <?= date('Y') ?> &nbsp;|&nbsp; CSE Dept, IT-GGV
</div>

<script>
function switchTab(role) {
  document.getElementById('tab-admin').classList.toggle('active', role==='admin');
  document.getElementById('tab-student').classList.toggle('active', role==='student');
  document.getElementById('hint-admin').style.display   = role==='admin'   ? 'block' : 'none';
  document.getElementById('hint-student').style.display = role==='student' ? 'block' : 'none';
  document.getElementById('lbl-user').textContent = role==='student' ? 'Roll Number' : 'Username / Employee ID';
  document.getElementById('inp-user').placeholder  = role==='student' ? 'e.g. CSE001' : 'Enter username';
}
</script>
</body>
</html>
