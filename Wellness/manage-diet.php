<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include "db-connection.php";

// جدول → اسم الخطة
$plans = [
    "diet_pcos"            => "PCOS",
    "diet_insulin_resist"  => "Insulin Resistance",
    "diet_glutenfree"      => "Gluten Intolerance"
];

$allPlans = [];

// نجيب كل الـ 7 أيام من كل جدول
foreach ($plans as $table => $planName) {
    $q = $conn->query("SELECT * FROM $table ORDER BY FIELD(day,'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')");
    while ($row = $q->fetch_assoc()) {
        $row['planName']  = $planName;
        $row['tableName'] = $table;
        $allPlans[]       = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Diet Plans — Admin</title>
<style>
:root {
  --bg:#1c1c1c; --layer:#222; --card:#2a2a2a; --text:#fff; --muted:#cfcfcf;
  --border:#3a3a3a; --accent:#ff6600; --accent2:#ff8533;
  --shadow:0 0 18px rgba(255,102,0,.28);
}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font-family:"Poppins","Segoe UI",system-ui,sans-serif;}
header{
  position:sticky;top:0;z-index:60;background:var(--layer);
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
  padding:12px 18px;box-shadow:0 4px 12px rgba(0,0,0,.25);
}
.hamburger{cursor:pointer;font-size:24px;user-select:none}
.page{font-weight:800}
.logo-link{display:flex;align-items:center}
.logo-img{width:120px;height:auto;object-fit:contain}

/* Drawer */
.drawer{
  position:fixed;left:-260px;top:0;height:100vh;width:260px;background:var(--card);
  border-right:1px solid var(--border);box-shadow:16px 0 30px rgba(0,0,0,.45);
  padding:18px;z-index:80;transition:left .28s ease;
}
.drawer.open{left:0}
.drawer h4{margin:4px 8px 12px;color:var(--accent);}
.drawer a{display:block;color:var(--text);text-decoration:none;padding:10px;border-radius:10px;margin:6px 0;}
.drawer a:hover{background:rgba(255,102,0,.15);}
.drawer a.active{background:var(--accent);color:#fff;}
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);display:none;z-index:70;}
.overlay.show{display:block;}

/* Main */
main{padding:30px;max-width:1200px;margin:auto;}
.page-title{font-size:1.6rem;font-weight:700;color:var(--accent);}
.subtitle{color:var(--muted);margin-bottom:20px;}
table{
  width:100%;border-collapse:collapse;background:var(--card);border-radius:10px;overflow:hidden;box-shadow:var(--shadow);
}
th,td{padding:14px 12px;border-bottom:1px solid var(--border);text-align:left;}
th{color:var(--accent);font-weight:700;border-bottom:2px solid var(--accent2);}
tr:hover{background:#2f2f2f;}
.actions button{
  background:transparent;border:1.5px solid var(--accent);color:var(--accent);
  border-radius:8px;padding:4px 10px;margin-right:6px;cursor:pointer;font-weight:600;transition:.2s;
}
.actions button:hover{background:var(--accent);color:#fff;}

/* Modal */
.modal{
  position:fixed;inset:0;background:rgba(0,0,0,.7);
  display:none;align-items:center;justify-content:center;z-index:90;
}
.modal.open{display:flex;}
.modal-content{
  background:var(--card);border:1px solid var(--border);border-radius:14px;
  box-shadow:var(--shadow);padding:24px;width:min(550px,95%);max-height:90vh;overflow-y:auto;
}
.modal-content h2{text-align:center;color:var(--accent);margin-top:0;}
.form-group{margin-bottom:16px;}
label{display:block;font-weight:600;margin-bottom:6px;}
input,textarea{
  width:100%;padding:8px;background:#333;border:1px solid var(--border);
  border-radius:8px;color:#fff;
}
textarea{min-height:60px;resize:vertical;}
.modal-buttons{
  display:flex;justify-content:space-between;margin-top:20px;
}
.modal-buttons button{
  flex:1;margin:0 6px;padding:8px 10px;border:none;border-radius:8px;font-weight:700;cursor:pointer;
}
#cancelBtn{background:#444;color:#fff;}
#saveBtn{background:var(--accent);color:#fff;}
#cancelBtn:hover{background:#555;}
#saveBtn:hover{background:var(--accent2);}
</style>
</head>
<body>

<!-- Drawer -->
<aside id="drawer" class="drawer">
  <h4>Admin Navigation</h4>
  <a href="admin-dashboard.php">Dashboard</a>
  <a href="manage-users.php">Manage Users</a>
  <a href="manage-exercises.php">Manage Exercises</a>
  <a class="active" href="manage-diet.php">Manage Diet</a>
</aside>
<div id="overlay" class="overlay" onclick="toggleDrawer(false)"></div>

<header>
  <div class="hamburger" onclick="toggleDrawer()">☰</div>
  <div class="page">Manage Diet Plans</div>
</header>

<main>

<table>
<thead>
<tr>
  <th>Plan Name / Day</th>
  <th>Total Calories</th>
  <th>Actions</th>
</tr>
</thead>

<tbody>
<?php foreach ($allPlans as $p): ?>
<tr data-table="<?= $p['tableName']; ?>" data-id="<?= $p[array_key_first($p)]; ?>">
  <td><?= $p['planName'].' — '.$p['day']; ?></td>
  <td><?= $p['total_calories_per_day']; ?></td>
  <td>
    <button onclick='openEdit(<?= json_encode($p) ?>)'>Edit</button>
    <button onclick="deletePlan('<?= $p['tableName'] ?>','<?= $p[array_key_first($p)] ?>')">Delete</button>
  </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</main>

<!-- EDIT MODAL -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <h2>Edit Diet Plan</h2>

    <form id="editForm">

      <input type="hidden" name="table">
      <input type="hidden" name="id">

      <label>Plan Name</label>
      <input type="text" id="planName" disabled>

      <label>Day</label>
      <input type="text" id="day" disabled>

      <h3>Breakfast</h3>
      <textarea name="breakfast" id="breakfast"></textarea>
      <label>Calories</label>
      <input type="number" name="b_calories" id="b_calories">

      <h3>Lunch</h3>
      <textarea name="lunch" id="lunch"></textarea>
      <label>Calories</label>
      <input type="number" name="l_calories" id="l_calories">

      <h3>Dinner</h3>
      <textarea name="dinner" id="dinner"></textarea>
      <label>Calories</label>
      <input type="number" name="d_calories" id="d_calories">

      <button type="button" onclick="saveEdit()">Save Changes</button>
      <button type="button" onclick="closeEdit()">Cancel</button>

    </form>

  </div>
</div>

<script>
function toggleDrawer(force){
  const d=document.getElementById('drawer'),o=document.getElementById('overlay');
  const open = (typeof force==='boolean')?force:!d.classList.contains('open');
  d.classList.toggle('open',open); o.classList.toggle('show',open);
}

function openEdit(p){
  document.getElementById("editModal").classList.add("open");

  // تعبئة الفورم
  document.querySelector("input[name='table']").value = p.tableName;
  document.querySelector("input[name='id']").value = p.PcosID ?? p.InsulinID ?? p.GlutenfreeID;

  document.getElementById("planName").value = p.planName;
  document.getElementById("day").value = p.day;

  document.getElementById("breakfast").value = p.breakfast;
  document.getElementById("b_calories").value = p.b_calories;

  document.getElementById("lunch").value = p.lunch;
  document.getElementById("l_calories").value = p.l_calories;

  document.getElementById("dinner").value = p.dinner;
  document.getElementById("d_calories").value = p.d_calories;
}

function closeEdit(){
  document.getElementById("editModal").classList.remove("open");
}

function saveEdit(){
  const form = new FormData(document.getElementById("editForm"));

  fetch("manage-diet-edit.php", {
    method: "POST",
    body: form
  })
  .then(r=>r.text())
  .then(t=>{
    alert(t);
    location.reload();
  });
}

function deletePlan(table,id){
  if(!confirm("Are you sure you want to delete this plan?")) return;

  fetch("manage-diet-delet.php",{
    method:"POST",
    headers:{"Content-Type":"application/x-www-form-urlencoded"},
    body:"table="+table+"&id="+id
  })
  .then(r=>r.text())
  .then(t=>{
    alert(t);
    location.reload();
  });
}
</script>

</body>
</html>
