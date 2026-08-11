<?php
require_once 'config.php';
requireAdmin();

$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
$is_new  = isset($_GET['new']);

$exams_list = $conn->query("SELECT * FROM exams ORDER BY exam_date DESC");
$selected   = null;
if ($exam_id) {
    $selected = $conn->query("SELECT * FROM exams WHERE id=$exam_id")->fetch_assoc();
}

$bcolors = [
    'CSE'  =>['bg'=>'rgba(88,166,255,0.2)',  'color'=>'#58a6ff','label'=>'CSE'],
    'ECE'  =>['bg'=>'rgba(63,185,80,0.2)',   'color'=>'#3fb950','label'=>'ECE'],
    'ME'   =>['bg'=>'rgba(247,129,102,0.2)', 'color'=>'#f78166','label'=>'ME'],
    'CIVIL'=>['bg'=>'rgba(210,168,255,0.2)', 'color'=>'#d2a8ff','label'=>'CIVIL'],
    'IT'   =>['bg'=>'rgba(227,179,65,0.2)',  'color'=>'#e3b341','label'=>'IT'],
    'EE'   =>['bg'=>'rgba(248,81,73,0.2)',   'color'=>'#f85149','label'=>'EE'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>View Seating - ExamSeat Pro</title>
<link rel="stylesheet" href="style.css">
<style>
/* BENCH STYLES */
.bench-row { display:flex; gap:6px; margin-bottom:10px; align-items:center; }
.bench-label { font-size:0.65rem; color:var(--muted); writing-mode:vertical-rl; transform:rotate(180deg); min-width:16px; text-align:center; font-family:'JetBrains Mono',monospace; }
.bench-block {
    display:flex;
    border:1px solid var(--border);
    border-radius:8px;
    overflow:hidden;
    min-width:110px;
    flex:1;
    max-width:200px;
}
.bench-seat {
    flex:1;
    padding:6px 5px;
    text-align:center;
    font-size:0.65rem;
    border-right:1px dashed var(--border);
    position:relative;
    min-height:65px;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:2px;
}
.bench-seat:last-child { border-right:none; }
.bench-seat.occupied { background:rgba(88,166,255,0.05); }
.bench-seat.empty { opacity:0.3; }
.bench-seat .s-roll { font-family:'JetBrains Mono',monospace; font-size:0.62rem; font-weight:700; color:var(--accent2); }
.bench-seat .s-name { font-size:0.65rem; font-weight:600; color:var(--text); line-height:1.2; }
.bench-seat .s-branch { font-size:0.58rem; padding:1px 5px; border-radius:8px; font-weight:700; margin-top:2px; }
.bench-seat .pos-dot { width:6px; height:6px; border-radius:50%; background:var(--border); position:absolute; top:4px; left:50%; transform:translateX(-50%); }
.bench-seat.occupied .pos-dot { background:var(--accent2); }
.col-labels { display:flex; gap:6px; margin-bottom:4px; }
.col-label { flex:1; max-width:200px; text-align:center; font-size:0.62rem; color:var(--muted); font-family:'JetBrains Mono',monospace; }
</style>
</head>
<body>
<?php include 'header.php'; ?>

<nav class="navbar no-print">
    <div class="logo">🏛️ GGV Exam Seating</div>
    <div class="nav-links">
        <a href="index.php">Dashboard</a>
        <a href="students.php">Students</a>
        <a href="rooms.php">Rooms</a>
        <a href="generate.php">Generate</a>
        <a href="view_seating.php" class="active">View Seats</a>
        <a href="logout.php" style="color:var(--danger);">Logout</a>
    </div>
</nav>

<div class="container">

    <div class="page-title no-print">
        <h1>🗺️ <span>Seating</span> Chart</h1>
        <p>View and print room-wise seating arrangement</p>
    </div>

    <?php if($is_new): ?>
        <div class="alert alert-success">🎉 Seating arrangement generated successfully!</div>
    <?php endif; ?>

    <!-- Select Exam -->
    <div class="card no-print">
        <div class="card-title">📅 Select Exam</div>
        <form method="GET" style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap;">
            <div class="form-group" style="flex:1;min-width:220px;">
                <label>Choose Exam</label>
                <select name="exam_id" onchange="this.form.submit()">
                    <option value="">-- Select Exam --</option>
                    <?php $exams_list->data_seek(0); while($e=$exams_list->fetch_assoc()): ?>
                    <option value="<?=$e['id']?>" <?=$exam_id==$e['id']?'selected':''?>>
                        <?= htmlspecialchars($e['exam_name']) ?> — <?= htmlspecialchars($e['subject']) ?> (<?= date('d M Y', strtotime($e['exam_date'])) ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>
    </div>

<?php if($selected): ?>

    <!-- Actions -->
    <div class="no-print" style="display:flex;gap:0.75rem;margin-bottom:1.5rem;flex-wrap:wrap;">
        <button onclick="window.print()" class="btn btn-green">🖨️ Print</button>
        <a href="delete_exam.php?id=<?=$exam_id?>" class="btn btn-danger" onclick="return confirm('Delete this seating?')">🗑️ Delete</a>
        <a href="generate.php" class="btn btn-blue">🎲 Generate New</a>
    </div>

    <!-- Exam Header -->
    <div style="text-align:center;margin-bottom:2rem;">
        <h2 style="font-size:1.4rem;font-weight:800;"><?= htmlspecialchars($selected['exam_name']) ?></h2>
        <p style="color:var(--muted);margin-top:0.4rem;">
            📚 <?= htmlspecialchars($selected['subject']) ?> &nbsp;|&nbsp;
            📅 <?= date('d F Y', strtotime($selected['exam_date'])) ?>
        </p>
    </div>

    <!-- Legend -->
    <div class="legend no-print">
        <?php foreach($bcolors as $br=>$bc): ?>
        <div class="legend-item">
            <div class="legend-dot" style="background:<?=$bc['color']?>;opacity:0.7;"></div>
            <?= $br ?>
        </div>
        <?php endforeach; ?>
        <div class="legend-item"><div class="legend-dot" style="border:1px dashed var(--border);"></div> Empty</div>
    </div>

    <?php
    // Get rooms used in this exam
    $rooms_in_exam = $conn->query("
        SELECT DISTINCT r.* FROM rooms r
        JOIN seating s ON s.room_id = r.id
        WHERE s.exam_id = $exam_id
        ORDER BY r.room_no
    ");

    while($room = $rooms_in_exam->fetch_assoc()):
        $rid        = $room['id'];
        $bench_size = $room['bench_size'];
        $nrows      = $room['rows'];
        $ncols      = $room['cols'];

        // Build 3D grid [row][col][pos] = student
        $grid = [];
        $sq = $conn->query("
            SELECT s.seat_row, s.seat_col, s.bench_position, st.roll_no, st.name, st.branch
            FROM seating s JOIN students st ON st.id = s.student_id
            WHERE s.exam_id = $exam_id AND s.room_id = $rid
        ");
        while($seat = $sq->fetch_assoc()) {
            $grid[$seat['seat_row']][$seat['seat_col']][$seat['bench_position']] = $seat;
        }

        $cnt_q = $conn->query("SELECT COUNT(*) as c FROM seating WHERE exam_id=$exam_id AND room_id=$rid");
        $cnt   = $cnt_q->fetch_assoc()['c'];
    ?>

    <div style="margin-bottom:3rem;page-break-after:always;">

        <!-- Room Header -->
        <div class="room-header">
            <div>
                <h2>🏫 <?= htmlspecialchars($room['room_no']) ?></h2>
                <p><?=$nrows?> rows × <?=$ncols?> benches × <?=$bench_size?> per bench | <?=$cnt?>/<?=$room['capacity']?> seats used</p>
            </div>
            <div style="display:flex;gap:0.5rem;align-items:center;">
                <span class="badge badge-blue"><?=$bench_size?> per bench</span>
                <span class="badge badge-green"><?=$cnt?> students</span>
            </div>
        </div>

        <!-- Blackboard -->
        <div class="blackboard">⬛ BLACKBOARD — FRONT OF ROOM</div>

        <!-- Seating Visual -->
        <div class="seating-grid-wrap">
            <!-- Column labels -->
            <div class="col-labels" style="margin-left:24px;">
                <?php for($c=1;$c<=$ncols;$c++): ?>
                <div class="col-label">Bench <?=$c?></div>
                <?php endfor; ?>
            </div>

            <?php for($row=1;$row<=$nrows;$row++): ?>
            <div class="bench-row">
                <div class="bench-label">Row <?=$row?></div>
                <?php for($col=1;$col<=$ncols;$col++): ?>
                <div class="bench-block">
                    <?php for($pos=1;$pos<=$bench_size;$pos++):
                        $stu = $grid[$row][$col][$pos] ?? null;
                        $bc  = $bcolors[$stu['branch'] ?? ''] ?? ['bg'=>'transparent','color'=>'#58a6ff'];
                    ?>
                    <div class="bench-seat <?= $stu ? 'occupied' : 'empty' ?>">
                        <div class="pos-dot"></div>
                        <?php if($bench_size > 1): ?>
                        <div style="font-size:0.55rem;color:var(--muted);margin-bottom:2px;">
                            <?= ['L','M','R'][$pos-1] ?? $pos ?>
                        </div>
                        <?php endif; ?>
                        <?php if($stu): ?>
                            <div class="s-roll"><?= htmlspecialchars($stu['roll_no']) ?></div>
                            <div class="s-name"><?= htmlspecialchars(explode(' ',$stu['name'])[0]) ?></div>
                            <div class="s-branch" style="background:<?=$bc['bg']?>;color:<?=$bc['color']?>;"><?= $stu['branch'] ?></div>
                        <?php else: ?>
                            <div style="color:var(--border);font-size:0.8rem;">—</div>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
                <?php endfor; ?>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Student List Table -->
        <div style="margin-top:1.5rem;">
            <div class="card-title" style="font-size:0.85rem;">📋 Attendance Sheet — <?= htmlspecialchars($room['room_no']) ?></div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Seat</th><th>Bench Pos</th><th>Roll No.</th><th>Name</th><th>Branch</th><th>Signature</th></tr>
                    </thead>
                    <tbody>
                    <?php
                    $lq = $conn->query("
                        SELECT s.seat_row, s.seat_col, s.bench_position, st.roll_no, st.name, st.branch
                        FROM seating s JOIN students st ON st.id=s.student_id
                        WHERE s.exam_id=$exam_id AND s.room_id=$rid
                        ORDER BY s.seat_row, s.seat_col, s.bench_position
                    ");
                    $pos_labels = ['1'=>'Left','2'=>'Middle','3'=>'Right'];
                    while($lr=$lq->fetch_assoc()):
                        $bc2 = $bcolors[$lr['branch']] ?? ['bg'=>'','color'=>'#58a6ff'];
                    ?>
                    <tr>
                        <td><code>R<?=$lr['seat_row']?>-C<?=$lr['seat_col']?></code></td>
                        <td><?= $bench_size > 1 ? ($pos_labels[$lr['bench_position']]??$lr['bench_position']) : '—' ?></td>
                        <td style="font-family:'JetBrains Mono',monospace;font-weight:700;color:var(--accent2);"><?= htmlspecialchars($lr['roll_no']) ?></td>
                        <td><?= htmlspecialchars($lr['name']) ?></td>
                        <td><span class="badge" style="background:<?=$bc2['bg']?>;color:<?=$bc2['color']?>"><?=$lr['branch']?></span></td>
                        <td style="width:120px;border-bottom:1px solid var(--border);"></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php endwhile; ?>

<?php endif; ?>
</div>
</body>
</html>
