<?php // header.php — GGV Official Header — include 'header.php'; in every page ?>
<div class="ggv-header">
  <div class="header-topbar">
    <div>
      <span>Screen Reader Access</span>
      <a href="#">Skip to Main</a>
      <a href="#">Sitemap</a>
      <a href="https://www.ggu.ac.in" target="_blank">Official GGV Website</a>
    </div>
    <div style="display:flex;align-items:center;gap:6px;font-size:11px;">
      <span style="color:rgba(255,255,255,0.5);">A-</span>
      <span style="color:#fff;font-weight:700;">A</span>
      <span style="color:rgba(255,255,255,0.5);">A+</span>
      <span style="margin:0 6px;opacity:0.3;">|</span>
      <a href="#">हिन्दी</a>
      <a href="#" style="margin-left:8px;">English</a>
    </div>
  </div>
  <div class="header-main">
    <img src="logo.png" alt="GGV Logo" class="uni-logo-img" onerror="this.style.display='none'">
    <div class="uni-text">
      <div class="uni-hindi">गुरु घासीदास विश्वविद्यालय, बिलासपुर (केन्द्रीय विश्वविद्यालय)</div>
      <div class="uni-name-main">Guru Ghasidas <em>Vishwavidyalaya</em></div>
      <div class="uni-dept">Department of Computer Science &amp; Engineering (CSE) &nbsp;|&nbsp; School of Studies in Engineering &amp; Technology</div>
      <div class="uni-address">Koni, Bilaspur, Chhattisgarh — 495009 &nbsp;|&nbsp; ☎ 07752-260283 &nbsp;|&nbsp; centralggu@ggu.ac.in</div>
    </div>
    <div class="header-badges">
      <div class="hbadge">&#9733; NAAC A++</div>
      <div class="hbadge">Central University</div>
      <div class="hbadge">UGC Recognised</div>
      <div class="hbadge">Est. 1983</div>
    </div>
  </div>
  <nav class="uni-nav no-print">
    <div class="uni-nav-inner">
      <a class="nav-item <?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>" href="index.php">Home</a>
      <a class="nav-item" href="https://www.ggu.ac.in/aboutus" target="_blank">About GGV</a>
      <a class="nav-item <?= basename($_SERVER['PHP_SELF'])=='students.php'?'active':'' ?>" href="students.php">Students</a>
      <a class="nav-item <?= basename($_SERVER['PHP_SELF'])=='rooms.php'?'active':'' ?>" href="rooms.php">Rooms</a>
      <a class="nav-item <?= basename($_SERVER['PHP_SELF'])=='generate.php'?'active':'' ?>" href="generate.php">Generate Seating</a>
      <a class="nav-item <?= basename($_SERVER['PHP_SELF'])=='view_seating.php'?'active':'' ?>" href="view_seating.php">View Chart</a>
      <a class="nav-item" href="https://www.ggu.ac.in" target="_blank">GGV Portal</a>
      <a class="nav-item" href="logout.php" style="color:rgba(255,210,100,0.8);">Logout</a>
      <div class="nav-search"><input type="text" placeholder="Search..."></div>
    </div>
  </nav>
</div>
<div class="notice-ticker no-print">
  <div class="notice-tag">NOTICE</div>
  <div class="notice-scroll">
    B.Tech Semester V Examination Seating Published — Check Your Seat Online &nbsp;|&nbsp;
    NAAC A++ Accreditation Achieved by GGV Bilaspur &nbsp;|&nbsp;
    CUET UG 2026 Admissions Open — Apply at ggu.ac.in &nbsp;|&nbsp;
    Automated Exam Seating System developed by Dept. of CSE, IT-GGV &nbsp;|&nbsp;
    PhD Registrations Open — VRET Entrance Exam 2026
  </div>
</div>
