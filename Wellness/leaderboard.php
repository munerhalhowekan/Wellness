<?php
session_start();

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    die("❌ You must log in first.");
}

$current_user_id = $_SESSION['user_id'];

include 'db-connection.php';

// Fetch leaderboard sorted by points
$sql = "SELECT UserID, firstName, lastName, points 
        FROM users
        ORDER BY points DESC";

$result = mysqli_query($conn, $sql);
if (!$result) { die("Query error: " . mysqli_error($conn)); }

$rank = 1;
$myRank = null;
$myPoints = null;

$allRows = [];
while($row = mysqli_fetch_assoc($result)){
    $allRows[] = $row;

    if ($row['UserID'] == $current_user_id){
        $myRank = $rank;
        $myPoints = $row['points'];
    }
    $rank++;
}

$rank = 1; // Reset for printing
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Leaderboard — Wellness</title>

<style>
/* Theme Variables */
:root{
  --bg:#1c1c1c; 
  --layer:#222; 
  --card:#2a2a2a; 
  --text:#fff;
  --muted:#cfcfcf; 
  --border:#3a3a3a; 
  --accent:#ff6600;
  --accent2:#ff8533; 
  --shadow:0 0 18px rgba(255,102,0,.28);
}

/* Reset + Fix Drawer Peeking */
body{
  margin:0 !important;
  padding:0 !important;
  overflow-x:hidden !important;
  background:var(--bg);
  color:var(--text);
  font-family:"Poppins","Segoe UI",system-ui,Arial,sans-serif;
}

/* Header */
header{
  position:sticky;
  top:0;
  z-index:60;
  background:var(--layer);
  padding:12px 18px;
  border-bottom:1px solid var(--border);
  display:flex;
  justify-content:space-between;
  align-items:center;
}
.page{font-weight:800;}
.logo-img{width:120px;height:auto;}
.hamburger{cursor:pointer;font-size:24px;color:var(--text);}

/* Drawer (Same as Dashboard) */
.drawer{
  position:fixed;
  top:0;
  left:-300px;      /* fully hidden */
  width:260px;
  height:100vh;
  background:var(--card);
  padding:18px;
  border-right:1px solid var(--border);
  box-shadow:16px 0 30px rgba(0,0,0,.45);
  z-index:80;
  transition:left .28s ease;
}
.drawer.open{
  left:0;
}
.drawer h4{
  color:var(--accent);
  margin-bottom:12px;
}
.drawer a{
  display:block;
  padding:10px;
  color:var(--text);
  text-decoration:none;
  border-radius:10px;
  margin:6px 0;
}
.drawer a:hover{
  background:rgba(255,102,0,.15);
}
/* LOGOUT BUTTON (same as dashboard) */
.logout{
  background:transparent;
  border:2px solid var(--accent);
  color:var(--accent);
  border-radius:10px;
  padding:6px 14px;
  font-weight:600;
  cursor:pointer;
  transition:.25s;
  margin-top:20px;
}
.logout:hover{
  background:var(--accent);
  color:#fff;
}

/* Overlay */
.overlay{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.5);
  display:none;
  z-index:70;
}
.overlay.show{
  display:block;
}

/* Main Container */
main{
  max-width:900px;
  margin:40px auto;
  padding:0 16px;
}

/* Leaderboard Card */
.leader-card{
  background:var(--card);
  border-radius:16px;
  padding:28px;
  border:1px solid var(--border);
  box-shadow:var(--shadow);
}
.leader-card h1{
  text-align:center;
  color:var(--accent);
  margin-top:0;
}
.leader-sub{
  text-align:center;
  color:var(--muted);
  margin-bottom:20px;
}

/* Rank Bar */
.my-box{
  background:#222;
  border:1px solid var(--border);
  border-radius:12px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:14px 16px;
  margin-bottom:20px;
}
.my-box strong{
  color:var(--accent);
}
.my-box small{
  color:var(--muted);
}

/* Table */
table{
  width:100%;
  border-collapse:collapse;
}
th, td{
  padding:14px 10px;
  border-bottom:1px solid var(--border);
}
th{
  background:#252525;
  color:var(--accent);
}
.me-row{
  background:rgba(255,102,0,.15);
}

/* Medals */
.gold{color:gold;}
.silver{color:#c0c0c0;}
.bronze{color:#cd7f32;}
</style>

</head>
<body>

<!-- Drawer -->
<aside id="drawer" class="drawer">
  <h4>Navigation</h4>
  <a href="dashboard.php">🏠 Dashboard</a>
  <a href="leaderboard.php" class="active">🏆 Leaderboard</a>
  <a href="profile.php">👤 Profile</a>
  <form action="logout.php" method="post">
    <button class="logout">Logout</button>
  </form>
</aside>

<div id="overlay" class="overlay" onclick="toggleDrawer(false)"></div>

<!-- Header -->
<header>
  <div class="hamburger" onclick="toggleDrawer()">☰</div>
  <div class="page">Leaderboard</div>
  <img src="wellness logo.png" class="logo-img">
</header>

<!-- Main -->
<main>
  <div class="leader-card">
    <h1>🏆 Wellness Leaderboard</h1>
    <p class="leader-sub">See how you rank compared to others</p>

    <?php if ($myRank !== null): ?>
      <div class="my-box">
        <span><strong>Your Rank:</strong> #<?php echo $myRank; ?></span>
        <span><strong>Your Points:</strong> <?php echo $myPoints; ?> pts</span>
        <small>Keep climbing 💪🔥</small>
      </div>
    <?php endif; ?>

    <table>
      <thead>
        <tr>
          <th>Rank</th>
          <th>Name</th>
          <th style="text-align:right;">Points</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($allRows as $row): ?>
        <?php
          $class = "";
          $medal = "#".$rank;

          if ($rank == 1){ $class="gold"; $medal="🥇"; }
          elseif ($rank == 2){ $class="silver"; $medal="🥈"; }
          elseif ($rank == 3){ $class="bronze"; $medal="🥉"; }

          $isMe = ($current_user_id == $row["UserID"]);
        ?>
        <tr class="<?php echo $isMe ? 'me-row' : ''; ?>">
          <td class="<?php echo $class; ?>"><?php echo $medal; ?></td>
          <td><?php echo $row["firstName"] . " " . $row["lastName"]; ?></td>
          <td style="text-align:right;"><?php echo $row["points"]; ?> pts</td>
        </tr>
      <?php $rank++; endforeach; ?>
      </tbody>
    </table>

  </div>
</main>

<script>
function toggleDrawer(force){
  const drawer = document.getElementById("drawer");
  const overlay = document.getElementById("overlay");
  const open = (typeof force === "boolean") ? force : !drawer.classList.contains("open");

  drawer.classList.toggle("open", open);
  overlay.classList.toggle("show", open);
}

document.querySelectorAll('#drawer a').forEach(a => 
  a.addEventListener('click', () => toggleDrawer(false))
);
</script>

</body>
</html>
