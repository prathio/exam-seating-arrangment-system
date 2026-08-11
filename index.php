<?php
require_once 'config.php';
requireAdmin();
$students_count = $conn->query("SELECT COUNT(*) as c FROM students")->fetch_assoc()['c'];
$rooms_count    = $conn->query("SELECT COUNT(*) as c FROM rooms")->fetch_assoc()['c'];
$exams_count    = $conn->query("SELECT COUNT(*) as c FROM exams")->fetch_assoc()['c'];
$seated_count   = $conn->query("SELECT COUNT(DISTINCT student_id) as c FROM seating")->fetch_assoc()['c'];
$exams = $conn->query("SELECT e.*, COUNT(s.id) as seated FROM exams e LEFT JOIN seating s ON s.exam_id=e.id GROUP BY e.id ORDER BY e.created_at DESC LIMIT 6");
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard — GGV CSE Exam Seating System</title>
<link rel="stylesheet" href="style.css">
<link rel="icon" href="logo.png">
</head>
<body>

<?php include 'header.php'; ?>

<div class="site-body">
  <!-- LEFT MAIN CONTENT -->
  <div>

    <!-- Hero Banner -->
    <div class="hero-banner">
      <div class="hero-title">Automatic Exam Seating Arrangement <em>System</em></div>
      <div class="hero-sub">
        Department of Computer Science &amp; Engineering, IT-GGV, Bilaspur.<br>
        Secure, fair, and branch-mixed seating for all university examinations.<br>
        Welcome back, <strong><?= htmlspecialchars($user['name']) ?></strong> — Academic Year 2025–26
      </div>
      <div class="hero-actions">
        <a href="generate.php" class="btn btn-gold">&#127922; Generate Seating</a>
        <a href="view_seating.php" class="btn btn-outline">&#128202; View Charts</a>
        <a href="students.php" class="btn btn-outline">&#43; Add Student</a>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
      <div class="stat-box"><div class="stat-num"><?= $students_count ?></div><div class="stat-lbl">Students</div></div>
      <div class="stat-box"><div class="stat-num"><?= $rooms_count ?></div><div class="stat-lbl">Exam Rooms</div></div>
      <div class="stat-box"><div class="stat-num"><?= $exams_count ?></div><div class="stat-lbl">Exams Created</div></div>
      <div class="stat-box"><div class="stat-num"><?= $seated_count ?></div><div class="stat-lbl">Students Seated</div></div>
    </div>

    <!-- Quick Actions -->
    <div class="section-card">
      <div class="section-head">Quick Actions</div>
      <div class="section-body">
        <div class="quick-links">
          <a href="students.php" class="quick-card"><div class="qicon">&#128104;&#8205;&#127979;</div><div><h3>Add Student</h3><p>Register new student</p></div></a>
          <a href="rooms.php"    class="quick-card"><div class="qicon">&#127979;</div><div><h3>Add Exam Room</h3><p>Configure hall + bench</p></div></a>
          <a href="generate.php" class="quick-card"><div class="qicon">&#127922;</div><div><h3>Generate Seating</h3><p>Auto-arrange students</p></div></a>
          <a href="view_seating.php" class="quick-card"><div class="qicon">&#128438;</div><div><h3>Print Chart</h3><p>Room-wise layout</p></div></a>
        </div>
      </div>
    </div>

    <!-- Exam Records -->
    <div class="section-card">
      <div class="section-head">
        Exam Seating Records
        <a href="generate.php">+ Generate New</a>
      </div>
      <div class="section-body">
        <?php if($exams->num_rows > 0): ?>
        <div class="table-wrap">
          <table>
            <thead><tr><th>#</th><th>Exam Name</th><th>Subject</th><th>Date</th><th>Students Seated</th><th>Action</th></tr></thead>
            <tbody>
            <?php $i=1; while($e=$exams->fetch_assoc()): ?>
            <tr>
              <td><?=$i++?></td>
              <td><strong><?= htmlspecialchars($e['exam_name']) ?></strong></td>
              <td><?= htmlspecialchars($e['subject']) ?></td>
              <td><?= date('d M Y', strtotime($e['exam_date'])) ?></td>
              <td><span class="badge badge-green">&#128698; <?=$e['seated']?> students</span></td>
              <td>
                <a href="view_seating.php?exam_id=<?=$e['id']?>" class="btn btn-blue btn-sm">View</a>
                <a href="delete_exam.php?id=<?=$e['id']?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this exam?')">Delete</a>
              </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <div class="alert alert-info">No exams yet. <a href="generate.php" style="color:inherit;font-weight:bold;">Generate first seating arrangement &#8594;</a></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- System Features -->
    <div class="section-card">
      <div class="section-head">System Features</div>
      <div class="section-body">
        <div class="feature-item"><div class="fcheck">&#10003;</div> Automatic branch-wise interleaved seating arrangement</div>
        <div class="feature-item"><div class="fcheck">&#10003;</div> Bench seating support — 1, 2, or 3 students per bench</div>
        <div class="feature-item"><div class="fcheck">&#10003;</div> Room-wise printable seating charts with attendance sheet</div>
        <div class="feature-item"><div class="fcheck">&#10003;</div> Student self-service portal — check seat by roll number login</div>
        <div class="feature-item"><div class="fcheck">&#10003;</div> Admin panel — manage students, rooms, and generate arrangements</div>
        <div class="feature-item"><div class="fcheck">&#10003;</div> Multi-room support with configurable capacity and bench layout</div>
      </div>
    </div>

    <!-- University Info -->
    <div class="section-card">
      <div class="section-head">About GGV</div>
      <div class="section-body" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;font-size:12px;">
        <div><div style="font-weight:700;color:var(--maroon-dark);margin-bottom:4px;font-size:11px;text-transform:uppercase;letter-spacing:0.05em;">University</div><div style="color:var(--text-sec);line-height:1.6;">Guru Ghasidas Vishwavidyalaya<br>Koni, Bilaspur, CG — 495009</div></div>
        <div><div style="font-weight:700;color:var(--maroon-dark);margin-bottom:4px;font-size:11px;text-transform:uppercase;letter-spacing:0.05em;">Department</div><div style="color:var(--text-sec);line-height:1.6;">Computer Science &amp; Engineering<br>School of Engineering &amp; Technology</div></div>
        <div><div style="font-weight:700;color:var(--maroon-dark);margin-bottom:4px;font-size:11px;text-transform:uppercase;letter-spacing:0.05em;">Accreditation</div><div style="color:var(--text-sec);line-height:1.6;">NAAC A++ Accredited<br>Central University, Est. 1983</div></div>
        <div><div style="font-weight:700;color:var(--maroon-dark);margin-bottom:4px;font-size:11px;text-transform:uppercase;letter-spacing:0.05em;">Developed By</div><div style="color:var(--text-sec);line-height:1.6;">CSE Dept, IT-GGV<br>Academic Project 2025–26</div></div>
      </div>
    </div>

  </div>

  <!-- RIGHT SIDEBAR -->
  <div>

    <!-- Login Info Box -->
    <div class="login-panel">
      <div class="lp-title">&#128274; Admin Control Panel</div>
      <div style="font-size:11px;color:rgba(255,255,255,0.7);margin-bottom:10px;line-height:1.6;">
        Logged in as <strong style="color:#fff;"><?= htmlspecialchars($user['name']) ?></strong><br>
        Role: <span style="color:var(--gold-light);font-weight:700;">Administrator</span>
      </div>
      <a href="students.php" class="lp-btn" style="display:block;text-align:center;text-decoration:none;margin-bottom:6px;">Manage Students</a>
      <a href="generate.php" class="lp-btn" style="display:block;text-align:center;text-decoration:none;background:var(--maroon-dark);border:1px solid var(--gold);color:var(--gold-light);">Generate Seating</a>
      <div class="lp-hint"><a href="logout.php" style="color:rgba(255,200,100,0.7);">Logout from system</a></div>
    </div>

    <!-- Quick Links -->
    <div class="sidebar-card">
      <div class="sidebar-head">Quick Links</div>
      <div class="sidebar-body">
        <div class="quick-grid">
          <a href="students.php" class="ql-box"><div class="ql-icon">&#128100;</div><div class="ql-lbl">Students</div></a>
          <a href="rooms.php"    class="ql-box"><div class="ql-icon">&#127960;</div><div class="ql-lbl">Rooms</div></a>
          <a href="generate.php" class="ql-box"><div class="ql-icon">&#127922;</div><div class="ql-lbl">Generate</div></a>
          <a href="view_seating.php" class="ql-box"><div class="ql-icon">&#128202;</div><div class="ql-lbl">View Chart</div></a>
          <a href="https://www.ggu.ac.in" target="_blank" class="ql-box"><div class="ql-icon">&#127760;</div><div class="ql-lbl">GGV Portal</div></a>
          <a href="logout.php" class="ql-box"><div class="ql-icon">&#128682;</div><div class="ql-lbl">Logout</div></a>
        </div>
      </div>
    </div>

    <!-- Upcoming Events -->
    <div class="sidebar-card">
      <div class="sidebar-head">Upcoming Events</div>
      <div class="sidebar-body">
        <div class="event-item">
          <div class="event-date"><div class="e-day">22</div><div class="e-mon">APR</div></div>
          <div><div class="event-info-title">B.Tech Sem V Exams Begin</div><div class="event-info-dept">Exam Cell, GGV</div></div>
        </div>
        <div class="event-item">
          <div class="event-date"><div class="e-day">25</div><div class="e-mon">APR</div></div>
          <div><div class="event-info-title">GGV-AITHON 2026 Hackathon</div><div class="event-info-dept">CSE Dept, IT-GGV</div></div>
        </div>
        <div class="event-item">
          <div class="event-date"><div class="e-day">01</div><div class="e-mon">MAY</div></div>
          <div><div class="event-info-title">National Skill Development Workshop</div><div class="event-info-dept">Dept. of Education</div></div>
        </div>
        <div class="event-item">
          <div class="event-date"><div class="e-day">11</div><div class="e-mon">MAY</div></div>
          <div><div class="event-info-title">CUET UG 2026 Examination</div><div class="event-info-dept">NTA / GGV Admissions</div></div>
        </div>
      </div>
    </div>

    <!-- Rankings -->
    <div class="sidebar-card">
      <div class="sidebar-head">Rankings &amp; Recognition</div>
      <div class="sidebar-body">
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:0.5px solid var(--surface3);font-size:11px;"><span style="font-weight:600;color:var(--text-sec);">NIRF Science Rank</span><span style="font-weight:700;color:var(--maroon);">#22</span></div>
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:0.5px solid var(--surface3);font-size:11px;"><span style="font-weight:600;color:var(--text-sec);">NIRF Pharmacy</span><span style="font-weight:700;color:var(--maroon);">#47</span></div>
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:0.5px solid var(--surface3);font-size:11px;"><span style="font-weight:600;color:var(--text-sec);">NAAC Grade</span><span style="font-weight:700;color:var(--success);">A++</span></div>
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:0.5px solid var(--surface3);font-size:11px;"><span style="font-weight:600;color:var(--text-sec);">QS Asia Rank</span><span style="font-weight:700;color:var(--maroon);">#1101</span></div>
        <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:11px;"><span style="font-weight:600;color:var(--text-sec);">Campus Area</span><span style="color:var(--muted);">700 Acres</span></div>
      </div>
    </div>

    <!-- Contact -->
    <div class="sidebar-card">
      <div class="sidebar-head">Contact &amp; Location</div>
      <div class="sidebar-body" style="font-size:11px;color:var(--muted);line-height:2;">
        <div>&#128205; Koni, Bilaspur, CG — 495009</div>
        <div>&#128222; 07752-260283</div>
        <div>&#128231; centralggu@ggu.ac.in</div>
        <div>&#127760; <a href="https://www.ggu.ac.in" style="color:var(--maroon);">www.ggu.ac.in</a></div>
        <div style="margin-top:8px;padding-top:8px;border-top:0.5px solid var(--surface3);font-size:10px;color:var(--muted);">12 km from Bilaspur Railway Station<br>Near Koni, off Bilaspur–Katghora Road</div>
      </div>
    </div>

  </div>
</div>

<div class="ggv-footer">
  <strong>Guru Ghasidas Vishwavidyalaya (A Central University)</strong><br>
  Koni, Bilaspur, Chhattisgarh — 495009 &nbsp;|&nbsp; ☎ 07752-260283 &nbsp;|&nbsp; centralggu@ggu.ac.in &nbsp;|&nbsp; <a href="https://www.ggu.ac.in">www.ggu.ac.in</a><br>
  <span style="color:rgba(255,255,255,0.4);font-size:10px;">Automated Exam Seating System &copy; <?= date('Y') ?> &nbsp;|&nbsp; Developed by: Dept. of Computer Science &amp; Engineering, IT-GGV</span>
</div>

</body>
</html>
