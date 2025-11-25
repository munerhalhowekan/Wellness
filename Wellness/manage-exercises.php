<?php
// manage-exercises.php
// Admin: manage exercises (across beginner/intermediate/advanced) + recommended workouts
// Requires: db-connection.php providing a MySQLi $conn

session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

require 'db-connection.php'; // must set $conn (MySQLi)

// ---------- helpers ----------
function json_response($arr){
    header('Content-Type: application/json');
    echo json_encode($arr);
    exit;
}

function choose_table_and_pk($level){
    // returns [table, pk_column]
    $level = strtolower($level);
    if ($level === 'intermediate') return ['workout_intermediate','IntermediateID'];
    if ($level === 'advanced') return ['workout_advanced','AdvancedID'];
    return ['workout_beginner','BeginnerID'];
}

// sanitize simple string
function clean_str($s){
    return trim((string)$s);
}

// ---------- POST API handlers ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1) Add Exercise
    if ($action === 'add_exercise') {
        $name = clean_str($_POST['name'] ?? '');
        $group = clean_str($_POST['group'] ?? '');
        $level = clean_str($_POST['level'] ?? 'beginner');

        if ($name === '') json_response(['ok'=>false,'error'=>'Exercise name required']);

        list($table,$pk) = choose_table_and_pk($level);
        $sets = intval($_POST['sets'] ?? 3);
        $reps = trim($_POST['reps'] ?? '');

        $stmt = $conn->prepare("INSERT INTO `$table` (workout_group, exercise, sets, reps) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssis', $group, $name, $sets, $reps);
        $ok = $stmt->execute();
        $stmt->close();
        json_response(['ok'=> (bool)$ok]);
    }

    // 2) Edit Exercise
    if ($action === 'edit_exercise') {
        $name = clean_str($_POST['name'] ?? '');
        $group = clean_str($_POST['group'] ?? '');
        $level = clean_str($_POST['level'] ?? 'beginner');
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) json_response(['ok'=>false,'error'=>'Invalid id']);
        if ($name === '') json_response(['ok'=>false,'error'=>'Exercise name required']);

        list($table,$pk) = choose_table_and_pk($level);

        // update in chosen table
        $stmt = $conn->prepare("UPDATE `$table` SET workout_group = ?, exercise = ?, sets = ?, reps = ? WHERE `$pk` = ?");
        $sets = intval($_POST['sets'] ?? 3);
        $reps = trim($_POST['reps'] ?? '');
        $stmt->bind_param('ssisi', $group, $name, $sets, $reps, $id);
        $ok = $stmt->execute();
        $stmt->close();
        json_response(['ok'=> (bool)$ok]);
    }

    // 3) Delete Exercise
    if ($action === 'delete_exercise') {
        $level = clean_str($_POST['level'] ?? 'beginner');
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) json_response(['ok'=>false,'error'=>'Invalid id']);
        list($table,$pk) = choose_table_and_pk($level);
        $stmt = $conn->prepare("DELETE FROM `$table` WHERE `$pk` = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        json_response(['ok'=> (bool)$ok]);
    }

   // 4) Add Recommended Workout (store in recommended_master)
if ($action === 'add_recommended') {
    $name = clean_str($_POST['name'] ?? '');
    $items_raw = $_POST['items'] ?? '[]';
    $items = json_decode($items_raw, true);
    if (!is_array($items)) $items = [];

    if ($name === '') json_response(['ok'=>false,'error'=>'Workout name required']);
    if (count($items) === 0) json_response(['ok'=>false,'error'=>'Select at least one exercise']);

    $items_json = json_encode($items, JSON_UNESCAPED_UNICODE);

    $stmt = $conn->prepare("INSERT INTO recommended_master (name, items, level, created_at) VALUES (?, ?, 'beginner', NOW())");
    $stmt->bind_param('ss', $name, $items_json);
    $ok = $stmt->execute();
    $stmt->close();
    json_response(['ok'=> (bool)$ok]);
}


    // 5) Delete Recommended workout by id
   if ($action === 'delete_recommended') {
    $wid = intval($_POST['id'] ?? 0);
    if ($wid <= 0) json_response(['ok'=>false,'error'=>'Invalid workout id']);

    $stmt = $conn->prepare("DELETE FROM recommended_master WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $wid);
    $ok = $stmt->execute();
    $stmt->close();
    json_response(['ok'=> (bool)$ok]);
}


    // 6) Edit Recommended workout (replace name/items)
    if ($action === 'edit_recommended') {
        $wid = intval($_POST['id'] ?? 0);
        $name = clean_str($_POST['name'] ?? '');
        $items_raw = $_POST['items'] ?? '[]';
        $items = json_decode($items_raw, true);
        if ($wid <= 0) json_response(['ok'=>false,'error'=>'Invalid id']);
        if ($name === '') json_response(['ok'=>false,'error'=>'Workout name required']);
        if (!is_array($items)) $items = [];
        $items_json = json_encode($items, JSON_UNESCAPED_UNICODE);
$stmt = $conn->prepare("UPDATE recommended_master SET name = ?, items = ? WHERE id = ?");
        $stmt->bind_param('ssi', $name, $items_json, $wid);
        $ok = $stmt->execute();
        $stmt->close();
        json_response(['ok' => (bool)$ok]);
    }

    // unknown action
    json_response(['ok'=>false,'error'=>'Unknown action']);
}

// ---------- GET data for rendering page ----------
// Fetch exercises from three tables and unify them
$exercises = [];
// beginner
$res = $conn->query("SELECT BeginnerID AS id, workout_group, exercise, sets, reps, 'beginner' AS level FROM workout_beginner");
if ($res) {
    while ($r = $res->fetch_assoc()) $exercises[] = $r;
    $res->free();
}
// intermediate
$res = $conn->query("SELECT IntermediateID AS id, workout_group, exercise, sets, reps, 'intermediate' AS level FROM workout_intermediate");
if ($res) {
    while ($r = $res->fetch_assoc()) $exercises[] = $r;
    $res->free();
}
// advanced
$res = $conn->query("SELECT AdvancedID AS id, workout_group, exercise, sets, reps, 'advanced' AS level FROM workout_advanced");
if ($res) {
    while ($r = $res->fetch_assoc()) $exercises[] = $r;
    $res->free();
}

// Fetch recommended workouts (user_id = 0)
$recommended = [];
$res = $conn->query("SELECT id, name, items, created_at FROM recommended_master ORDER BY created_at DESC");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $r['items_arr'] = json_decode($r['items'], true) ?: [];
        $recommended[] = $r;
    }
    $res->free();
}

// For client-side, we will pass exercises & recommended as JSON
$ex_json = json_encode($exercises, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE);
$rec_json = json_encode($recommended, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE);

// ---------- Render page ----------
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Exercises — Wellness Admin</title>
<style>
    header{
 position:sticky;top:0;z-index:50;
 background:var(--layer);
 border-bottom:1px solid var(--border);
 display:flex;align-items:center;justify-content:space-between;
 padding:14px 18px;
}
.hamburger{font-size:24px;cursor:pointer}

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

.link-right{
  color:var(--accent);
  font-weight:700;
  text-decoration:none;
}

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
/* (use your CSS — trimmed for brevity but keep the same look) */
:root{--bg:#1c1c1c;--layer:#222;--card:#2a2a2a;--text:#fff;--muted:#cfcfcf;--border:#3a3a3a;--accent:#ff6600}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font-family:"Poppins",Arial}
header{position:sticky;top:0;background:var(--layer);padding:12px 18px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border)}
.hamburger{cursor:pointer;font-size:22px}
.drawer{position:fixed;left:-260px;top:0;height:100vh;width:260px;background:var(--card);border-right:1px solid var(--border);padding:18px;transition:left .25s}
.drawer.open{left:0}
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);display:none}
.overlay.show{display:block}
main{padding:22px;max-width:1300px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:22px}
.container{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:18px;min-height:60vh}
.searchbar{display:flex;padding:8px;background:var(--layer);border-radius:10px;margin-bottom:12px}
.searchbar input{flex:1;background:transparent;border:none;color:var(--text);outline:none;padding:6px}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;overflow:auto}
.card{background:var(--layer);border:1px solid var(--border);border-radius:12px;padding:12px}
.pill{display:inline-block;background:#333;border-radius:999px;padding:6px 10px}
.btn{background:var(--accent);border:none;color:#fff;padding:9px 12px;border-radius:8px;cursor:pointer}
.btn.ghost{background:#444}
.btn.danger{background:#c33}
.modal{position:fixed;inset:0;background:rgba(0,0,0,.65);display:none;align-items:center;justify-content:center;padding:12px;z-index:90}
.modal.open{display:flex}
.sheet{background:var(--card);padding:18px;border-radius:12px;width:min(720px,96vw);max-height:90vh;overflow:auto;border:1px solid var(--border)}
.field{display:flex;gap:10px;margin:10px 0}
.field label{min-width:140px;color:var(--muted)}
.field input[type="text"], .field input[type="number"]{flex:1;background:#2f2f2f;border:1px solid var(--border);color:#fff;padding:8px;border-radius:8px}
.picklist{border:1px solid var(--border);background:#2a2a2a;padding:8px;border-radius:10px;max-height:46vh;overflow:auto}
.pick-row{display:grid;grid-template-columns:28px 1fr 78px 78px;gap:8px;padding:8px 0;border-bottom:1px dashed #3a3a3a}
.pick-row:last-child{border-bottom:none}
.pick-row.disabled{opacity:.5}
.actions{display:flex;gap:8px;margin-top:10px;justify-content:flex-end}
@media(max-width:1000px){main{grid-template-columns:1fr}}
</style>
</head>
<body>

<!-- Drawer -->
<!-- DRAWER -->
<div id="drawer" class="drawer">
    <h4>Navigation</h4>
 <a href="admin-dashboard.php">🏠 Dashboard</a>
  <a href="manage-users.php">👥 Manage Users</a>
  <a href="manage-exercises.php" class="active">💪 Manage Exercises</a>
  <a href="manage-diet.php">🥗 Manage Diet Plans</a>
</div>

<div id="overlay" class="overlay" onclick="toggleDrawer(false)"></div>


<!-- Header -->
<!-- HEADER -->
<header>
  <div class="hamburger" onclick="toggleDrawer()">☰</div>
  <div style="font-weight:800">Manage Exercises</div>
  <a class="link-right" href="recommended.php">Recommended</a>
</header>


<main>
  <!-- LEFT: EXERCISES -->
  <section class="container">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
      <div style="font-weight:800;color:var(--accent)">Exercises</div>
      <div><button class="btn" onclick="openAddExercise()">+ Add Exercise</button></div>
    </div>

    <div class="searchbar"><input id="exSearch" placeholder="Search exercises..." oninput="renderExercises()"></div>

    <div id="exerciseGrid" class="grid" aria-live="polite"></div>
  </section>

  <!-- RIGHT: Recommended Workouts -->
  <section class="container">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
      <div style="font-weight:800;color:var(--accent)">Recommended Workouts</div>
      <div><button class="btn" onclick="openAddWorkout()">+ Add Workout</button></div>
    </div>

    <div id="workoutGrid" class="grid" aria-live="polite"></div>
  </section>
</main>

<!-- Exercise Modal -->
<div id="exerciseModal" class="modal" 
     >
  <div class="sheet">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <h2 id="exTitle">Add Exercise</h2>
      <button id="exCloseBtn" onclick="closeExerciseModal()" style="background:none;border:none;color:#fff;font-size:20px">✕</button>
    </div>
    <div class="field"><label>Exercise name</label><input id="exName" type="text" placeholder="e.g. Lat Pulldown"></div>
    <div class="field"><label>Muscle group</label><input id="exGroup" type="text" placeholder="e.g. Back"></div>
    <div class="field"><label>Level</label>
      <select id="exLevel" style="background:#2f2f2f;color:#fff;padding:8px;border-radius:8px;border:1px solid var(--border)">
        <option value="beginner">Beginner</option>
        <option value="intermediate">Intermediate</option>
        <option value="advanced">Advanced</option>
      </select>
    </div>
    <div class="field"><label>Sets</label><input id="exSets" type="number" min="1" value="3"></div>
    <div class="field"><label>Reps</label><input id="exReps" type="text" placeholder="e.g. 10-12"></div>

    <div class="actions">
      <button class="btn ghost" onclick="closeExerciseModal()">Cancel</button>
      <button class="btn" id="exSaveBtn" onclick="saveExercise()">Save</button>
    </div>
  </div>
</div>

<!-- Workout Modal -->
<div id="workoutModal" class="modal" >
  <div class="sheet">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <h2 id="woTitle">Add Workout</h2>
      <button id="woCloseBtn" onclick="closeWorkoutModal()" style="background:none;border:none;color:#fff;font-size:20px">✕</button>
    </div>

    <div class="field"><label>Workout name</label><input id="woName" type="text" placeholder="e.g. Back & Biceps"></div>
    <div style="margin:6px 0;color:var(--muted)">Select exercises and set default sets & reps:</div>

    <div id="pickList" class="picklist"></div>

    <div class="actions">
      <button class="btn ghost" onclick="closeWorkoutModal()">Cancel</button>
      <button class="btn" id="woSaveBtn" onclick="saveWorkout()">Save</button>
    </div>
  </div>
</div>

<script>
/* initial data from server */
const exercises = <?php echo $ex_json; ?>;   // array of {id, workout_group, exercise, sets, reps, level}
const recommended = <?php echo $rec_json; ?>; // array of {id, name, items_arr, created_at}

/* drawer */
function toggleDrawer(force){
  const d=document.getElementById('drawer'), o=document.getElementById('overlay');
  const open = (typeof force === 'boolean')? force : !d.classList.contains('open');
  d.classList.toggle('open', open);
  o.classList.toggle('show', open);
}
document.getElementById('overlay').addEventListener('click', ()=>toggleDrawer(false));

/* Render exercises */
let filteredExercises = [...exercises];
function renderExercises(){
  const q = (document.getElementById('exSearch').value || '').trim().toLowerCase();
  const grid = document.getElementById('exerciseGrid');
  grid.innerHTML = '';
  filteredExercises = exercises.filter(e => {
    return (e.exercise||'').toLowerCase().includes(q) || (e.workout_group||'').toLowerCase().includes(q) || (e.level||'').toLowerCase().includes(q);
  });
  if (filteredExercises.length === 0) {
    const no = document.createElement('div');
    no.className = 'card';
    no.innerHTML = '<div style="color:#cfcfcf">No results found. Try a different search term.</div>';
    grid.appendChild(no);
    return;
  }
  filteredExercises.forEach((e, idx) => {
    const div = document.createElement('div');
    div.className = 'card';
    div.innerHTML = `
      <div style="display:flex;justify-content:space-between;align-items:center">
        <div>
          <div style="font-weight:800">${escapeHtml(e.exercise)}</div>
          <div style="color:#cfcfcf;font-size:.9rem">${escapeHtml(e.workout_group || '')} • <span class="pill">${escapeHtml(e.level)}</span></div>
          <div style="color:#9f9f9f;font-size:.85rem;margin-top:6px">${e.sets} sets × ${escapeHtml(e.reps || '')} reps</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;margin-left:8px">
          <button class="btn ghost" onclick="openEditExercise(${idx})">Edit</button>
          <button class="btn danger" onclick="deleteExercise(${idx})">Delete</button>
        </div>
      </div>
    `;
    grid.appendChild(div);
  });
}

/* Render recommended workouts */
function renderWorkouts(){
  const grid = document.getElementById('workoutGrid');
  grid.innerHTML = '';
  if (!recommended.length) {
    const no = document.createElement('div'); no.className='card'; no.innerHTML='<div style="color:#cfcfcf">No recommended workouts yet</div>'; grid.appendChild(no); return;
  }
  recommended.forEach((w, idx) => {
    const card = document.createElement('div'); card.className = 'card';
    const count = (w.items_arr||[]).length;
    const tags = (w.items_arr||[]).slice(0,3).map(it => `<span class="pill">${escapeHtml(it.name || it.exercise || it.exId || 'Unknown')}</span>`).join(' ');
    card.innerHTML = `
      <h3 style="margin:0;color:var(--accent)">${escapeHtml(w.name)}</h3>
      <p style="color:#cfcfcf;margin:6px 0">${count} exercise${count!==1?'s':''}</p>
      <div style="display:flex;gap:8px;flex-wrap:wrap">${tags}</div>
      <div style="display:flex;gap:8px;margin-top:10px">
        <button class="btn ghost" onclick="openEditWorkout(${idx})">Edit</button>
        <button class="btn danger" onclick="deleteRecommended(${idx})">Delete</button>
      </div>
    `;
    grid.appendChild(card);
  });
}

/* --- Exercise Modal Logic --- */
let editingExerciseIndex = null;
function openAddExercise(){
  editingExerciseIndex = null;
  document.getElementById('exTitle').textContent = 'Add Exercise';
  document.getElementById('exName').value = '';
  document.getElementById('exGroup').value = '';
  document.getElementById('exLevel').value = 'beginner';
  document.getElementById('exSets').value = 3;
  document.getElementById('exReps').value = '';
  const modal = document.getElementById('exerciseModal');
  modal.classList.add('open');
  modal.setAttribute('false');
  // focus first input for accessibility
  setTimeout(()=>document.getElementById('exName').focus(), 10);
}
function openEditExercise(idx){
  const e = filteredExercises[idx];
  if (!e) return alert('Not found');
  // find the real index in exercises[]
  const realIdx = exercises.findIndex(x => x.id == e.id && x.level == e.level);
  editingExerciseIndex = realIdx;
  document.getElementById('exTitle').textContent = 'Edit Exercise';
  document.getElementById('exName').value = e.exercise;
  document.getElementById('exGroup').value = e.workout_group;
  document.getElementById('exLevel').value = e.level;
  document.getElementById('exSets').value = e.sets;
  document.getElementById('exReps').value = e.reps;
  const modal = document.getElementById('exerciseModal');
  modal.classList.add('open');
  modal.setAttribute('false');
  setTimeout(()=>document.getElementById('exName').focus(), 10);
}
function closeExerciseModal(){
  const modal = document.getElementById('exerciseModal');
  modal.classList.remove('open');
  modal.setAttribute('true');
}

/* save exercise */
function saveExercise(){
  const name = document.getElementById('exName').value.trim();
  const group = document.getElementById('exGroup').value.trim();
  const level = document.getElementById('exLevel').value;
  const sets = parseInt(document.getElementById('exSets').value) || 1;
  const reps = document.getElementById('exReps').value.trim();

  if (!name) return alert('Please enter exercise name.');

  const form = new URLSearchParams();
  if (editingExerciseIndex === null) {
    form.append('action','add_exercise');
  } else {
    form.append('action','edit_exercise');
    const e = exercises[editingExerciseIndex];
    form.append('id', e.id);
  }
  form.append('name', name);
  form.append('group', group);
  form.append('level', level);
  form.append('sets', sets);
  form.append('reps', reps);

  fetch(location.href, { method:'POST', body: form })
    .then(r=>r.json()).then(j=>{
      if (j.ok) location.reload(); else alert(j.error || 'Failed');
    }).catch(()=> alert('Request failed'));
}

/* delete exercise */
function deleteExercise(idx){
  const e = filteredExercises[idx];
  if (!e) return alert('Not found');
  if (!confirm('Delete this exercise?')) return;
  const form = new URLSearchParams();
  form.append('action','delete_exercise');
  form.append('id', e.id);
  form.append('level', e.level);

  fetch(location.href, { method:'POST', body: form })
    .then(r=>r.json()).then(j=>{
      if (j.ok) location.reload(); else alert(j.error || 'Failed to delete');
    }).catch(()=> alert('Request failed'));
}

/* --- Workouts (recommended) modal --- */
let editingWorkoutIndex = null;
function openAddWorkout(){
  editingWorkoutIndex = null;
  document.getElementById('woTitle').textContent = 'Add Workout';
  document.getElementById('woName').value = '';
  buildPicker({ items_arr: [] });
  const modal = document.getElementById('workoutModal');
  modal.classList.add('open');
  modal.setAttribute('false');
  setTimeout(()=>document.getElementById('woName').focus(), 10);
}
function openEditWorkout(idx){
  editingWorkoutIndex = idx;
  const w = recommended[idx];
  if (!w) return;
  document.getElementById('woTitle').textContent = 'Edit Workout';
  document.getElementById('woName').value = w.name || '';
  // support both shapes: { items_arr } or { items }
  const payload = w.items_arr || w.items || [];
  buildPicker({ items_arr: payload });
  const modal = document.getElementById('workoutModal');
  modal.classList.add('open');
  modal.setAttribute('false');
  setTimeout(()=>document.getElementById('woName').focus(), 10);
}
function closeWorkoutModal(){
  const modal = document.getElementById('workoutModal');
  modal.classList.remove('open');
  modal.setAttribute('true');
}

/* build picker: list all exercises; if workout provided, pre-check with sets/reps */
function buildPicker(workout){
  const list = document.getElementById('pickList');
  list.innerHTML = '';

  // normalize selected items: accept items_arr or items; keys by exId (preferred) or by normalized name
  const selected = new Map();
  const arr = workout.items_arr || workout.items || [];
  arr.forEach(it => {
    const obj = {
      sets: (it.sets != null ? it.sets : 3),
      reps: (it.reps != null ? it.reps : ''),
      name: (it.name || it.exercise || '').toString(),
      exId: (it.exId != null ? String(it.exId) : '')
    };
    if (obj.exId) selected.set(obj.exId, obj);
  });

  exercises.forEach(ex => {
    const row = document.createElement('div');
    row.className = 'pick-row';

const exIdKey = `${ex.level}_${ex.id}`;
    const exNameKey = String((ex.exercise || '').toLowerCase().trim());

   let pre = null;
if (selected.has(exIdKey)) {
    pre = selected.get(exIdKey);
}


    const defaultSets = (ex.sets != null ? ex.sets : 3);
    const defaultReps = (ex.reps != null ? ex.reps : '');

    const setsVal = (pre && pre.sets != null) ? pre.sets : defaultSets;
    const repsVal = (pre && pre.reps != null) ? pre.reps : defaultReps;

    // store defaults for fallback
    row.dataset.defaultSets = String(defaultSets);
    row.dataset.defaultReps = String(defaultReps);

    // Build inner HTML with proper inputs; note order: checkbox, info, number, text
    row.innerHTML = `
      <input type="checkbox" ${pre ? 'checked' : ''}>
      <div>
        <div style="font-weight:700">${escapeHtml(ex.exercise)}</div>
        <div style="font-size:.85rem;color:var(--muted)">
          ${escapeHtml(ex.workout_group||'')} • ${escapeHtml(ex.level)}
        </div>
      </div>
      <input type="number" min="1" value="${escapeAttr(setsVal)}" ${pre ? '' : 'disabled'}>
      <input type="text" value="${escapeAttr(repsVal)}" ${pre ? '' : 'disabled'}>
    `;

    // Select inputs robustly by type to avoid index issues
    const cb = row.querySelector('input[type="checkbox"]');
    const setsIn = row.querySelector('input[type="number"]');
    const repsIn = row.querySelector('input[type="text"]');

    cb.addEventListener('change', () => {
      const on = cb.checked;
      if (setsIn) {
        setsIn.disabled = !on;
        if (on && (setsIn.value === '' || setsIn.value === '0')) setsIn.value = row.dataset.defaultSets || '1';
      }
      if (repsIn) {
        repsIn.disabled = !on;
        if (on && (repsIn.value === '')) repsIn.value = row.dataset.defaultReps || '';
      }
      row.classList.toggle('disabled', !on);
    });

    row.dataset.name = ex.exercise || '';
row.dataset.id = `${ex.level}_${ex.id}`;

    if (!pre) row.classList.add('disabled');
    list.appendChild(row);
  });
}

/* save recommended workout */
function saveWorkout(){
  const name = document.getElementById('woName').value.trim();
  if (!name) return alert('Please enter workout name.');
  const rows = [...document.querySelectorAll('#pickList .pick-row')];
  const items = [];

  rows.forEach(r => {
    const cb = r.querySelector('input[type="checkbox"]');
    if (!cb) return;
    if (cb.checked) {
  const exerciseId = String(r.dataset.id);
const exerciseName = r.dataset.name; 


      const setsIn = r.querySelector('input[type="number"]');
      const repsIn = r.querySelector('input[type="text"]');

      // robust reading with fallback to data-* defaults
      let setsVal = 1;
      if (setsIn) {
        const sRaw = ('' + setsIn.value).trim();
        if (sRaw === '') setsVal = parseInt(r.dataset.defaultSets || '1', 10);
        else {
          setsVal = parseInt(sRaw, 10);
          if (!isFinite(setsVal) || setsVal < 1) setsVal = parseInt(r.dataset.defaultSets || '1', 10);
        }
      } else {
        setsVal = parseInt(r.dataset.defaultSets || '1', 10);
      }

      let repsVal = '';
      if (repsIn) repsVal = ('' + repsIn.value).trim();
      else repsVal = r.dataset.defaultReps || '';

      const item = { name: exerciseName, sets: setsVal, reps: repsVal };
      if (exerciseId) item.exId = exerciseId;
      items.push(item);
    }
  });

  if (items.length === 0) return alert('Select at least one exercise.');

  const form = new URLSearchParams();
  if (editingWorkoutIndex === null) {
    form.append('action','add_recommended');
  } else {
    const workout = recommended[editingWorkoutIndex];
    if (!workout) return alert('Not found');
    form.append('action','edit_recommended');
    form.append('id', workout.id);
  }

  form.append('name', name);
  form.append('items', JSON.stringify(items));

  fetch(location.href, { method:'POST', body: form })
    .then(r=>r.json())
    .then(j=>{
      if (j.ok) {
        location.reload();
      } else {
        alert(j.error || 'Failed to save workout');
      }
    })
    .catch(()=> alert('Request failed'));
}

function deleteRecommended(idx){
  const w = recommended[idx];
  if (!w) return alert('Not found');
  if (!confirm('Delete this recommended workout?')) return;
  const form = new URLSearchParams();
  form.append('action','delete_recommended');
  form.append('id', w.id);
  fetch(location.href, { method:'POST', body: form })
    .then(r=>r.json()).then(j=>{
      if (j.ok) location.reload(); else alert(j.error || 'Failed');
    }).catch(()=> alert('Request failed'));
}

/* utils */
function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function escapeAttr(s){ if(!s && s!==0) return ''; return String(s).replace(/"/g,'&quot;'); }

/* initial render */
renderExercises();
renderWorkouts();

function toggleDrawer(force){
  const dr=document.getElementById('drawer');
  const ov=document.getElementById('overlay');
  const open=(typeof force==='boolean')?force:!dr.classList.contains('open');
  dr.classList.toggle('open',open);
  ov.classList.toggle('show',open);
}




</script>

</body>
</html>
