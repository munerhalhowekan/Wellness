<?php
session_start();


$current_user_id = $_SESSION['user_id'];

/* DB CONNECTION */
$conn = mysqli_connect("localhost", "root", "root", "wellness", 3306);
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

/* FETCH LEADERBOARD */
$sql = "SELECT UserID, firstName, lastName, points 
        FROM users
        ORDER BY points DESC";

$result = mysqli_query($conn, $sql);
if (!$result) { die("Query error: " . mysqli_error($conn)); }

$rank = 1;
$myRank = null;
$myPoints = null;

/* Scan result once to get user rank BEFORE printing */
$allRows = [];
while($row = mysqli_fetch_assoc($result)){
    $allRows[] = $row;

    if ($row['UserID'] == $current_user_id){
        $myRank = $rank;
        $myPoints = $row['points'];
    }
    $rank++;
}

/* Reset for table printing */
$rank = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Leaderboard — Wellness</title>

<style>
  :root{
    --bg:#1c1c1c; --layer:#222; --card:#2a2a2a; --text:#fff;
    --muted:#cfcfcf; --border:#3a3a3a; --accent:#ff6600;
    --accent-2:#ff8533; --shadow:0 0 18px rgba(255,102,0,.28);
  }
  body{
    margin:0;background:var(--bg);color:var(--text);
    font-family:"Poppins","Segoe UI",system-ui,Arial,sans-serif;
  }

  header{
    position:sticky;top:0;z-index:50;
    background:var(--layer);padding:12px 18px;
    display:flex;justify-content:space-between;align-items:center;
    border-bottom:1px solid var(--border);
  }

  .logo-img{width:120px;}

  main{max-width:800px;margin:40px auto;padding:0 16px;}

  .leader-card{
    background:var(--card);border-radius:16px;
    padding:24px;border:1px solid var(--border);
    box-shadow:var(--shadow);
  }
  .leader-card h1{text-align:center;color:var(--accent);}
  .leader-sub{text-align:center;color:var(--muted);margin-bottom:14px;}

  /* My Rank box (now ABOVE the table) */
  .my-box{
    background:#222;border:1px solid var(--border);border-radius:12px;
    padding:14px 16px;margin:0 0 20px 0;
    display:flex;justify-content:space-between;align-items:center;
  }
  .my-box strong{color:var(--accent);}
  .my-box small{color:var(--muted);}

  table{width:100%;border-collapse:collapse;}
  th,td{padding:12px;border-bottom:1px solid var(--border);}
  th{background:#252525;color:var(--accent);font-weight:700;}
  tr:last-child td{border-bottom:none;}

  .gold{color:gold;} .silver{color:#c0c0c0;} .bronze{color:#cd7f32;}
  .me-row{background:rgba(255,102,0,.15);}
</style>
</head>
<body>

<header>
  <div class="hamburger">☰</div>
  <div class="page">Leaderboard</div>
  <img src="wellness logo.png" class="logo-img">
</header>

<main>
  <div class="leader-card">
    <h1>🏆 Wellness Leaderboard</h1>
    <p class="leader-sub">See how you rank compared to others</p>

    <!-- ⭐ My Rank BOX (NOW ABOVE THE TABLE) ⭐ -->
    <?php if ($myRank !== null): ?>
      <div class="my-box">
        <span><strong>Your Rank:</strong> #<?php echo $myRank; ?></span>
        <span><strong>Your Points:</strong> <?php echo $myPoints; ?> pts</span>
        <small>Keep climbing the leaderboard 💪🔥</small>
      </div>
    <?php endif; ?>

    <!-- Leaderboard TABLE -->
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

</body>
</html>
