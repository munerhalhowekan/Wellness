<?php
session_start();
include 'db-connection.php';

// حماية
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: user-login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$health = $_SESSION['health_condition'] ?? null;

// اختيار جدول الدايت حسب الحالة الصحية
switch ($health) {
    case 'PCOS':               $dietTable = 'diet_pcos'; break;
    case 'Insulin Resistance': $dietTable = 'diet_insulin_resist'; break;
    case 'Gluten Intolerance': $dietTable = 'diet_glutenfree'; break;
    default:                   $dietTable = 'diet_pcos'; break;
}

// ترتيب الأيام
$daysOrder = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

// جلب بيانات الدايت اليومية
$sql = "SELECT day, breakfast, b_calories, lunch, l_calories, dinner, d_calories 
        FROM $dietTable";
$res = $conn->query($sql);

$prefill = [];
while ($r = $res->fetch_assoc()) {
    $prefill[$r['day']] = [
        [
            'meal' => 'Breakfast',
            'kcal' => (int)$r['b_calories'],
            'items'=> preg_split('/\s*\+\s*/', $r['breakfast'])
        ],
        [
            'meal' => 'Lunch',
            'kcal' => (int)$r['l_calories'],
            'items'=> preg_split('/\s*\+\s*/', $r['lunch'])
        ],
        [
            'meal' => 'Dinner',
            'kcal' => (int)$r['d_calories'],
            'items'=> preg_split('/\s*\+\s*/', $r['dinner'])
        ]
    ];
}

// جلب كل التقدم للمستخدم
$progSQL = $conn->prepare("
    SELECT day, breakfast_done, lunch_done, dinner_done
    FROM diet_progress
    WHERE userID = ?
");
$progSQL->bind_param("i", $userId);
$progSQL->execute();
$progRes = $progSQL->get_result();
$PROGRESS = [];

while ($p = $progRes->fetch_assoc()) {
    $PROGRESS[$p['day']] = [
        'breakfast_done' => (int)$p['breakfast_done'],
        'lunch_done'     => (int)$p['lunch_done'],
        'dinner_done'    => (int)$p['dinner_done']
    ];
}

// إضافة أيام ناقصة
foreach ($daysOrder as $d) {
    if (!isset($PROGRESS[$d])) {
        $insert = $conn->prepare("
            INSERT INTO diet_progress (userID, day, breakfast_done, lunch_done, dinner_done)
            VALUES (?, ?, 0, 0, 0)
        ");
        $insert->bind_param("is", $userId, $d);
        $insert->execute();
        $insert->close();

        $PROGRESS[$d] = [
            'breakfast_done'=>0,
            'lunch_done'=>0,
            'dinner_done'=>0
        ];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Diet Plan</title>
<style>
/* نفس ستايلك */
:root{
  --bg:#1c1c1c; --layer:#222; --card:#2a2a2a; --text:#fff;
  --muted:#cfcfcf; --border:#3a3a3a; --accent:#ff6600;
  --ok:#0fb76b; --shadow:0 0 18px rgba(255,102,0,.28);
}
body{margin:0;background:var(--bg);color:var(--text);font-family:Poppins,Arial}
header{
  background:var(--layer); padding:12px 18px; display:flex;
  justify-content:space-between; align-items:center; border-bottom:1px solid var(--border);
}
.hamburger{cursor:pointer;font-size:24px}
.logo-img{width:120px}
.drawer {
    position: fixed;
    left: -260px; /* بدل -250px */
    top: 0;
    width: 260px; /* لازم تكون نفس قيمة السحب */
    height: 100vh;
    background: var(--card);
    padding: 14px;
    border-right: 1px solid var(--border);
    transition: left .25s ease;
    box-sizing: border-box;
}

.drawer.open{left:0}
.drawer a{display:block;color:#fff;padding:10px;border-radius:8px;text-decoration:none}
.drawer a:hover{background:#333}

.daybar{
  margin:20px auto;display:flex;justify-content:center;gap:16px;
  background:var(--card);padding:10px;border-radius:14px;
}
.daybtn{padding:8px 14px;background:#333;color:#fff;border-radius:8px;border:none}
.daytitle{font-weight:900;font-size:20px}

.meal-row{
  display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin:20px;
}
.meal{
  background:var(--card);padding:16px;border-radius:14px;border:1px solid var(--border);
}
.meal h3{margin:0 0 6px;color:var(--accent)}
.items li{margin:4px 0}
.check{padding:8px 14px;border:none;background:var(--accent);color:#fff;border-radius:8px;font-weight:800}
.check.done{background:var(--ok)}
.check:disabled{opacity:.4}
</style>
</head>

<body>
<!-- Drawer + overlay -->
<aside id="drawer" class="drawer" aria-hidden="true">
  <h4>Navigation</h4>

  <a href="user-dashboard.php">🏠 Dashboard</a>
  <a href="exercises.php">💪 Exercises</a>
  <a href="dietplan.php">🥗 Diet Plan</a>
  <a href="leaderboard.php">🏆 Leaderboard</a>
</aside>

<div id="overlay" class="overlay" onclick="toggleDrawer(false)"></div>

<!-- Header -->
<header>
  <div class="hamburger" onclick="toggleDrawer()">☰</div>
  <div class="page">Diet Plan</div>
  <a href="user-dashboard.php" class="logo-link">
    <img src="wellness logo.png" alt="Wellness Logo" class="logo-img">
  </a>
</header>




<div class="daybar">
  <button class="daybtn" onclick="prevDay()">&larr;</button>
  <div class="daytitle" id="dayTitle">Sunday</div>
  <button class="daybtn" onclick="nextDay()">&rarr;</button>
</div>

<div class="meal-row" id="mealRow"></div>

<script>
function toggleDrawer(){
  document.getElementById("drawer").classList.toggle("open");
}

const DAYS = <?php echo json_encode($daysOrder); ?>;
const PREFILL = <?php echo json_encode($prefill); ?>;
let PROGRESS = <?php echo json_encode($PROGRESS); ?>;

let index = new Date().getDay(); // اليوم الحالي

function render(){
  const day = DAYS[index];
  document.getElementById("dayTitle").textContent = day;

  const meals = PREFILL[day];
  const prog = PROGRESS[day];

  const today = new Date().getDay();
  const locked = (index !== today); // الأيام غير اليوم الحالي → مقفلة

  const box = document.getElementById("mealRow");
  box.innerHTML = "";

  meals.forEach(m => {
    let done=false;
    if (m.meal==="Breakfast") done = prog.breakfast_done==1;
    if (m.meal==="Lunch")     done = prog.lunch_done==1;
    if (m.meal==="Dinner")    done = prog.dinner_done==1;

    const card = document.createElement("div");
    card.className = "meal";
    card.innerHTML = `
      <h3>${m.meal}</h3>
      <p class='kcal'>${m.kcal} kcal</p>
      <ul class='items'>${m.items.map(i=>`<li>${i}</li>`).join('')}</ul>
      <button class="check ${done?'done':''}" ${locked?'disabled':''}>
        ${done?'Done ✓':'✓'}
      </button>
    `;

    const btn = card.querySelector("button");
    btn.onclick = ()=>updateMeal(m.meal, day);

    box.appendChild(card);
  });
}

function prevDay(){ if(index>0){index--; render();} }
function nextDay(){ if(index<6){index++; render();} }

function updateMeal(meal, day){
  fetch("update_meal.php", {
    method: "POST",
    headers: {"Content-Type":"application/x-www-form-urlencoded"},
    body: "meal="+meal.toLowerCase()+"&day="+day
  })
  .then(r=>r.json())
  .then(data=>{
    PROGRESS[day].breakfast_done = data.breakfast_done;
    PROGRESS[day].lunch_done     = data.lunch_done;
    PROGRESS[day].dinner_done    = data.dinner_done;
    render();
  });
}

render();
</script>

</body>
</html>
