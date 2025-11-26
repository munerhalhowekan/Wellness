<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

session_regenerate_id(true);
include 'db-connection.php';

/****************************************
  SESSION FIX
*****************************************/

// لو login حاط user_id بس
if (isset($_SESSION['user_id']) && !isset($_SESSION['UserID'])) {
    $_SESSION['UserID'] = $_SESSION['user_id'];
}

// لو حاط name بس
if (isset($_SESSION['name']) && !isset($_SESSION['firstName'])) {
    $_SESSION['firstName'] = $_SESSION['name'];
}

// ما فيه تسجيل دخول
if (!isset($_SESSION['UserID'])) {
    die("<p style='color:#ff4a6a;font-size:18px;margin:40px'>❌ Please login first.</p>");
}

$userId = (int)$_SESSION['UserID'];



/****************************************
  FETCH USER INFO + STATS
*****************************************/
$q = $conn->prepare("
    SELECT firstName, lastName, fitness_level, health_condition,
           COALESCE(points,0) AS points,
           COALESCE(workouts_completed,0) AS workouts_completed,
           COALESCE(calories_remaining,0) AS calories_remaining,
           COALESCE(diet_done,0) AS diet_done
    FROM users
    WHERE UserID = ?
");

if (!$q) {
    die("SQL ERROR (user-dashboard) → " . $conn->error);
}

$q->bind_param("i", $userId);
$q->execute();
$user = $q->get_result()->fetch_assoc();
$q->close();

if (!$user) {
    die("<p style='color:#ff4a6a;font-size:18px;margin:40px'>❌ User not found.</p>");
}

$_SESSION['firstName']        = $user['firstName'];
$_SESSION['lastName']         = $user['lastName'];
$_SESSION['fitness_level']    = $user['fitness_level'];
$_SESSION['health_condition'] = $user['health_condition'];

$userName        = $user['firstName'] . " " . $user['lastName'];
$fitnessLevel    = $user['fitness_level'];
$healthCondition = $user['health_condition'];

$points            = (int)$user['points'];
$workoutsCompleted = (int)$user['workouts_completed'];
$caloriesRemaining = (int)$user['calories_remaining'];
$dietDoneFlag      = (int)$user['diet_done'];   // 0 أو 1

/****************************************
  MAP WORKOUT TABLE BY LEVEL (عرض فقط)
*****************************************/
switch ($fitnessLevel) {
    case 'intermediate':
        $workoutTable = 'workout_intermediate';
        $pk           = 'IntermediateID';
        break;
    case 'advanced':
        $workoutTable = 'workout_advanced';
        $pk           = 'AdvancedID';
        break;
    default:
        $workoutTable = 'workout_beginner';
        $pk           = 'BeginnerID';
        break;
}

/****************************************
  MAP DIET TABLE BY HEALTH CONDITION
*****************************************/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$health = $_SESSION['health_condition'] ?? null;

switch ($health) {
    case 'PCOS':
        $dietTable = "diet_pcos";
        break;
    case 'Insulin Resistance':
        $dietTable = "diet_insulin_resist";
        break;
    case 'Gluten Intolerance':
        $dietTable = "diet_glutenfree";
        break;
    default:
        $dietTable = "diet_pcos"; // DEFAULT ONLY IF HEALTH EMPTY
        break;
}
/****************************************
  CREATE/INIT diet_progress ROW FOR TODAY
*****************************************/

// اسم اليوم الحالي
$todayName = date("l");

// هل يوجد صف سابق لهذا اليوم؟
$chk = $conn->prepare("
    SELECT id, breakfast_done, lunch_done, dinner_done, remaining_kcal
    FROM diet_progress
    WHERE userID = ? AND day = ?
    LIMIT 1
");
$chk->bind_param("is", $userId, $todayName);
$chk->execute();
$exist = $chk->get_result()->fetch_assoc();
$chk->close();

/* 
  لو ما فيه صف → ننشئ واحد جديد 
  remaining_kcal = total_calories_per_day من جدول الدايت
*/
if (!$exist) {
    // نجيب السعرات اليومية
    $dailyCalories = 0;
    $get = $conn->prepare("SELECT total_calories_per_day FROM $dietTable WHERE day = ?");
    $get->bind_param("s", $todayName);
    $get->execute();
    $res = $get->get_result()->fetch_assoc();
    $get->close();

    if ($res) {
        $dailyCalories = (int)$res['total_calories_per_day'];
    }

    // الآن ننشئ صف جديد
    $ins = $conn->prepare("
        INSERT INTO diet_progress (userID, day, remaining_kcal)
        VALUES (?, ?, ?)
    ");
    $ins->bind_param("isi", $userId, $todayName, $dailyCalories);
    $ins->execute();
    $ins->close();

    // نخزّن القيم في ذاكرة الصفحة
    $breakfastDone = 0;
    $lunchDone     = 0;
    $dinnerDone    = 0;
    $remainingKcal = $dailyCalories;

} else {
    // يوجد صف: نرجّع قيمه
    $breakfastDone = (int)$exist['breakfast_done'];
    $lunchDone     = (int)$exist['lunch_done'];
    $dinnerDone    = (int)$exist['dinner_done'];
    $remainingKcal = (int)$exist['remaining_kcal'];
}


/****************************************
  FETCH TODAY MEALS + CALORIES
*****************************************/
$todayName = date("l");

$ds = $conn->prepare("
    SELECT breakfast, lunch, dinner, total_calories_per_day 
    FROM $dietTable 
    WHERE day = ?
");
$ds->bind_param("s", $todayName);
$ds->execute();
$diet = $ds->get_result()->fetch_assoc();
$ds->close();

$breakfast     = $diet['breakfast'] ?? "No breakfast";
$lunch         = $diet['lunch'] ?? "No lunch";
$dinner        = $diet['dinner'] ?? "No dinner";
$dailyCalories = isset($diet['total_calories_per_day']) ? (int)$diet['total_calories_per_day'] : 0;

/****************************************
  INIT calories_remaining لأول مرة
  (بس للمستخدم اللي لسه ما أنهى الدايت)
*****************************************/
if ($caloriesRemaining === 0 && $dailyCalories > 0 && $dietDoneFlag === 0) {
    $caloriesRemaining = $dailyCalories;
    $st = $conn->prepare("UPDATE users SET calories_remaining = ? WHERE UserID = ?");
    $st->bind_param("ii", $caloriesRemaining, $userId);
    $st->execute();
    $st->close();
}

/****************************************
  HANDLE SIMPLE STATS ACTIONS
  - workout_done → يزيد points +10 & workouts_completed +1
  - diet_done    → ينقص calories_remaining بقيمة dailyCalories ويخزن diet_done=1
*****************************************/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['statsAction'])) {
    $action = $_POST['statsAction'];

    if ($action === 'workout_done') {
        $workoutsCompleted++;
        $points += 10;

        $st = $conn->prepare("UPDATE users SET workouts_completed = ?, points = ? WHERE UserID = ?");
        $st->bind_param("iii", $workoutsCompleted, $points, $userId);
        $st->execute();
        $st->close();

    } elseif ($action === 'diet_done') {
        // ما نسوي شيء لو أصلاً مسوي Done قبل
        if ($dietDoneFlag === 0 && $dailyCalories > 0) {
            // ننقص سعرات اليوم
            $caloriesRemaining = max(0, $caloriesRemaining - $dailyCalories);

            $st = $conn->prepare("UPDATE users SET calories_remaining = ?, diet_done = 1 WHERE UserID = ?");
            $st->bind_param("ii", $caloriesRemaining, $userId);
            $st->execute();
            $st->close();
        }
    }

    // منع إعادة الإرسال عند الريفريش
    header("Location: user-dashboard.php");
    exit;
}
/****************************************
  FETCH USER CUSTOM WORKOUT (آخر ووركاوت مضاف)
*****************************************/
$wstmt = $conn->prepare("
    SELECT workout_id AS id, name, items
    FROM user_workouts
    ORDER BY workout_id DESC
    LIMIT 1
");

$wstmt->execute();
$wrow = $wstmt->get_result()->fetch_assoc();
$wstmt->close();


$userWorkout = null;

if ($wrow) {
    $userWorkout = [
        "id"    => $wrow["id"],
        "name"  => $wrow["name"],
        "items" => json_decode($wrow["items"], true)
    ];
}

/****************************************
  FETCH TODAY EXERCISE (من جدول user_workouts)
*****************************************/

if ($userWorkout && isset($userWorkout['items'][0])) {

    // نأخذ أول تمرين فقط كـ "Today's Exercise"
    $todayEx = $userWorkout['items'][0];

    $exName  = $todayEx['name'] ?? "Unnamed Exercise";
    $exSets  = $todayEx['sets'] ?? 0;
    $exReps  = $todayEx['reps'] ?? 0;
    $exGroup = ""; // لأن custom ما فيه جروب
    $exId    = $userWorkout['id'];

} else {
    // المستخدم ما أضاف تمارين
    $exName  = "No exercise added yet";
    $exSets  = 0;
    $exReps  = 0;
    $exGroup = "";
    $exId    = 0;
}




/****************************************
  BUTTON STATES (أخضر / برتقالي)
*****************************************/
$workoutDone   = $workoutsCompleted > 0;        // يكفي إنه خلص ووركاوت مرة
$dietDone      = ($dietDoneFlag === 1);         // مباشرة من الداتابيس

$workoutBtnClass = $workoutDone ? 'check done' : 'check';
$workoutBtnLabel = $workoutDone ? 'Done ✓'     : 'Done';

$dietBtnClass    = $dietDone ? 'check done' : 'check';
$dietBtnLabel    = $dietDone ? 'Done ✓'     : 'Done';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Wellness — Dashboard</title>

  <style>
    :root{
      --bg:#1c1c1c;
      --layer:#222;
      --card:#2a2a2a;
      --text:#ffffff;
      --muted:#cfcfcf;
      --border:#3a3a3a;
      --accent:#ff6600;
      --accent-2:#ff8533;
      --ok:#0fb76b;
      --shadow:0 0 18px rgba(255,102,0,.28);
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;background:var(--bg);color:var(--text);
      font-family:"Poppins","Segoe UI",system-ui,Arial,sans-serif;
    }

    /* ===== Sticky Header ===== */
    header {
      position: sticky;
      top: 0;
      z-index: 60;
      background: var(--layer);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 18px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }
    .hamburger {
      cursor: pointer;
      font-size: 24px;
      user-select: none;
      order: 1;
    }
    .page {
      font-weight: 800;
      letter-spacing: 0.3px;
      order: 2;
    }
    .logo-link {
      order: 3;
      display: flex;
      align-items: center;
    }
    .logo-img {
      width: 120px;
      height: auto;
      object-fit: contain;
      display: block;
    }

    /* ===== Drawer ===== */
    .drawer{
      position:fixed; left:-260px; top:0; height:100vh; width:260px;
      background:var(--card); border-right:1px solid var(--border);
      box-shadow: 16px 0 30px rgba(0,0,0,.45);
      padding:18px 12px; z-index:80; transition:left .28s ease;
    }
    .drawer.open{ left:0; }
    .drawer h4{margin:4px 8px 12px;color:var(--accent)}
    .drawer a{
      display:flex;align-items:center;gap:10px;
      color:var(--text); text-decoration:none;
      padding:12px 10px; border-radius:10px; margin:2px 6px;
    }
    .drawer a:hover{background:rgba(255,102,0,.14)}
    .overlay{
      position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:70; display:none;
    }
    .overlay.show{display:block}

    /* ===== Dashboard Layout ===== */
    .dash{
      width:100%; padding:20px 24px;
    }
    .panel{
      background:var(--layer); border:1px solid var(--border);
      border-radius:16px; overflow:hidden; box-shadow:0 10px 24px rgba(0,0,0,.3);
    }
    .panel-head{
      background:var(--accent); color:#fff; font-weight:900; padding:14px 18px; font-size:20px;
    }
    .panel-body{padding:18px}

    .stats{
      display:grid;
      grid-template-columns: repeat(3, 0.8fr);
      gap:14px;
      margin-top: 40px;
      margin-bottom:16px;
    }
    .stat{
      background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px; text-align:center;
      box-shadow:var(--shadow); transition:.25s;
    }
    .stat:hover{transform:translateY(-3px); border-color:var(--accent)}
    .stat .ico{font-size:28px;margin-bottom:6px}
    .stat h4{margin:6px 0 2px 0;font-weight:800}
    .stat p{margin:0;color:var(--muted)}

    .two{
      display:grid; grid-template-columns:1fr 1fr; gap:14px;
    }
    .two .box { margin-top: 70px; }

    .box{
      background:var(--card); border:1px solid var(--border);
      border-radius:12px; padding:16px; box-shadow:var(--shadow);
    }
    .box h3{margin:0 0 10px;color:var(--accent)}
    .item{
      background:#333;border:1px solid var(--border); border-radius:10px; padding:12px 14px;
      display:flex; align-items:center; justify-content:space-between; gap:10px;
      transition:.25s; cursor:pointer;
    }
    .item:hover{border-color:var(--accent)}
    .meta{display:flex;flex-direction:column;gap:4px}
    .title{font-weight:800}
    .sub{color:var(--muted);font-size:13px}
    .check{
      background:var(--accent); color:#fff; border:none; border-radius:999px; padding:8px 18px; font-weight:800; cursor:pointer;
      min-width:90px;
    }
    .check.done{
      background:var(--ok);
    }

    .actions{display:flex; gap:10px; flex-wrap:wrap; margin-top:14px}
    .btn{
      background:var(--accent); color:#fff; border:none; border-radius:10px; padding:10px 14px; font-weight:800; cursor:pointer;
    }
    .btn:hover{background:var(--accent-2)}

    /* ===== Modals ===== */
    .modal{ position:fixed; inset:0; background:rgba(0,0,0,.6); display:none; align-items:center; justify-content:center; padding:16px; z-index:90; }
    .modal.open{ display:flex; }
    .sheet{
      width:min(560px, 96vw); background:#222; color:var(--text); border:1px solid var(--border);
      border-radius:14px; padding:18px; position:relative; box-shadow:var(--shadow);
    }
    .sheet h4{margin:0 0 10px; color:var(--accent)}
    .close{position:absolute; right:12px; top:10px; cursor:pointer; font-size:18px}
    .edit-link{position:absolute; right:46px; top:12px; color:var(--accent); cursor:pointer; text-decoration:underline}

    .field{display:flex; gap:8px; margin:8px 0}
    .field label{width:110px; color:var(--muted)}
    .field span{font-weight:800}
    .field input{
      flex:1; background:#2f2f2f; border:1px solid var(--border); color:#fff; border-radius:8px; padding:8px 10px;
    }

    .sublist{background:#2f2f2f; border:1px solid var(--border); border-radius:10px; padding:10px; margin:8px 0}
    .subrow{display:flex; gap:8px; align-items:center; margin:6px 0}
    .subrow input[type="text"], .subrow input[type="number"]{
      background:#3a3a3a; border:1px solid var(--border); color:#fff; border-radius:8px; padding:8px 10px;
    }
    .del{
      background:#b81031;border:none;color:#fff;padding:6px 10px;border-radius:8px;cursor:pointer;font-weight:800;
    }

    .sheet-actions{display:flex; justify-content:flex-end; gap:10px; margin-top:12px}
    .ghost{background:#555}
    .ok{background:var(--accent)}
    .ok,.ghost{border:none;color:#fff;border-radius:999px;padding:10px 20px;font-weight:800;cursor:pointer}

    @media (max-width: 980px){
      .stats{grid-template-columns:1fr}
      .two{grid-template-columns:1fr}
    }
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
  <div class="page">Dashboard — <?php echo htmlspecialchars($userName); ?></div>
  <a href="user-dashboard.php" class="logo-link">
    <img src="wellness logo.png" alt="Wellness Logo" class="logo-img">
  </a>
</header>

<!-- Dashboard -->
<main class="dash">
  <section class="panel">
    <div class="panel-head">Wellness Dashboard</div>
    <div class="panel-body">

      <!-- Row 1 stats -->
      <div class="stats">
        <div class="stat">
          <div class="ico">🔥</div>
          <h4>Calories (remaining)</h4>
          <p><span id="caloriesVal"><?php echo (int)$caloriesRemaining; ?></span> kcal</p>
        </div>
        <div class="stat">
          <div class="ico">⭐</div>
          <h4>Points</h4>
          <p><span id="pointsVal"><?php echo (int)$points; ?></span> pts</p>
        </div>
        <div class="stat">
          <div class="ico">🏋️‍♀️</div>
          <h4>Workouts</h4>
          <p><span id="workoutsVal"><?php echo (int)$workoutsCompleted; ?></span> completed</p>
        </div>
      </div>

      <!-- Row 2 -->
      <div class="two">
        <!-- Exercise -->
        <div class="box">
          <h3>Today's Exercise</h3>
          <div class="item" onclick="openExercise()">
            <div class="meta">
              <div class="title" id="todayExName">
                <?php echo htmlspecialchars($exName) . " — " . htmlspecialchars($exGroup); ?>
              </div>
              <div class="sub" id="todayExBrief">
                <?php echo (int)$exSets . " sets × " . htmlspecialchars($exReps) . " reps — " . htmlspecialchars($exGroup); ?>
              </div>
            </div>
            <button 
              class="<?php echo $workoutBtnClass; ?>" 
              id="exCheck" 
              onclick="markWorkoutDone(event)">
              <?php echo $workoutBtnLabel; ?>
            </button>
          </div>
          <div class="actions">
            <button class="btn" onclick="openExercise()">View Details</button>
          </div>
        </div>

        <!-- Diet -->
        <div class="box">
          <h3>Today's Diet Plan</h3>
          <div class="item" onclick="openDiet()">
            <div class="meta">
              <div class="title" id="dietTitle"><?php echo htmlspecialchars($health); ?></div>
              <div class="sub">Breakfast + Lunch + Dinner — tap for details</div>
            </div>
            <button 
              class="<?php echo $dietBtnClass; ?>" 
              id="dietCheck" 
              onclick="markDietDone(event)">
              <?php echo $dietBtnLabel; ?>
            </button>
          </div>
          <div class="actions">
            <button class="btn" onclick="openDiet()">View Meals</button>
          </div>
        </div>
      </div>

    </div>
  </section>
</main>

<!-- Exercise Modal -->
<div id="exerciseModal" class="modal" aria-hidden="true">
  <div class="sheet">
    <span class="close" onclick="closeModal('exerciseModal')">✕</span>
    <span class="edit-link" id="exEditLink" onclick="toggleExerciseEdit()">Edit</span>
    <h4>Exercise Details</h4>

    <!-- view -->
    <div id="exView">
      <div class="field"><label>Name:</label><span id="exNameText"><?php echo htmlspecialchars($exName); ?></span></div>
      <div class="sublist">
        <div class="subrow"><span>Sets:</span>&nbsp;<span id="exSetsText"><?php echo (int)$exSets; ?></span></div>
        <div class="subrow"><span>Reps:</span>&nbsp;<span id="exRepsText"><?php echo htmlspecialchars($exReps); ?></span></div>
      </div>
    </div>

    <!-- edit -->
    <div id="exEdit" style="display:none">
      <div class="field"><label>Name:</label><input id="exNameInp" type="text" value="<?php echo htmlspecialchars($exName); ?>"/></div>
      <div class="sublist">
        <div class="subrow">
          <label>Sets</label><input id="exSetsInp" type="number" min="1" value="<?php echo (int)$exSets; ?>" style="width:110px"/>
          <label>Reps</label><input id="exRepsInp" type="number" min="1" value="<?php echo htmlspecialchars($exReps); ?>" style="width:110px"/>
        </div>
        <div class="subrow">
          <button class="del" onclick="deleteExercise()">Delete exercise</button>
        </div>
      </div>
    </div>

    <!-- hidden form to talk to backend (exercise) -->
    <form id="exForm" method="post" style="display:none;">
      <input type="hidden" name="ex_id"   id="exIdField"   value="<?php echo (int)$exId; ?>">
      <input type="hidden" name="ex_name" id="exNameField">
      <input type="hidden" name="ex_sets" id="exSetsField">
      <input type="hidden" name="ex_reps" id="exRepsField">
      <input type="hidden" name="exAction" id="exActionField">
    </form>

    <div class="sheet-actions">
      <button class="ghost" onclick="closeModal('exerciseModal')">Cancel</button>
      <button class="ok" onclick="saveExercise()">Done</button>
    </div>
  </div>
</div>

<!-- Diet Modal -->
<div id="dietModal" class="modal" aria-hidden="true">
  <div class="sheet">
    <span class="close" onclick="closeModal('dietModal')">✕</span>
    <h4>Today's Meals</h4>

    <div id="dietView">
      <div class="sublist">
        <div class="subrow"><label>Breakfast:</label>&nbsp;<span id="bText"><?php echo htmlspecialchars($breakfast); ?></span></div>
        <div class="subrow"><label>Lunch:</label>&nbsp;<span id="lText"><?php echo htmlspecialchars($lunch); ?></span></div>
        <div class="subrow"><label>Dinner:</label>&nbsp;<span id="dText"><?php echo htmlspecialchars($dinner); ?></span></div>
      </div>
    </div>

    <div class="sheet-actions">
      <button class="ghost" onclick="closeModal('dietModal')">Close</button>
      <!-- زر التشيك الحقيقي للدايت -->
      <button class="ok" onclick="markDietDone(event)">Done ✓</button>
    </div>

  </div>
</div>

<!-- hidden form for stats (points / workouts / calories) -->
<form id="statsForm" method="post" style="display:none;">
  <input type="hidden" name="statsAction" id="statsActionField">
</form>

<script>
  /* Drawer */
  function toggleDrawer(force){
    const drawer = document.getElementById('drawer');
    const overlay = document.getElementById('overlay');
    const willOpen = (typeof force === 'boolean') ? force : !drawer.classList.contains('open');
    drawer.classList.toggle('open', willOpen);
    overlay.classList.toggle('show', willOpen);
  }

  /* Modals */
  function openExercise(){ document.getElementById('exerciseModal').classList.add('open'); }
  function openDiet(){ document.getElementById('dietModal').classList.add('open'); }
  function closeModal(id){ document.getElementById(id).classList.remove('open'); }

  /* Workout button → يزيد points + workouts_completed */
  function markWorkoutDone(e){
    e.stopPropagation();
    document.getElementById('statsActionField').value = 'workout_done';
    document.getElementById('statsForm').submit();
  }

  /* Diet button → ينقص calories_remaining بقيمة dailyCalories ويخزن diet_done = 1 */
 function markDietDone(e){
    e.stopPropagation();

    fetch("update_diet_done.php", {
        method: "POST"
    })
    .then(r => r.json())
    .then(data => {
        // تحديث واجهة الداشبورد مباشرة
        document.getElementById("caloriesVal").textContent = 0;

        const btn = document.getElementById("dietCheck");
        btn.classList.add("done");
        btn.textContent = "Done ✓";
    })
    .catch(err => console.error(err));
}


  /* Exercise edit mode */
  let exEditMode = false;
  function toggleExerciseEdit(){
    exEditMode = !exEditMode;
    document.getElementById('exView').style.display = exEditMode ? 'none' : 'block';
    document.getElementById('exEdit').style.display = exEditMode ? 'block' : 'none';
    document.getElementById('exEditLink').textContent = exEditMode ? 'View' : 'Edit';
  }

  function saveExercise(){
    if(exEditMode){
      const name = document.getElementById('exNameInp').value.trim() || 'Exercise';
      const sets = document.getElementById('exSetsInp').value || 1;
      const reps = document.getElementById('exRepsInp').value || 1;

      document.getElementById('exNameText').textContent = name;
      document.getElementById('exSetsText').textContent = sets;
      document.getElementById('exRepsText').textContent = reps;

      document.getElementById('todayExName').textContent = name + " — <?php echo htmlspecialchars($exGroup); ?>";
      document.getElementById('todayExBrief').textContent = sets + " sets × " + reps + " reps — <?php echo htmlspecialchars($exGroup); ?>";

      document.getElementById('exNameField').value  = name;
      document.getElementById('exSetsField').value  = sets;
      document.getElementById('exRepsField').value  = reps;
      document.getElementById('exActionField').value = 'save';

      document.getElementById('exForm').submit();
    } else {
      closeModal('exerciseModal');
    }
  }

  function deleteExercise(){
    document.getElementById('todayExName').textContent  = 'No exercise added';
    document.getElementById('todayExBrief').textContent = 'Add an exercise later';
    document.getElementById('exNameText').textContent   = 'No exercise added';
    document.getElementById('exSetsText').textContent   = '0';
    document.getElementById('exRepsText').textContent   = '0';

    document.getElementById('exActionField').value = 'delete';
    document.getElementById('exForm').submit();
  }
  
 
function refreshCalories() {
    fetch("fetch_remaining.php")
    .then(res => res.json())
    .then(data => {
        if (data.remaining !== undefined) {
            document.getElementById("caloriesVal").textContent = data.remaining;
        }
    })
    .catch(err => console.error("Error fetching remaining:", err));
}

// نجعل الصفحة تحدث السعرات كل 3 ثواني (تلقائي)
setInterval(refreshCalories, 1000);


</script>
</body>
</html> 
