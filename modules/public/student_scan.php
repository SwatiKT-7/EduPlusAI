<?php
// Student scanner page: scans QR (via webcam or manual token), gets geolocation,
// and posts to modules/attendance/scan.php.
// Requires you to be logged in with localStorage {jwt, uid, device_id}.
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>EduPulse • Student Scan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Optional QR scanner via CDN (html5-qrcode). If blocked, manual token works. -->
  <script src="https://unpkg.com/html5-qrcode" defer></script>
  <style>
    body{background:#0b1220;color:#e5e7eb}
    .card{background:#111827;border-radius:1rem;padding:1.25rem;box-shadow:0 10px 25px rgba(0,0,0,.35)}
    input{color:#111827}
  </style>
</head>
<body class="min-h-screen p-4">
  <div class="max-w-3xl mx-auto grid md:grid-cols-2 gap-6">
    <div class="card">
      <h1 class="text-xl font-semibold mb-2">Scan Attendance</h1>
      <div class="space-y-3">
        <div>
          <label class="text-sm">Class ID</label>
          <input class="w-full mt-1 px-3 py-2 rounded-lg" id="class_id" type="number" value="1">
        </div>
        <div>
          <label class="text-sm">Token (auto-fills from QR)</label>
          <input class="w-full mt-1 px-3 py-2 rounded-lg" id="token" placeholder="Scan QR or paste token">
        </div>
        <div class="flex items-center gap-2">
          <button id="scanBtn" class="px-3 py-2 bg-indigo-600 rounded-lg hover:bg-indigo-500">Start Camera Scan</button>
          <button id="stopBtn" class="px-3 py-2 bg-slate-700 rounded-lg hover:bg-slate-600">Stop</button>
        </div>
        <div id="reader" class="bg-black/20 rounded-lg aspect-video flex items-center justify-center text-sm opacity-80">
          Camera preview
        </div>
        <div>
          <button id="markBtn" class="w-full py-2 bg-emerald-600 rounded-lg hover:bg-emerald-500 mt-2">Mark Attendance</button>
          <div id="msg" class="text-sm mt-2 opacity-80"></div>
        </div>
      </div>
    </div>

    <div class="card">
      <h2 class="text-lg mb-2">Status</h2>
      <div class="text-sm">User: <span id="uName">-</span> (<span id="uRole">-</span>)</div>
      <div class="text-sm mt-1">User ID: <span id="uId">-</span></div>
      <div class="text-sm mt-1">Device: <span id="devId">-</span></div>
      <div class="text-sm mt-2">Location: <span id="loc">waiting…</span></div>
      <div class="text-xs opacity-60 mt-2">Tip: If QR scanning fails (blocked camera), ask faculty for the token and paste it.</div>
      <hr class="my-3 border-slate-600">
      <div class="text-sm opacity-80">
        <strong>How it works:</strong>
        The server validates the rotating token (20s window), your geofence, and your bound device.
      </div>
    </div>
  </div>

<script>
// Read session
const nameEl = document.getElementById('uName');
const roleEl = document.getElementById('uRole');
const idEl   = document.getElementById('uId');
const devEl  = document.getElementById('devId');
const locEl  = document.getElementById('loc');
const msgEl  = document.getElementById('msg');

const jwt = localStorage.getItem('jwt') || '';
const uid = localStorage.getItem('uid') || '';
const role= localStorage.getItem('role') || '';
const name= localStorage.getItem('name') || '';
const deviceId = localStorage.getItem('device_id') || 'dev-unknown';

nameEl.textContent = name || '(unknown)';
roleEl.textContent = role || '-';
idEl.textContent = uid || '-';
devEl.textContent = deviceId;

// Geo
let myLat = null, myLng = null;
if (navigator.geolocation) {
  navigator.geolocation.getCurrentPosition(
    (pos)=>{ myLat = pos.coords.latitude; myLng = pos.coords.longitude; locEl.textContent = myLat.toFixed(5)+', '+myLng.toFixed(5); },
    (err)=>{ locEl.textContent = 'Location blocked: ' + err.message; },
    { enableHighAccuracy:true, timeout:8000, maximumAge:0 }
  );
} else {
  locEl.textContent = 'Geolocation not supported';
}

// QR scanner (optional)
let html5Qrcode = null;
const readerDivId = "reader";
document.getElementById('scanBtn').onclick = async ()=>{
  if (!window.Html5Qrcode) { msgEl.textContent = 'QR library not loaded; paste token manually.'; return; }
  if (html5Qrcode) return;
  html5Qrcode = new Html5Qrcode(readerDivId);
  try{
    await html5Qrcode.start(
      { facingMode: "environment" },
      { fps: 10, qrbox: 250 },
      (decodedText)=>{ document.getElementById('token').value = decodedText; }
    );
    msgEl.textContent = 'Scanner running…';
  }catch(e){
    msgEl.textContent = 'Cannot start camera: ' + e;
  }
};
document.getElementById('stopBtn').onclick = async ()=>{
  if (html5Qrcode) {
    try { await html5Qrcode.stop(); html5Qrcode.clear(); } catch(e){}
    html5Qrcode = null;
    msgEl.textContent = 'Scanner stopped.';
  }
};

// Mark attendance
document.getElementById('markBtn').onclick = async ()=>{
  const classId = parseInt(document.getElementById('class_id').value || '0', 10);
  const token   = document.getElementById('token').value.trim();
  if (!uid) { msgEl.textContent='Not logged in. Open login.php'; msgEl.className='text-sm mt-2 text-red-400'; return; }
  if (!classId || !token) { msgEl.textContent='Class ID and token required.'; msgEl.className='text-sm mt-2 text-red-400'; return; }
  if (myLat===null || myLng===null) { msgEl.textContent='Waiting for location…'; msgEl.className='text-sm mt-2 text-yellow-400'; return; }

  const fd = new FormData();
  fd.append('class_id', classId);
  fd.append('user_id', uid);
  fd.append('token', token);
  fd.append('lat', String(myLat));
  fd.append('lng', String(myLng));
  fd.append('device_id', deviceId);

  const r = await fetch('../modules/attendance/scan.php', { method:'POST', body: fd });
  let j; try { j = await r.json(); } catch(e){ j = {ok:false, error:'Bad response'}; }
  if(!j.ok){
    msgEl.textContent = '❌ ' + (j.error || 'Failed') + (j.distance_m!==undefined ? ` (distance ${j.distance_m} m)` : '');
    msgEl.className = 'text-sm mt-2 text-red-400';
  } else {
    msgEl.textContent = '✅ Marked present! Distance ' + (j.distance_m!==undefined ? j.distance_m : '?') + ' m';
    msgEl.className = 'text-sm mt-2 text-green-400';
  }
};
</script>
</body>
</html>
