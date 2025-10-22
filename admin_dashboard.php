<?php
require 'connect.php';
session_start();
if (!isset($_SESSION['is_admin'])) { header('Location: admin_login.php'); exit; }

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$where = '';
$params = [];
$types = '';
if ($from && $to) {
  $where = 'WHERE a.date BETWEEN ? AND ?';
  $types = 'ss';
  $params = [$from, $to];
} elseif ($from) {
  $where = 'WHERE a.date >= ?'; $types = 's'; $params = [$from];
} elseif ($to) {
  $where = 'WHERE a.date <= ?'; $types = 's'; $params = [$to];
}
$sql = "SELECT a.*, s.firstname, s.lastname, s.department FROM attendance a JOIN staff s ON a.staff_id = s.staff_id $where ORDER BY a.date DESC, s.lastname";
$stmt = $conn->prepare($sql);
if ($types) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>#mapModal{position:fixed;top:6%;left:6%;width:88%;height:76%;background:#fff;border:1px solid #ccc;z-index:1000;display:none;padding:8px}#map{width:100%;height:100%}</style>
</head>
<style>
  body {
    background-image: url('nnn.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
  }

  /* style the form card and let an overlay image sit on top of it */
  .card {
    position: relative;
    max-width: 520px;
    width: 100%;
    padding: 2rem;
    border-radius: .5rem;
    overflow: hidden;
    background: rgba(255,255,255,0.85); /* translucent so background shows through */
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
  }

  /* overlay image on the form card; adjust opacity/mix-blend-mode as needed */
  .card::before {
    content: "";
    position: absolute;
    inset: 0;
    background-image: url('aaa.jpg');
    background-size: cover;
    background-position: center;
    opacity: 0.35; /* increase for stronger overlay */
    mix-blend-mode: overlay;
    pointer-events: none;
  }

  /* ensure form content sits above the overlay */
  .card * {
    position: relative;
    z-index: 1;
  }
</style>
<body>
<div class="card wide">
  <h2>Attendance Records — Admin</h2>
  <p>Welcome, <?=htmlspecialchars($_SESSION['admin_name'])?> | <a href="logout.php">Logout</a></p>

  <form method="get" style="display:flex;gap:8px;align-items:center;">
    <label>From</label><input type="date" name="from" value="<?=htmlspecialchars($from)?>">
    <label>To</label><input type="date" name="to" value="<?=htmlspecialchars($to)?>">
    <button type="submit">Filter</button>
  </form>

  <table class="records" style="margin-top:12px;">
    <thead>
      <tr><th>Date</th><th>Staff ID</th><th>Name</th><th>Dept</th><th>Check-in</th><th>Check-out</th><th>Location</th></tr>
    </thead>
    <tbody>
      <?php while ($row = $res->fetch_assoc()): ?>
      <tr>
        <td><?=htmlspecialchars($row['date'])?></td>
        <td><?=htmlspecialchars($row['staff_id'])?></td>
        <td><?=htmlspecialchars($row['firstname'].' '.$row['lastname'])?></td>
        <td><?=htmlspecialchars($row['department'])?></td>
        <td><?=htmlspecialchars($row['check_in'])?></td>
        <td><?=htmlspecialchars($row['check_out'])?></td>
        <td>
          <?php if ($row['latitude'] && $row['longitude']): ?>
            <button class="viewMapBtn" data-lat="<?=htmlspecialchars($row['latitude'])?>" data-lon="<?=htmlspecialchars($row['longitude'])?>" data-name="<?=htmlspecialchars($row['firstname'].' '.$row['lastname'])?>">View Map</button>
          <?php else: ?>
            No location
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<div id="mapModal"><button id="closeMap" style="float:right">Close</button><div id="map"></div></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let mapModal = document.getElementById('mapModal');
let mapDiv = document.getElementById('map');
let closeBtn = document.getElementById('closeMap');
let map = null;
let currentMarker = null;
function openMap(lat, lon, name) {
  mapModal.style.display = 'block';
  setTimeout(() => {
    if (!map) {
      map = L.map('map').setView([lat, lon], 17);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom:19}).addTo(map);
      currentMarker = L.marker([lat, lon]).addTo(map).bindPopup(name).openPopup();
    } else {
      map.setView([lat, lon], 17);
      if (currentMarker) { map.removeLayer(currentMarker); }
      currentMarker = L.marker([lat, lon]).addTo(map).bindPopup(name).openPopup();
    }
  }, 100);
}
closeBtn.addEventListener('click', () => { mapModal.style.display='none'; if (map) { map.remove(); map = null; currentMarker=null;} });

document.querySelectorAll('.viewMapBtn').forEach(btn => {
  btn.addEventListener('click', () => {
    const lat = parseFloat(btn.dataset.lat);
    const lon = parseFloat(btn.dataset.lon);
    const name = btn.dataset.name;
    openMap(lat, lon, name);
  });
});
</script>
</body>
</html>
