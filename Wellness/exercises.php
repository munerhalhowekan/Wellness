<?php
session_start();
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

require 'db-connection.php';

// fallback
if(!isset($_SESSION['user_id'])) $_SESSION['user_id']=9;
$user_id=intval($_SESSION['user_id']);

// get level
$fitness_level='beginner';
$stmt=$conn->prepare("SELECT fitness_level FROM users WHERE UserID=?");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$res=$stmt->get_result();
if($row=$res->fetch_assoc()){
    if(!empty($row['fitness_level']))
        $fitness_level=$row['fitness_level'];
}
$stmt->close();

// table
$table='workout_beginner';
if($fitness_level==='intermediate') $table='workout_intermediate';
if($fitness_level==='advanced') $table='workout_advanced';

// save workout
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='save_workout'){
    $name=trim($_POST['name']) ?: 'Custom Workout';
    $items_raw=$_POST['items'] ?? '[]';
    $arr=json_decode($items_raw,true);
    if(!is_array($arr)) $arr=[];
    $json=json_encode($arr,JSON_UNESCAPED_UNICODE);
    $ins=$conn->prepare("INSERT INTO user_workouts (user_id,name,items) VALUES (?,?,?)");
    $ins->bind_param("iss",$user_id,$name,$json);
    $ok=$ins->execute();
    $ins->close();
    header('Content-Type: application/json');
    echo json_encode(['ok'=>$ok]);
    exit;
}

// fetch exercises
$q=$conn->query("SELECT workout_group,exercise,sets,reps FROM `$table` ORDER BY workout_group,exercise");
$groups=[];
while($ex=$q->fetch_assoc()){
    $gid=preg_replace('/[^a-z0-9]+/i','_',strtolower($ex['exercise']));
    $groups[$ex['workout_group']][]=[
        'id'=>$gid,
        'name'=>$ex['exercise'],
        'sets'=>$ex['sets'],
        'reps'=>$ex['reps']
    ];
}
$groups_json=json_encode($groups,JSON_UNESCAPED_UNICODE);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Exercises — Wellness</title>

<style>
:root{
 --bg:#1c1c1c;--layer:#222;--card:#2a2a2a;--text:#fff;
 --muted:#cfcfcf;--border:#3a3a3a;--accent:#ff6600;
}
body{margin:0;background:var(--bg);color:var(--text);font-family:"Poppins",Arial}
*{box-sizing:border-box}

/* HEADER */
header{
 position:sticky;top:0;z-index:50;
 background:var(--layer);
 border-bottom:1px solid var(--border);
 display:flex;align-items:center;justify-content:space-between;
 padding:14px 18px;
}
.hamburger{font-size:24px;cursor:pointer}

/* DRAWER */
.drawer{
 position:fixed;left:-260px;top:0;height:100vh;width:260px;
 background:var(--card);border-right:1px solid var(--border);
 padding:18px;transition:.25s;z-index:80;
}
.drawer.open{left:0}
.drawer h4{margin:0 0 12px;color:var(--accent)}
.drawer a{
 display:block;padding:10px;border-radius:8px;color:#fff;
 text-decoration:none;margin-bottom:6px;
}
.drawer a:hover{background:rgba(255,102,0,.25)}

.overlay{
 position:fixed;inset:0;background:rgba(0,0,0,.5);
 display:none;z-index:70;
}
.overlay.show{display:block}

/* MAIN */
main{padding:18px;max-width:1100px;margin:auto}

.topbar{display:flex;gap:12px;margin-bottom:16px}
.search{flex:1}
.grid{
 display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
 gap:12px;
}
.card{
 background:var(--card);border:1px solid var(--border);
 padding:14px;border-radius:12px;cursor:pointer
}
.card:hover{border-color:var(--accent)}

.btn{
 background:var(--accent);color:#fff;border:none;cursor:pointer;
 padding:10px 14px;border-radius:8px;font-weight:600
}

/* MODAL */
.modal{position:fixed;inset:0;background:rgba(0,0,0,.6);
 display:none;align-items:center;justify-content:center;z-index:90}
.modal.open{display:flex}
.sheet{
 background:#222;padding:18px;border-radius:12px;
 width:min(600px,95vw);border:1px solid var(--border)
}
.row{display:flex;gap:8px;justify-content:space-between;margin:8px 0}
input{background:#2f2f2f;border:1px solid var(--border);color:#fff;border-radius:6px;padding:8px}
.link-right{color:var(--accent);font-weight:700}
</style>
</head>
<body>

<!-- DRAWER -->
<div id="drawer" class="drawer">
    <h4>Navigation</h4>
    <a href="user-dashboard.php">🏠 Dashboard</a>
    <a href="exercises.php">💪 Exercises</a>
    <a href="dietplan.php">🥗 Diet Plan</a>
    <a href="leaderboard.php">🏆 Leaderboard</a>
</div>
<div id="overlay" class="overlay" onclick="toggleDrawer(false)"></div>

<!-- HEADER -->
<header>
  <div class="hamburger" onclick="toggleDrawer()">☰</div>
  <div style="font-weight:800">Exercises</div>
  <a class="link-right" href="recommended.php">Recommended</a>
</header>

<main>

  <div class="topbar">
    <div class="search">
      <input id="searchInput" placeholder="Search exercise..." style="width:100%">
    </div>
    <button id="selectToggle" class="btn">Select</button>
  </div>

  <div id="grid" class="grid"></div>

  <button id="addFab" class="btn" style="position:fixed;left:16px;bottom:20px;" disabled>
    + Add Workout
  </button>

  <!-- MODAL -->
  <div id="modal" class="modal">
    <div class="sheet">
      <h3>Create Workout</h3>
      <label>Workout name</label>
      <input id="woName" placeholder="e.g. Push Day" style="width:100%">
      <div id="woList" style="max-height:50vh;overflow:auto;margin-top:10px"></div>
      <div style="text-align:right;margin-top:12px">
        <button onclick="closeModal()" class="btn" style="background:#555">Cancel</button>
        <button onclick="saveWorkout()" class="btn">Add to Dashboard</button>
      </div>
    </div>
  </div>

</main>

<script>
/* --- Drawer --- */
function toggleDrawer(force){
  const dr=document.getElementById('drawer');
  const ov=document.getElementById('overlay');
  const open=(typeof force==='boolean')?force:!dr.classList.contains('open');
  dr.classList.toggle('open',open);
  ov.classList.toggle('show',open);
}

/* PHP data */
const groups = <?php echo $groups_json; ?>;

let selectMode=false;
const selected=new Map();

function renderGrid(){
  const q=document.getElementById('searchInput').value.toLowerCase();
  const grid=document.getElementById('grid');
  grid.innerHTML='';

  for(const [grp,items] of Object.entries(groups)){
    items.forEach(it=>{
      if(!it.name.toLowerCase().includes(q)) return;

      const div=document.createElement('div');
      div.className='card';
      div.innerHTML=`<strong>${it.name}</strong><br><span style="color:#ccc">${grp} • ${it.sets}×${it.reps}</span>`;
      div.onclick=()=>onClick(it);

      if(selected.has(it.id)) div.style.outline='3px solid var(--accent)';

      grid.appendChild(div);
    });
  }

  /* --- No results found message --- */
  if(grid.innerHTML.trim()===""){
    grid.innerHTML = `
      <div style="grid-column:1/-1;text-align:center;color:#bbb;padding:20px;font-size:16px">
        No results found. Try a different search term.
      </div>
    `;
  }

  document.getElementById('addFab').disabled=(selected.size===0);
}

document.getElementById('searchInput').oninput=renderGrid;

function onClick(item){
  if(!selectMode){
    alert(`${item.name}\n${item.sets} sets × ${item.reps}`);
    return;
  }
  if(selected.has(item.id)) selected.delete(item.id);
  else selected.set(item.id,{...item});
  renderGrid();
}

document.getElementById('selectToggle').onclick=()=>{
  selectMode=!selectMode;
  document.getElementById('selectToggle').textContent=
      selectMode?'Selecting…':'Select';
  if(!selectMode) selected.clear();
  renderGrid();
};

/* Modal */
function openModal(){
  const list=document.getElementById('woList');
  list.innerHTML='';
  for(const item of selected.values()){
    const row=document.createElement('div');
    row.className='row';
    row.innerHTML=`
      <div style="font-weight:700">${item.name}</div>
      <input class="sets" type="number" value="${item.sets}">
      <input class="reps" type="text" value="${item.reps}">
    `;
    row.querySelector('.sets').oninput=e=>item.sets=e.target.value;
    row.querySelector('.reps').oninput=e=>item.reps=e.target.value;
    list.appendChild(row);
  }
  document.getElementById('modal').classList.add('open');
}

document.getElementById('addFab').onclick=openModal;
function closeModal(){ document.getElementById('modal').classList.remove('open'); }

function saveWorkout(){
const name=document.getElementById('woName').value.trim();

// NAME VALIDATION
if (!name) {
    alert("Please enter a workout name.");
    return;
}
  const items=[...selected.values()].map(i=>({name:i.name,sets:i.sets,reps:i.reps}));

  /* VALIDATION */
  for (const it of items) {

    if (!it.sets || isNaN(it.sets) || it.sets <= 0) {
        alert("Please enter a valid number of sets.");
        return;
    }

    if (!it.reps || it.reps.trim() === "") {
        alert("Please enter valid reps.");
        return;
    }
  }

  const form=new URLSearchParams();
  form.append('action','save_workout');
  form.append('name',name);
  form.append('items',JSON.stringify(items));

  fetch(location.href,{method:'POST',body:form})
  .then(r=>r.json()).then(j=>{
    alert(j.ok?'Workout added successfully to Dashboard':'Failed to save.');
    closeModal();selected.clear();renderGrid();
  });
}

renderGrid();
</script>

</body>
</html>
