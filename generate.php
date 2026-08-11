<?php
require_once 'config.php';
requireAdmin();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $exam_name = trim($_POST['exam_name']);
    $subject   = trim($_POST['subject']);
    $exam_date = $_POST['exam_date'];

    if (!$exam_name || !$subject || !$exam_date) {
        $msg = ['type'=>'error', 'text'=>'❌ Please fill all fields!'];
    } else {

        // Step 1: Fetch students grouped by branch
        $result = $conn->query("SELECT * FROM students ORDER BY branch, roll_no");
        $by_branch = [];
        while($s = $result->fetch_assoc()) {
            $by_branch[$s['branch']][] = $s;
        }

        if (empty($by_branch)) {
            $msg = ['type'=>'error', 'text'=>'❌ No students found! Add students first.'];
        } else {

            // Step 2: Interleave students (round-robin by branch)
            // This ensures no two same-branch students sit together
            $interleaved = [];
            $branches = array_keys($by_branch);
            $maxLen = max(array_map('count', $by_branch));
            for ($i = 0; $i < $maxLen; $i++) {
                foreach ($branches as $branch) {
                    if (isset($by_branch[$branch][$i])) {
                        $interleaved[] = $by_branch[$branch][$i];
                    }
                }
            }

            // Step 3: Fetch rooms
            $rooms_result = $conn->query("SELECT * FROM rooms ORDER BY room_no");
            $rooms = [];
            while($r = $rooms_result->fetch_assoc()) $rooms[] = $r;

            if (empty($rooms)) {
                $msg = ['type'=>'error', 'text'=>'❌ No rooms! Add rooms first.'];
            } else {
                $total_cap = array_sum(array_column($rooms, 'capacity'));
                $total_stu = count($interleaved);

                if ($total_stu > $total_cap) {
                    $msg = ['type'=>'error', 'text'=>"❌ Not enough seats! Students: $total_stu, Capacity: $total_cap. Add more rooms!"];
                } else {

                    // Save exam
                    $stmt = $conn->prepare("INSERT INTO exams (exam_name, exam_date, subject) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $exam_name, $exam_date, $subject);
                    $stmt->execute();
                    $exam_id = $conn->insert_id;

                    // Step 4: BENCH SEATING ALGORITHM
                    // For each room: fill benches row by row, position by position
                    // bench_size=1: 1 student per bench
                    // bench_size=2: 2 students per bench (left, right)
                    // bench_size=3: 3 students per bench (left, middle, right)
                    // KEY: students at same bench are from DIFFERENT branches (already interleaved)

                    $stu_index = 0;
                    $stmt2 = $conn->prepare("INSERT INTO seating (exam_id, room_id, student_id, seat_row, seat_col, bench_position) VALUES (?, ?, ?, ?, ?, ?)");

                    foreach ($rooms as $room) {
                        if ($stu_index >= $total_stu) break;
                        $bench_size = $room['bench_size'];

                        for ($row = 1; $row <= $room['rows']; $row++) {
                            for ($col = 1; $col <= $room['cols']; $col++) {
                                for ($pos = 1; $pos <= $bench_size; $pos++) {
                                    if ($stu_index >= $total_stu) break 3;
                                    $stu = $interleaved[$stu_index];
                                    $stmt2->bind_param("iiiiii",
                                        $exam_id, $room['id'], $stu['id'],
                                        $row, $col, $pos
                                    );
                                    $stmt2->execute();
                                    $stu_index++;
                                }
                            }
                        }
                    }

                    header("Location: view_seating.php?exam_id=$exam_id&new=1");
                    exit;
                }
            }
        }
    }
}

$students_count = $conn->query("SELECT COUNT(*) as c FROM students")->fetch_assoc()['c'];
$rooms_result   = $conn->query("SELECT *, (`rows` * cols * bench_size) as cap FROM rooms ORDER BY room_no");
$branches_res   = $conn->query("SELECT branch, COUNT(*) as cnt FROM students GROUP BY branch ORDER BY branch");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Generate Seating - ExamSeat Pro</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

<nav class="navbar">
    <div class="logo">🏛️ GGV Exam Seating</div>
    <div class="nav-links">
        <a href="index.php">Dashboard</a>
        <a href="students.php">Students</a>
        <a href="rooms.php">Rooms</a>
        <a href="generate.php" class="active">Generate</a>
        <a href="view_seating.php">View Seats</a>
        <a href="logout.php" style="color:var(--danger);">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="page-title">
        <h1>🎲 <span>Generate</span> Seating</h1>
        <p>Auto-arrange students with branch mixing + bench seating</p>
    </div>

    <?php if($msg): ?>
        <div class="alert alert-<?= $msg['type']==='success'?'success':'error' ?>"><?= $msg['text'] ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid" style="margin-bottom:1.5rem;">
        <div class="stat-card">
            <div class="stat-icon">👨‍🎓</div>
            <div class="stat-info"><h3><?= $students_count ?></h3><p>Students</p></div>
        </div>
        <?php
        $rooms_result->data_seek(0);
        $total_cap = 0;
        $rooms_arr = [];
        while($r = $rooms_result->fetch_assoc()) { $total_cap += $r['cap']; $rooms_arr[] = $r; }
        ?>
        <div class="stat-card">
            <div class="stat-icon">💺</div>
            <div class="stat-info"><h3><?= $total_cap ?></h3><p>Total Seats</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><?= $total_cap >= $students_count ? '✅' : '❌' ?></div>
            <div class="stat-info">
                <h3 style="color:<?= $total_cap >= $students_count ? 'var(--accent3)' : 'var(--danger)' ?>">
                    <?= $total_cap >= $students_count ? 'OK' : 'LOW' ?>
                </h3>
                <p>Seat Capacity</p>
            </div>
        </div>
    </div>

    <!-- Branch breakdown -->
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-title">📊 Students by Branch</div>
        <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
        <?php
        $bcolors2 = ['CSE'=>'badge-blue','ECE'=>'badge-green','ME'=>'badge-orange','CIVIL'=>'badge-purple','IT'=>'badge-blue'];
        $branches_res->data_seek(0);
        while($b = $branches_res->fetch_assoc()):
            $bc = $bcolors2[$b['branch']] ?? 'badge-blue';
        ?>
            <div style="background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:0.6rem 1rem;display:flex;align-items:center;gap:0.5rem;">
                <span class="badge <?= $bc ?>"><?= $b['branch'] ?></span>
                <span style="font-family:'JetBrains Mono',monospace;font-weight:700;"><?= $b['cnt'] ?></span>
            </div>
        <?php endwhile; ?>
        </div>
    </div>

    <?php if($students_count == 0): ?>
        <div class="alert alert-error">❌ <a href="students.php" style="color:inherit;font-weight:bold;">Add students first →</a></div>
    <?php elseif(empty($rooms_arr)): ?>
        <div class="alert alert-error">❌ <a href="rooms.php" style="color:inherit;font-weight:bold;">Add rooms first →</a></div>
    <?php else: ?>

    <!-- Rooms preview -->
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-title">🏫 Rooms to be Used</div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Room</th><th>Rows</th><th>Bench Cols</th><th>Per Bench</th><th>Capacity</th></tr></thead>
                <tbody>
                <?php foreach($rooms_arr as $r): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($r['room_no']) ?></strong></td>
                    <td><?= $r['rows'] ?></td>
                    <td><?= $r['cols'] ?></td>
                    <td><span class="badge badge-blue"><?= $r['bench_size'] ?> per bench</span></td>
                    <td><span class="badge badge-green">💺 <?= $r['cap'] ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Generation Form -->
    <div class="card">
        <div class="card-title">📝 Exam Details</div>

        <div class="alert alert-info" style="margin-bottom:1.2rem;">
            🧠 <strong>Algorithm:</strong> Students are interleaved by branch (CSE→ECE→ME→CIVIL→repeat), then placed bench by bench. Students on the same bench are always from different branches!
        </div>

        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Exam Name *</label>
                    <input type="text" name="exam_name" placeholder="e.g. Mid-Term Exam 2025" required>
                </div>
                <div class="form-group">
                    <label>Subject *</label>
                    <input type="text" name="subject" placeholder="e.g. Data Structures" required>
                </div>
                <div class="form-group">
                    <label>Exam Date *</label>
                    <input type="date" name="exam_date" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="form-actions" style="margin-top:1.5rem;">
                <button type="submit" name="generate" class="btn btn-primary" style="font-size:1rem;padding:0.8rem 2rem;">
                    🎲 Generate Seating Arrangement
                </button>
            </div>
        </form>
    </div>

    <?php endif; ?>
</div>
</body>
</html>
