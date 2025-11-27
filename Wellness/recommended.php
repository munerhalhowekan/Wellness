<?php
// recommended.php (user-facing)
session_start();
require 'db-connection.php';
ini_set('display_errors',1);
error_reporting(E_ALL);

// تأكد أن المستخدم مسجل ولديه user_id
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    // لو تريد إعادة توجيه لصفحة تسجيل الدخول بدل الرسالة
    http_response_code(403);
    echo "Please login to use this page.";
    exit;
}
$USER_ID = intval($_SESSION['user_id']);

// Handle AJAX POST: add recommended to user's dashboard (user_workouts)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_dashboard') {
    $rec_id = intval($_POST['recommended_id'] ?? 0);
    if ($rec_id <= 0) {
        echo json_encode(['ok'=>false,'error'=>'Invalid recommended id']);
        exit;
    }
    // fetch recommended by id
    $stmt = $conn->prepare("SELECT name, items FROM recommended_master WHERE id = ? LIMIT 1");
    $stmt->bind_param('i',$rec_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    if (!$row) {
        echo json_encode(['ok'=>false,'error'=>'Not found']);
        exit;
    }
    $name = $row['name'];
    $items = $row['items']; // already JSON
    // insert into user_workouts
    $ins = $conn->prepare("INSERT INTO user_workouts (user_id, name, items, created_at) VALUES (?, ?, ?, NOW())");
    $ins->bind_param('iss', $USER_ID, $name, $items);
    $ok = $ins->execute();
    $ins->close();
    echo json_encode(['ok'=> (bool)$ok]);
    exit;
}

// Fetch user level
$stmt = $conn->prepare("SELECT fitness_level FROM users WHERE UserID = ? LIMIT 1");
$stmt->bind_param("i", $USER_ID);
$stmt->execute();
$levelRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

$userLevel = $levelRow['fitness_level'] ?? 'beginner';

// GET: load recommended_master filtered by level
$recommended = [];

$stmt = $conn->prepare("SELECT id, name, level, items, created_at 
                        FROM recommended_master 
                        WHERE level = ? 
                        ORDER BY created_at DESC");
$stmt->bind_param("s", $userLevel);
$stmt->execute();
$res = $stmt->get_result();

while ($r = $res->fetch_assoc()) {
    $r['items_arr'] = json_decode($r['items'], true) ?: [];
    $recommended[] = $r;
}
$stmt->close();

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Recommended Workouts — Wellness</title>
<style>
    
    
    .hamburger{
  font-size:24px;
  cursor:pointer;
}

.drawer{
  position:fixed;
  left:-260px;
  top:0;
  height:100vh;
  width:260px;
  background:var(--card);
  border-right:1px solid var(--border);
  padding:18px;
  z-index:80;
  transition:left .25s;
}

.drawer.open{ left:0 }

.drawer h4{
  margin:0 0 12px;
  color:var(--accent);
}

.drawer a{
  display:block;
  padding:10px;
  border-radius:8px;
  color:#fff;
  text-decoration:none;
  margin-bottom:6px;
}

.drawer a:hover{
  background:rgba(255,102,0,.25);
}

.overlay{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.5);
  display:none;
  z-index:70;
}

.overlay.show{ display:block }

    
  
    
    
:root{
  --bg:#1c1c1c;--layer:#222;--card:#2a2a2a;--text:#fff;--muted:#cfcfcf;
  --border:#3a3a3a;--accent:#ff6600;--accent-2:#ff8533;--shadow:0 0 18px rgba(255,102,0,.28);
}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font-family:"Poppins","Segoe UI",system-ui,Arial,sans-serif}
header{position:sticky;top:0;z-index:60;background:var(--layer);border-bottom:1px solid var(--border);
 display:flex;align-items:center;justify-content:space-between;padding:12px 18px;box-shadow:0 4px 12px rgba(0,0,0,.25);}
.page{font-weight:800}
.drawer{position:fixed;left:-260px;top:0;height:100vh;width:260px;background:var(--card);border-right:1px solid var(--border);padding:18px;z-index:80;transition:left .28s ease;}
.drawer.open{left:0}
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);display:none;z-index:70}
.overlay.show{display:block}
main{padding:20px;max-width:1100px;margin:auto}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px}
.card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:18px;box-shadow:var(--shadow);cursor:pointer;transition:.25s}
.card:hover{border-color:var(--accent);transform:translateY(-4px)}
.card h3{margin:0 0 8px;color:var(--accent)}
.card p{color:var(--muted);margin:0}
.modal{position:fixed;inset:0;background:rgba(0,0,0,.6);display:none;align-items:center;justify-content:center;z-index:90}
.modal.open{display:flex}
.sheet{position:relative;background:#222;color:var(--text);border:1px solid var(--border);border-radius:14px;padding:20px;width:min(600px,90vw);box-shadow:var(--shadow)}
.close{position:absolute;right:20px;top:16px;cursor:pointer;font-size:18px}
h2{margin:0 0 12px;color:var(--accent)}
ul{list-style:none;padding:0;margin:0 0 12px}
li{margin:8px 0;padding:8px;background:#2f2f2f;border-radius:8px;border:1px solid var(--border)}
.btn{background:var(--accent);border:none;color:#fff;padding:10px 18px;border-radius:10px;font-weight:800;cursor:pointer}
.btn:hover{background:var(--accent-2)}
.pill{display:inline-block;background:#333;border-radius:999px;padding:6px 8px;color:#fff;margin-right:6px}
</style>
</head>
<body>
<!-- Drawer -->
<div id="drawer" class="drawer">
    <h4>Navigation</h4>
    <a href="user-dashboard.php">🏠 Dashboard</a>
    <a href="exercises.php">💪 Exercises</a>
    <a href="dietplan.php">🥗 Diet Plan</a>
    <a href="leaderboard.php">🏆 Leaderboard</a>
</div>

<div id="overlay" class="overlay" onclick="toggleDrawer(false)"></div>

    
    
    
    
    
    
    
<header>
  <div class="hamburger" onclick="toggleDrawer()">☰</div>
  <div class="page">Recommended Workouts</div>
</header>


<main>
  <div class="grid">
    <?php if (empty($recommended)): ?>
      <div class="card"><h3 style="color:var(--accent)">No recommended yet</h3><p style="color:var(--muted)">No items found.</p></div>
    <?php endif; ?>

    <?php foreach ($recommended as $w): ?>
      <div class="card" onclick='openModal(<?= json_encode($w, JSON_UNESCAPED_UNICODE) ?>)'>
        <h3><?= htmlspecialchars($w['name']) ?></h3>
        <p>Level: <span class="pill"><?= htmlspecialchars($w['level']) ?></span></p>
        <p><?= count($w['items_arr']) ?> exercises</p>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<!-- modal -->
<div id="modal" class="modal" aria-hidden="true">
  <div class="sheet">
    <span class="close" id="mClose">✕</span>
    <h2 id="mName"></h2>
    <div style="margin-bottom:8px;color:var(--muted)">Level: <span id="mLevel" class="pill"></span></div>
    <ul id="mList"></ul>
    <div style="text-align:right">
      <button class="btn" id="mAddBtn">Add to dashboard</button>
    </div>
  </div>
</div>

<script>
const modal = document.getElementById('modal');
const mName = document.getElementById('mName');
const mLevel = document.getElementById('mLevel');
const mList = document.getElementById('mList');
const mAddBtn = document.getElementById('mAddBtn');
let currentRec = null;

function openModal(w){
  currentRec = w;
  mName.textContent = w.name;
  mLevel.textContent = w.level || '';
  mList.innerHTML = '';
  (w.items_arr || []).forEach(i=>{
    const li = document.createElement('li');
    li.textContent = `${i.name || i.ex || i.exercise} — ${i.sets} sets × ${i.reps} reps`;
    mList.appendChild(li);
  });
  modal.classList.add('open');
}
document.getElementById('mClose').onclick = ()=> modal.classList.remove('open');
modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('open'); });

mAddBtn.addEventListener('click', () => {
  if (!currentRec) return;
  // POST to this same page to copy recommended into user_workouts
  const form = new FormData();
  form.append('action','add_to_dashboard');
  form.append('recommended_id', currentRec.id);
  fetch(location.href, { method:'POST', body: form })
    .then(r => r.json())
    .then(j => {
      if (j.ok) {
        alert('Added to your dashboard');
        modal.classList.remove('open');
      } else {
        alert('Failed: ' + (j.error || 'Unknown'));
      }
    }).catch(e => { alert('Request failed'); });
});







</script>
<script>
function toggleDrawer(force){
  const d = document.getElementById('drawer');
  const o = document.getElementById('overlay');
  const open = (typeof force === 'boolean') ? force : !d.classList.contains('open');
  d.classList.toggle('open', open);
  o.classList.toggle('show', open);
}
</script>

</body>
</html>
