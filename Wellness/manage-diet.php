<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Diet Plans — Wellness Admin</title>
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

<!-- Drawer + overlay -->
<aside id="drawer" class="drawer">
  <h4>Admin Navigation</h4>
  <a href="admin-dashboard.html">🏠 Dashboard</a>
  <a href="manage-users.html">👥 Manage Users</a>
  <a href="manage-exercises.html">💪 Manage Exercises</a>
  <a href="manage-diet.html" class="active">🥗 Manage Diet Plans</a>
</aside>
<div id="overlay" class="overlay" onclick="toggleDrawer(false)"></div>

<!-- Header -->
<header>
  <div class="hamburger" onclick="toggleDrawer()">☰</div>
  <div class="page">Manage Diet Plans</div>
  <a href="admin-dashboard.html" class="logo-link">
    <img src="images/wellness logo.png" alt="Wellness Logo" class="logo-img">
  </a>
</header>

<!-- Main -->
<main>
  <div class="page-title">Manage Diet Plans</div>
  <p class="subtitle">A table listing all daily plans:</p>

  <table id="dietTable">
    <thead>
      <tr><th>Plan Name / Day</th><th>Total Calories</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <tr><td>Sunday</td><td>1200</td><td class="actions"><button onclick="openEdit(this)">Edit</button><button onclick="deletePlan(this)">Delete</button></td></tr>
      <tr><td>Monday</td><td>1250</td><td class="actions"><button onclick="openEdit(this)">Edit</button><button onclick="deletePlan(this)">Delete</button></td></tr>
      <tr><td>Tuesday</td><td>1200</td><td class="actions"><button onclick="openEdit(this)">Edit</button><button onclick="deletePlan(this)">Delete</button></td></tr>
      <tr><td>PCOS Day 1</td><td>1400</td><td class="actions"><button onclick="openEdit(this)">Edit</button><button onclick="deletePlan(this)">Delete</button></td></tr>
      <tr><td>Diabetes Day 1</td><td>1300</td><td class="actions"><button onclick="openEdit(this)">Edit</button><button onclick="deletePlan(this)">Delete</button></td></tr>
    </tbody>
  </table>
</main>

<!-- Edit Modal -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <h2>Edit Diet Plan</h2>

    <div class="form-group">
      <label>Plan Name / Day</label>
      <input type="text" id="planName">
    </div>

    <h3>--- Breakfast ---</h3>
    <div class="form-group">
      <label>Food Items</label>
      <textarea id="breakfastItems"></textarea>
      <label>Calories</label>
      <input type="number" id="breakfastCalories">
    </div>

    <h3>--- Lunch ---</h3>
    <div class="form-group">
      <label>Food Items</label>
      <textarea id="lunchItems"></textarea>
      <label>Calories</label>
      <input type="number" id="lunchCalories">
    </div>

    <h3>--- Dinner ---</h3>
    <div class="form-group">
      <label>Food Items</label>
      <textarea id="dinnerItems"></textarea>
      <label>Calories</label>
      <input type="number" id="dinnerCalories">
    </div>

    <div class="modal-buttons">
      <button id="cancelBtn" onclick="closeEdit()">Cancel</button>
      <button id="saveBtn" onclick="saveEdit()">Save</button>
    </div>
  </div>
</div>

<script>
function toggleDrawer(force){
  const d=document.getElementById('drawer'),o=document.getElementById('overlay');
  const open=(typeof force==='boolean')?force:!d.classList.contains('open');
  d.classList.toggle('open',open);o.classList.toggle('show',open);
}

// Mock data for editing
let currentRow = null;

function openEdit(btn){
  currentRow = btn.closest("tr");
  document.getElementById("planName").value = currentRow.children[0].textContent;
  document.getElementById("breakfastItems").value = "1 egg\n2 coffee";
  document.getElementById("breakfastCalories").value = 160;
  document.getElementById("lunchItems").value = "Grilled chicken salad";
  document.getElementById("lunchCalories").value = 450;
  document.getElementById("dinnerItems").value = "Baked salmon\nSteamed vegetables";
  document.getElementById("dinnerCalories").value = 590;
  document.getElementById("editModal").classList.add("open");
}

function closeEdit(){
  document.getElementById("editModal").classList.remove("open");
}

function saveEdit(){
  if(currentRow){
    currentRow.children[0].textContent = document.getElementById("planName").value;
    const totalCalories = 
      (+document.getElementById("breakfastCalories").value||0) +
      (+document.getElementById("lunchCalories").value||0) +
      (+document.getElementById("dinnerCalories").value||0);
    currentRow.children[1].textContent = totalCalories;
  }
  closeEdit();
}

function deletePlan(btn){
  if(confirm("Are you sure you want to delete this plan?"))
    btn.closest("tr").remove();
}
</script>
</body>
</html>
