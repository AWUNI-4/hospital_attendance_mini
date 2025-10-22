<?php
require 'connect.php';
session_start();
if (!isset($_SESSION['staff_id'])) { header('Location: login.php'); exit; }
$staff_id = $_SESSION['staff_id'];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $lat = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
  $lon = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;

  if ($lat === null || $lon === null) {
    $message = 'Location required to mark attendance.';
  } else {
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT id, check_out FROM attendance WHERE staff_id = ? AND date = ? LIMIT 1");
    $stmt->bind_param('ss', $staff_id, $today);
    $stmt->execute();
    $res = $stmt->get_result();
    $now = date('Y-m-d H:i:s');
    if ($row = $res->fetch_assoc()) {
      if (is_null($row['check_out'])) {
        $upd = $conn->prepare("UPDATE attendance SET check_out = ?, latitude = ?, longitude = ? WHERE id = ?");
        $upd->bind_param('dddi', $now, $lat, $lon, $row['id']);
        $upd->execute();
        $message = 'Checked out at ' . $now;
      } else {
        $message = 'You have already checked out today.';
      }
    } else {
      $ins = $conn->prepare("INSERT INTO attendance (staff_id, check_in, date, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
      $ins->bind_param('sssdd', $staff_id, $now, $today, $lat, $lon);
      if ($ins->execute()) {
        $message = 'Checked in at ' . $now . ' — coordinates saved.';
      } else {
        $message = 'DB error: ' . $conn->error;
      }
    }
  }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Mark Attendance</title><link rel="stylesheet" href="styles.css"></head>
<body>
  <div class="card">
    <h2>Welcome, <?=htmlspecialchars($_SESSION['firstname'])?></h2>
    <?php if ($message): ?><p class="msg"><?=htmlspecialchars($message)?></p><?php endif; ?>

    <p>Use the button below to get your live location. A mini-map will show your position — confirm and submit to mark attendance.</p>

    <button id="getLocationBtn">Get My Location</button>
    <div id="locationInfo" style="margin-top:12px;"></div>

    <div id="miniMap" style="width:100%;height:250px;margin-top:12px;display:none;"></div>

    <form id="attForm" method="post" style="display:none;margin-top:8px;">
      <input type="hidden" name="latitude" id="latitude">
      <input type="hidden" name="longitude" id="longitude">
      <button type="submit">Mark Attendance (Confirm)</button>
    </form>

    <p style="margin-top:12px;"><a href="logout.php">Logout</a></p>
  </div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const getLocationBtn = document.getElementById('getLocationBtn');
const locationInfo = document.getElementById('locationInfo');
const miniMapDiv = document.getElementById('miniMap');
let miniMap = null;
let marker = null;

function showMap(lat, lon) {
  miniMapDiv.style.display = 'block';
  if (!miniMap) {
    miniMap = L.map('miniMap').setView([lat, lon], 17);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom:19}).addTo(miniMap);
    marker = L.marker([lat, lon]).addTo(miniMap).bindPopup('You are here').openPopup();
  } else {
    miniMap.setView([lat, lon], 17);
    if (marker) { miniMap.removeLayer(marker); }
    marker = L.marker([lat, lon]).addTo(miniMap).bindPopup('You are here').openPopup();
  }
}

getLocationBtn.addEventListener('click', () => {
  locationInfo.textContent = 'Requesting location…';
  if (!navigator.geolocation) {
    locationInfo.textContent = 'Geolocation not supported by your browser.';
    return;
  }
  navigator.geolocation.getCurrentPosition((pos) => {
    const lat = pos.coords.latitude;
    const lon = pos.coords.longitude;
    locationInfo.innerHTML = 'Lat: ' + lat.toFixed(6) + ', Lon: ' + lon.toFixed(6) + ' (accuracy: ' + pos.coords.accuracy + ' m)';
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lon;
    showMap(lat, lon);
    document.getElementById('attForm').style.display = 'block';
  }, (err) => {
    locationInfo.textContent = 'Could not get location: ' + err.message;
  }, {enableHighAccuracy:true, timeout:15000, maximumAge:0});
});
</script>
</body>
</html>
