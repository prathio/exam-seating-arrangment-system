<?php
require_once 'config.php';
requireAdmin();

$msg = '';

// ---- ADD ROOM ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_room'])) {
    $room_no    = trim($_POST['room_no']);
    $rows       = intval($_POST['rows']);
    $cols       = intval($_POST['cols']);
    $bench_size = intval($_POST['bench_size']);
    $capacity   = $rows * $cols * $bench_size;

    if ($room_no && $rows > 0 && $cols > 0 && $bench_size > 0) {
        // Using backticks around reserved words rows and cols
        $stmt = $conn->prepare("INSERT INTO rooms (room_no, `rows`, cols, bench_size, capacity) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siiii", $room_no, $rows, $cols, $bench_size, $capacity);
        if ($stmt->execute()) {
            $msg = ['type'=>'success', 'text'=>"✅ Room '$room_no' added! Capacity: $capacity students ($rows rows × $cols benches × $bench_size per bench)"];
        } else {
            $msg = ['type'=>'error', 'text'=>"❌ Room number already exists!"];
        }
    } else {
        $msg = ['type'=>'error', 'text'=>"❌ Please fill all fields!"];
    }
}

// ---- DELETE ----
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM rooms WHERE id=$id");
    $msg = ['type'=>'success', 'text'=>"🗑️ Room deleted."];
}

$rooms = $conn->query("SELECT * FROM rooms ORDER BY room_no");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rooms - ExamSeat Pro</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

<nav class="navbar">
    <div class="logo">🏛️ GGV Exam Seating</div>
    <div class="nav-links">
        <a href="index.php">Dashboard</a>
        <a href="students.php">Students</a>
        <a href="rooms.php" class="active">Rooms</a>
        <a href="generate.php">Generate</a>
        <a href="view_seating.php">View Seats</a>
        <a href="logout.php" style="color:var(--danger);">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="page-title">
        <h1>🏫 <span>Exam Rooms</span></h1>
        <p>Manage rooms — set bench size (1, 2, or 3 students per bench)</p>
    </div>

    <?php if($msg): ?>
        <div class="alert alert-<?= $msg['type']==='success'?'success':'error' ?>"><?= $msg['text'] ?></div>
    <?php endif; ?>

    <!-- Bench Explanation -->
    <div class="card" style="border-color:var(--accent2);margin-bottom:1.5rem;">
        <div class="card-title">ℹ️ How Bench Seating Works</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;">
            <div style="text-align:center;padding:1rem;background:var(--surface2);border-radius:8px;">
                <div style="font-size:1.5rem;margin-bottom:0.5rem;">🪑</div>
                <div style="font-weight:700;margin-bottom:0.3rem;">1 per bench</div>
                <div style="font-size:0.78rem;color:var(--muted);">Individual desk<br>5 rows × 6 cols = 30 students</div>
            </div>
            <div style="text-align:center;padding:1rem;background:var(--surface2);border-radius:8px;">
                <div style="font-size:1.5rem;margin-bottom:0.5rem;">🪑🪑</div>
                <div style="font-weight:700;margin-bottom:0.3rem;">2 per bench</div>
                <div style="font-size:0.78rem;color:var(--muted);">Double bench<br>5 rows × 6 cols = 60 students</div>
            </div>
            <div style="text-align:center;padding:1rem;background:var(--surface2);border-radius:8px;">
                <div style="font-size:1.5rem;margin-bottom:0.5rem;">🪑🪑🪑</div>
                <div style="font-weight:700;margin-bottom:0.3rem;">3 per bench</div>
                <div style="font-size:0.78rem;color:var(--muted);">Triple bench<br>5 rows × 6 cols = 90 students</div>
            </div>
        </div>
    </div>

    <!-- Add Room Form -->
    <div class="card">
        <div class="card-title">➕ Add New Room</div>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Room Number *</label>
                    <input type="text" name="room_no" placeholder="e.g. Room 101, Hall A" required>
                </div>
                <div class="form-group">
                    <label>Number of Rows *</label>
                    <input type="number" name="rows" id="rows" placeholder="e.g. 5" min="1" max="20" required>
                </div>
                <div class="form-group">
                    <label>Number of Bench Columns *</label>
                    <input type="number" name="cols" id="cols" placeholder="e.g. 6" min="1" max="20" required>
                </div>
                <div class="form-group">
                    <label>Students per Bench *</label>
                    <select name="bench_size" id="bench_size" required>
                        <option value="1">1 — Single desk</option>
                        <option value="2">2 — Double bench</option>
                        <option value="3">3 — Triple bench</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Total Capacity (auto)</label>
                    <input type="text" id="cap_show" readonly style="opacity:0.6;" placeholder="Auto calculated...">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" name="add_room" class="btn btn-green">➕ Add Room</button>
                <a href="rooms.php" class="btn btn-ghost">↩ Cancel</a>
            </div>
        </form>
    </div>

    <!-- Rooms List -->
    <div class="card">
        <div class="card-title">📋 All Rooms (<?= $rooms->num_rows ?>)</div>
        <?php if($rooms->num_rows > 0): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Room</th><th>Rows</th><th>Bench Cols</th><th>Per Bench</th><th>Capacity</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php $i=1; while($r=$rooms->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><strong><?= htmlspecialchars($r['room_no']) ?></strong></td>
                    <td><?= $r['rows'] ?></td>
                    <td><?= $r['cols'] ?></td>
                    <td>
                        <span class="badge <?= $r['bench_size']==1?'badge-blue':($r['bench_size']==2?'badge-green':'badge-orange') ?>">
                            <?= $r['bench_size'] ?> <?= $r['bench_size']==1?'Single':($r['bench_size']==2?'Double':'Triple') ?>
                        </span>
                    </td>
                    <td><span class="badge badge-green">💺 <?= $r['capacity'] ?></span></td>
                    <td>
                        <a href="rooms.php?delete=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this room?')">🗑 Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-info">ℹ️ No rooms yet.</div>
        <?php endif; ?>
    </div>
</div>

<script>
const r = document.getElementById('rows');
const c = document.getElementById('cols');
const b = document.getElementById('bench_size');
const cap = document.getElementById('cap_show');
function updateCap() {
    const rv = parseInt(r.value)||0, cv = parseInt(c.value)||0, bv = parseInt(b.value)||1;
    if(rv&&cv) cap.value = (rv*cv*bv) + ' students total';
    else cap.value = '';
}
r.addEventListener('input', updateCap);
c.addEventListener('input', updateCap);
b.addEventListener('change', updateCap);
</script>
</body>
</html>
