<?php
session_start();

 if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
   die("Access denied.");
 }

$conn = mysqli_connect("localhost", "root", "root", "wellness", 3306);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateUser'])) {
    $id = intval($_POST['UserID']);
    $email = $_POST['email'];
    $level = $_POST['fitness_level'];
    $illness = ($_POST['health_condition'] === '-' ? NULL : $_POST['health_condition']);
    $points = intval($_POST['points']);

    $sql = "UPDATE users 
            SET email='$email',
                fitness_level='$level',
                health_condition=" . ($illness ? "'$illness'" : "NULL") . ",
                points=$points
            WHERE UserID=$id";

    mysqli_query($conn, $sql);
    header("Location: manage-users.php");
    exit;
}


if (isset($_GET['delete'])) {
    $deleteID = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE UserID=$deleteID");
    header("Location: manage-users.php");
    exit;
}


$sql = "SELECT UserID, firstName, lastName, email, fitness_level, health_condition, points 
        FROM users 
        WHERE role='user'";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Manage Users — Wellness Admin</title>

<style>
:root {
  --bg:#1c1c1c; --layer:#222; --card:#2a2a2a; --text:#fff; --muted:#cfcfcf;
  --border:#3a3a3a; --accent:#ff6600; --accent2:#ff8533;
  --shadow:0 0 18px rgba(255,102,0,.28);
}
*{box-sizing:border-box}
body{
  margin:0;background:var(--bg);color:var(--text);
  font-family:"Poppins","Segoe UI",system-ui,sans-serif;
}


header{
  position:sticky;top:0;z-index:60;background:var(--layer);
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
  padding:12px 18px;box-shadow:0 4px 12px rgba(0,0,0,.25);
}
.hamburger{cursor:pointer;font-size:24px;user-select:none}
.page{font-weight:800}
.logo-img{width:120px;height:auto;object-fit:contain}


.drawer{
  position:fixed;left:-260px;top:0;height:100vh;width:260px;background:var(--card);
  border-right:1px solid var(--border);box-shadow:16px 0 30px rgba(0,0,0,.45);
  padding:18px;z-index:80;transition:left .28s ease;
}
.drawer.open{left:0}
.drawer a{display:block;color:var(--text);padding:10px;border-radius:10px;margin:6px 0;text-decoration:none}
.drawer a.active{background:var(--accent);color:#fff}
.drawer a:hover{background:rgba(255,102,0,.15)}
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);display:none;z-index:70}
.overlay.show{display:block}


main{padding:30px;max-width:1200px;margin:auto}
.page-title{font-size:1.6rem;font-weight:700;color:var(--accent)}
.subtitle{color:var(--muted);margin-bottom:20px}


table{
  width:100%;border-collapse:collapse;background:var(--card);
  border-radius:10px;overflow:hidden;box-shadow:var(--shadow);
}
th, td{
  padding:14px 12px;border-bottom:1px solid var(--border);text-align:left;
}
th{color:var(--accent);font-weight:700;border-bottom:2px solid var(--accent2)}
tr:hover{background:#2f2f2f}

.actions button{
  background:transparent;border:1.5px solid var(--accent);
  color:var(--accent);border-radius:8px;padding:4px 10px;margin-right:6px;
  cursor:pointer;font-weight:600;transition:.2s;
}
.actions button:hover{background:var(--accent);color:#fff}


.modal{
  position:fixed;inset:0;background:rgba(0,0,0,.7);
  display:none;align-items:center;justify-content:center;z-index:90;
}
.modal.open{display:flex}
.modal-content{
  background:var(--card);border:1px solid var(--border);
  border-radius:14px;box-shadow:var(--shadow);
  padding:24px;width:min(420px,90%);
}
.modal-content h2{margin-top:0;color:var(--accent);text-align:center}
.form-group{margin-bottom:16px}
label{display:block;font-weight:600;margin-bottom:6px}
input, select{
  width:100%;padding:8px;border:1px solid var(--border);
  border-radius:8px;background:#333;color:#fff;
}
.modal-buttons{display:flex;justify-content:space-between;margin-top:20px}
.modal-buttons button{
  flex:1;margin:0 6px;padding:8px 10px;border:none;border-radius:8px;
  font-weight:700;cursor:pointer;
}
#cancelBtn{background:#444;color:#fff}
#doneBtn{background:var(--accent);color:#fff}
</style>
</head>
<body>


<aside id="drawer" class="drawer">
  <h4>Admin Navigation</h4>
  <a href="admin-dashboard.php">🏠 Dashboard</a>
  <a class="active" href="manage-users.php">👥 Manage Users</a>
  <a href="manage-exercises.php">💪 Manage Exercises</a>
  <a href="manage-diet.php">🥗 Manage Diet Plans</a>
</aside>
<div id="overlay" class="overlay" onclick="toggleDrawer(false)"></div>


<header>
  <div class="hamburger" onclick="toggleDrawer()">☰</div>
  <div class="page">Manage Users</div>
  <img src="wellness logo.png" class="logo-img">
</header>

<main>
  <div class="page-title">Manage Users</div>
  <p class="subtitle">View and manage all user accounts.</p>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Fitness Level</th>
        <th>Illness</th>
        <th>Points</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>

<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
  <td><?= $row['UserID'] ?></td>
  <td><?= htmlspecialchars($row['firstName'] . " " . $row['lastName']) ?></td>
  <td><?= htmlspecialchars($row['email']) ?></td>
  <td><?= $row['fitness_level'] ?></td>
  <td><?= $row['health_condition'] ? $row['health_condition'] : "-" ?></td>
  <td><?= $row['points'] ?></td>

  <td class="actions">
    <button onclick="openEdit(
      <?= $row['UserID'] ?>,
      '<?= $row['email'] ?>',
      '<?= $row['fitness_level'] ?>',
      '<?= $row['health_condition'] ? $row['health_condition'] : '-' ?>',
      <?= $row['points'] ?>
    )">Edit</button>

    <button onclick="deleteUser(<?= $row['UserID'] ?>)">Delete</button>
  </td>
</tr>
<?php endwhile; ?>

    </tbody>
  </table>
</main>


<div id="editModal" class="modal">
  <div class="modal-content">
    <h2>Edit User</h2>

    <form method="POST">
      <input type="hidden" name="updateUser" value="1">

      <div class="form-group">
        <label>User ID</label>
        <input type="text" name="UserID" id="UserID" readonly>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" id="userEmail">
      </div>

      <div class="form-group">
        <label>Fitness Level</label>
        <select name="fitness_level" id="userLevel">
          <option value="beginner">Beginner</option>
          <option value="intermediate">Intermediate</option>
          <option value="advanced">Advanced</option>
        </select>
      </div>

      <div class="form-group">
        <label>Illness</label>
        <select name="health_condition" id="userIllnessType">
          <option>-</option>
          <option>PCOS</option>
          <option>Gluten Intolerance</option>
          <option>Insulin Resistance</option>
        </select>
      </div>

      <div class="form-group">
        <label>Points</label>
        <input type="number" name="points" id="userPoints" min="0">
      </div>

      <div class="modal-buttons">
        <button type="button" id="cancelBtn" onclick="closeEdit()">Cancel</button>
        <button id="doneBtn" type="submit">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleDrawer(force){
  const d=document.getElementById('drawer'),o=document.getElementById('overlay');
  const open=(typeof force==='boolean')?force:!d.classList.contains('open');
  d.classList.toggle('open',open);o.classList.toggle('show',open);
}

function openEdit(id,email,level,illness,points){
  document.getElementById("UserID").value=id;
  document.getElementById("userEmail").value=email;
  document.getElementById("userLevel").value=level;
  document.getElementById("userIllnessType").value=illness;
  document.getElementById("userPoints").value=points;
  document.getElementById("editModal").classList.add("open");
}

function closeEdit(){
  document.getElementById("editModal").classList.remove("open");
}

function deleteUser(id){
  if(confirm("Are you sure you want to delete this user?")){
    window.location.href = "manage-users.php?delete="+id;
  }
}
</script>

</body>
</html>

