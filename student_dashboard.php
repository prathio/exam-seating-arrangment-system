<?php
require_once 'config.php';
requireLogin();
if (isAdmin()) { header("Location: index.php"); exit; }

$student_id = $_SESSION['student_id'];

// Get student info
$student = $conn->query("SELECT * FROM students WHERE id = $student_id")->fetch_assoc();

// Get all seating assignments for this student
$my_seats = $conn->query("
    SELECT s.*, e.exam_name, e.exam_date, e.subject, r.room_no, r.bench_size
    FROM seating s
    JOIN exams e ON e.id = s.exam_id
    JOIN rooms r ON r.id = s.room_id
    WHERE s.student_id = $student_id
    ORDER BY e.exam_date DESC
");

// Branch colors
$bcolors = ['CSE'=>'badge-blue','ECE'=>'badge-green','ME'=>'badge-orange','CIVIL'=>'badge-purple','IT'=>'badge-blue'];
$bc = $bcolors[$student['branch']] ?? 'badge-blue';

$bench_pos = ['1'=>'Left','2'=>'Middle','3'=>'Right'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Seat - ExamSeat Pro</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

<nav class="navbar">
    <div class="logo">🏛️ GGV Exam Seating</div>
    <div class="nav-links">
        <a href="student_dashboard.php" class="active">My Seat</a>
        <a href="logout.php" style="color:var(--danger);">Logout</a>
    </div>
</nav>

<div class="container">

    <!-- Student Profile Card -->
    <div class="card" style="border-color:var(--accent2);display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;">
        <div style="width:70px;height:70px;border-radius:50%;background:rgba(88,166,255,0.15);border:2px solid var(--accent2);display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0;">
            👨‍🎓
        </div>
        <div>
            <h2 style="font-size:1.3rem;font-weight:800;margin-bottom:0.3rem;"><?= htmlspecialchars($student['name']) ?></h2>
            <div style="display:flex;gap:0.6rem;flex-wrap:wrap;align-items:center;">
                <span class="badge <?= $bc ?>"><?= $student['branch'] ?></span>
                <span class="badge badge-purple"><?= $student['semester'] ?> Sem</span>
                <span style="font-family:'JetBrains Mono',monospace;font-size:0.85rem;color:var(--accent2);"><?= $student['roll_no'] ?></span>
            </div>
            <?php if($student['email']): ?>
            <div style="font-size:0.8rem;color:var(--muted);margin-top:0.4rem;">📧 <?= $student['email'] ?></div>
            <?php endif; ?>
        </div>
        <div style="margin-left:auto;">
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>

    <!-- My Exam Seats -->
    <div class="page-title">
        <h1>💺 <span>My</span> Exam Seats</h1>
        <p>Your assigned seats for upcoming and past exams</p>
    </div>

    <?php if ($my_seats->num_rows === 0): ?>
        <div class="card" style="text-align:center;padding:3rem;">
            <div style="font-size:3rem;margin-bottom:1rem;">🔍</div>
            <h3 style="margin-bottom:0.5rem;">No Seat Assigned Yet</h3>
            <p style="color:var(--muted);">Your seat will appear here once the admin generates the seating arrangement.</p>
        </div>
    <?php else: ?>

    <div style="display:grid;gap:1rem;">
    <?php while($seat = $my_seats->fetch_assoc()):
        $pos_label = $bench_pos[$seat['bench_position']] ?? 'Seat '.$seat['bench_position'];
        $is_upcoming = strtotime($seat['exam_date']) >= strtotime(date('Y-m-d'));
    ?>
        <div class="card" style="border-left:4px solid <?= $is_upcoming ? 'var(--accent3)' : 'var(--border)' ?>;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;">

                <!-- Exam Info -->
                <div>
                    <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:0.5rem;">
                        <h3 style="font-size:1.05rem;font-weight:800;"><?= htmlspecialchars($seat['exam_name']) ?></h3>
                        <?php if($is_upcoming): ?>
                            <span class="badge badge-green">Upcoming</span>
                        <?php else: ?>
                            <span class="badge" style="background:var(--surface2);color:var(--muted);">Past</span>
                        <?php endif; ?>
                    </div>
                    <p style="color:var(--muted);font-size:0.85rem;">📚 <?= htmlspecialchars($seat['subject']) ?></p>
                    <p style="color:var(--muted);font-size:0.85rem;">📅 <?= date('l, d M Y', strtotime($seat['exam_date'])) ?></p>
                </div>

                <!-- Seat Details Box -->
                <div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:1rem 1.5rem;text-align:center;min-width:180px;">
                    <div style="font-size:0.7rem;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.5rem;">Your Seat</div>
                    <div style="font-size:1.8rem;font-weight:800;font-family:'JetBrains Mono',monospace;color:var(--accent2);line-height:1;">
                        R<?= $seat['seat_row'] ?>-C<?= $seat['seat_col'] ?>
                    </div>
                    <?php if($seat['bench_size'] > 1): ?>
                    <div style="margin-top:0.4rem;">
                        <span class="badge badge-purple" style="font-size:0.7rem;"><?= $pos_label ?> seat on bench</span>
                    </div>
                    <?php endif; ?>
                    <div style="margin-top:0.6rem;font-size:0.8rem;color:var(--muted);">🏫 <?= htmlspecialchars($seat['room_no']) ?></div>
                </div>

            </div>

            <!-- Visual bench diagram if bench_size > 1 -->
            <?php if($seat['bench_size'] > 1): ?>
            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border);">
                <div style="font-size:0.75rem;color:var(--muted);margin-bottom:0.5rem;font-weight:700;">BENCH VIEW:</div>
                <div style="display:flex;gap:4px;align-items:center;">
                    <?php for($p=1; $p<=$seat['bench_size']; $p++): ?>
                        <div style="flex:1;padding:0.5rem;border-radius:6px;text-align:center;font-size:0.75rem;font-weight:700;
                            <?= $p == $seat['bench_position']
                                ? 'background:rgba(88,166,255,0.2);border:2px solid var(--accent2);color:var(--accent2);'
                                : 'background:var(--surface2);border:1px dashed var(--border);color:var(--muted);' ?>">
                            <?php
                            $labels = ['Left','Middle','Right'];
                            echo $labels[$p-1] ?? "Seat $p";
                            if($p == $seat['bench_position']) echo "<br>⭐ YOU";
                            ?>
                        </div>
                        <?php if($p < $seat['bench_size']): ?>
                            <div style="width:8px;height:2px;background:var(--border);"></div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    <?php endwhile; ?>
    </div>

    <?php endif; ?>

</div>
</body>
</html>
