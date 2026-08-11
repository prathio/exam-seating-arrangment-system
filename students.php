<?php
// students.php
require_once 'config.php';
requireAdmin();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $roll   = trim($_POST['roll_no']);
    $name   = trim($_POST['name']);
    $branch = trim($_POST['branch']);
    $sem    = trim($_POST['semester']);
    $email  = trim($_POST['email']);
    $phone  = trim($_POST['phone']);

    if ($roll && $name && $branch && $sem) {
        $stmt = $conn->prepare("INSERT INTO students (roll_no, name, branch, semester, email, phone) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssss", $roll, $name, $branch, $sem, $email, $phone);
        if ($stmt->execute()) {
            $stu_id = $conn->insert_id;
            // Create login account for student
            $pass = md5($roll);
            $ustmt = $conn->prepare("INSERT INTO users (username, password, role, student_id) VALUES (?,?,'student',?)");
            $ustmt->bind_param("ssi", $roll, $pass, $stu_id);
            $ustmt->execute();
            $msg = ['type'=>'success', 'text'=>"✅ Student '$name' added! Login: username=<strong>$roll</strong>, password=<strong>$roll</strong>"];
        } else {
            $msg = ['type'=>'error', 'text'=>"❌ Roll number already exists!"];
        }
    } else {
        $msg = ['type'=>'error', 'text'=>"❌ Fill all required fields!"];
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE student_id=$id");
    $conn->query("DELETE FROM students WHERE id=$id");
    $msg = ['type'=>'success', 'text'=>"🗑️ Student deleted."];
}

$students = $conn->query("SELECT * FROM students ORDER BY branch, roll_no");
$bcolors = ['CSE'=>'badge-blue','ECE'=>'badge-green','ME'=>'badge-orange','CIVIL'=>'badge-purple','IT'=>'badge-blue','EE'=>'badge-red'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Students - ExamSeat Pro</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

<nav class="navbar">
    <div class="logo">🏛️ GGV Exam Seating</div>
    <div class="nav-links">
        <a href="index.php">Dashboard</a>
        <a href="students.php" class="active">Students</a>
        <a href="rooms.php">Rooms</a>
        <a href="generate.php">Generate</a>
        <a href="view_seating.php">View Seats</a>
        <a href="logout.php" style="color:var(--danger);">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="page-title">
        <h1>👨‍🎓 <span>Students</span></h1>
        <p>Manage student records — login is auto-created (password = roll number)</p>
    </div>

    <?php if($msg): ?>
        <div class="alert alert-<?= $msg['type']==='success'?'success':'error' ?>"><?= $msg['text'] ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-title">➕ Add Student</div>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Roll Number *</label>
                    <input type="text" name="roll_no" placeholder="e.g. CSE001" required>
                </div>
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" placeholder="e.g. Aarav Sharma" required>
                </div>
                <div class="form-group">
                    <label>Branch *</label>
                    <select name="branch" required>
                        <option value="">-- Select --</option>
                        <option>CSE</option><option>ECE</option><option>ME</option>
                        <option>CIVIL</option><option>IT</option><option>EE</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Semester *</label>
                    <select name="semester" required>
                        <option value="">-- Select --</option>
                        <?php for($s=1;$s<=8;$s++): ?>
                        <option value="<?=$s?>th"><?=$s?>th Semester</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="student@college.edu">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" placeholder="9876543210">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" name="add_student" class="btn btn-primary">➕ Add Student</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-title">📋 All Students (<?= $students->num_rows ?>)</div>
        <?php if($students->num_rows > 0): ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>#</th><th>Roll No.</th><th>Name</th><th>Branch</th><th>Semester</th><th>Email</th><th>Login Pass</th><th>Actions</th></tr></thead>
                <tbody>
                <?php $i=1; while($s=$students->fetch_assoc()):
                    $bc = $bcolors[$s['branch']] ?? 'badge-blue';
                ?>
                <tr>
                    <td><?=$i++?></td>
                    <td><span style="font-family:'JetBrains Mono',monospace;font-weight:700;color:var(--accent2)"><?= htmlspecialchars($s['roll_no']) ?></span></td>
                    <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                    <td><span class="badge <?=$bc?>"><?=$s['branch']?></span></td>
                    <td><?=$s['semester']?></td>
                    <td style="font-size:0.8rem;color:var(--muted);"><?= $s['email'] ?: '—' ?></td>
                    <td><span style="font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:var(--accent3);"><?= $s['roll_no'] ?></span></td>
                    <td><a href="students.php?delete=<?=$s['id']?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">🗑</a></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-info">No students yet.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
