<?php // Simple form to configure mode, geofence, wifi, beacon, meeting URL ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>EduPulse • Class Mode</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>body{background:#0b1220;color:#e5e7eb}.card{background:#111827;border-radius:1rem;padding:1.25rem;box-shadow:0 10px 25px rgba(0,0,0,.35)}</style>
</head>
<body class="min-h-screen p-4">
  <div class="max-w-4xl mx-auto card">
    <h1 class="text-2xl font-semibold mb-3">Class Mode & Integrity</h1>
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm">Class ID</label>
        <div class="flex gap-2">
          <input id="class_id" type="number" class="w-full mt-1 px-3 py-2 rounded-lg text-black" value="1">
          <button id="loadBtn" class="px-3 py-2 bg-slate-700 rounded-lg hover:bg-slate-600 mt-1">Load</button>
        </div>
      </div>
      <div>
        <label class="text-sm">Mode</label>
        <select id="mode" class="w-full mt-1 px-3 py-2 rounded-lg text-black">
          <option value="offline">offline</option>
          <option value="online">online</option>
          <option value="hybrid">hybrid</option>
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="text-sm">Meeting URL (for online/hybrid)</label>
        <input id="meeting_url" class="w-full mt-1 px-3 py-2 rounded-lg text-black" placeholder="https://...">
      </div>
      <div>
        <label class="text-sm">Geo Lat</label>
        <input id="geo_lat" class="w-full mt-1 px-3 py-2 rounded-lg text-black" placeholder="28.6139">
      </div>
      <div>
        <label class="text-sm">Geo Lng</label>
        <input id="geo_lng" class="w-full mt-1 px-3 py-2 rounded-lg text-black" placeholder="77.2090">
      </div>
      <div>
        <label class="text-sm">Radius (m)</label>
        <input id="radius_m" type="number" class="w-full mt-1 px-3 py-2 rounded-lg text-black" value="80">
      </div>
      <div>
        <label class="text-sm">Wi-Fi BSSID (optional)</label>
        <input id="wifi_bssid" class="w-full mt-1 px-3 py-2 rounded-lg text-black" placeholder="e.g., 9c:xx:xx:xx:xx:aa">
      </div>
      <div>
        <label class="text-sm">BLE Beacon ID (optional)</label>
        <input id="ble_beacon_id" class="w-full mt-1 px-3 py-2 rounded-lg text-black" placeholder="beacon-uuid">
      </div>
    </div>

    <div class="flex gap-2 mt-4">
      <button id="saveBtn" class="px-4 py-2 bg-emerald-600 rounded-lg hover:bg-emerald-500">Save</button>
      <button id="startBtn" class="px-4 py-2 bg-indigo-600 rounded-lg hover:bg-indigo-500">Start / Go Live</button>
      <button id="closeBtn" class="px-4 py-2 bg-rose-700 rounded-lg hover:bg-rose-600">Close Class</button>
      <a id="facultyLink" href="./faculty_session.php?class_id=1" class="px-4 py-2 bg-slate-700 rounded-lg hover:bg-slate-600">Open Faculty View</a>
    </div>
    <div id="msg" class="text-sm opacity-80 mt-3">—</div>

    <hr class="my-4 border-slate-700">
    <div id="info" class="text-sm opacity-90"></div>
  </div>

<script>
const cEl = id=>document.getElementById(id);
const msg = cEl('msg'), info = cEl('info');

async function load(){
  const cid = parseInt(cEl('class_id').value||'0',10);
  if(!cid){ alert('Enter class id'); return; }
  const r = await fetch(`../modules/classes/get.php?class_id=${cid}`);
  let j; try { j = await r.json(); } catch(e){ j={ok:false,error:'Bad response'}; }
  if(!j.ok){ msg.textContent = 'Error: '+(j.error||'failed'); return; }
  const x = j.class;
  cEl('mode').value = x.mode || 'offline';
  cEl('meeting_url').value = x.meeting_url || '';
  cEl('geo_lat').value = x.geo_lat ?? '';
  cEl('geo_lng').value = x.geo_lng ?? '';
  cEl('radius_m').value = x.radius_m ?? 80;
  cEl('wifi_bssid').value = x.wifi_bssid || '';
  cEl('ble_beacon_id').value = x.ble_beacon_id || '';
  cEl('facultyLink').href = `./faculty_session.php?class_id=${cid}`;
  info.innerHTML = `
    <div>Status: <b>${x.status}</b> • Mode: <b>${x.mode}</b></div>
    <div>Started: ${x.started_at||'-'} • Closed: ${x.closed_at||'-'}</div>
  `;
  msg.textContent = 'Loaded ✔';
}
cEl('loadBtn').onclick = load;

cEl('saveBtn').onclick = async ()=>{
  const fd = new FormData();
  fd.append('class_id', cEl('class_id').value);
  fd.append('mode', cEl('mode').value);
  fd.append('meeting_url', cEl('meeting_url').value);
  if(cEl('geo_lat').value && cEl('geo_lng').value){ fd.append('geo_lat', cEl('geo_lat').value); fd.append('geo_lng', cEl('geo_lng').value); }
  if(cEl('radius_m').value) fd.append('radius_m', cEl('radius_m').value);
  if(cEl('wifi_bssid').value) fd.append('wifi_bssid', cEl('wifi_bssid').value);
  if(cEl('ble_beacon_id').value) fd.append('ble_beacon_id', cEl('ble_beacon_id').value);
  const r = await fetch('../modules/classes/update_mode.php',{method:'POST',body:fd});
  let j; try { j=await r.json(); } catch(e){ j={ok:false,error:'Bad response'}; }
  msg.textContent = j.ok ? 'Saved ✔' : ('Error: '+(j.error||'failed'));
};

cEl('startBtn').onclick = async ()=>{
  const fd = new FormData(); fd.append('class_id', cEl('class_id').value);
  const r = await fetch('../modules/attendance/start_class.php',{method:'POST',body:fd});
  let j; try { j=await r.json(); } catch(e){ j={ok:false,error:'Bad response'}; }
  msg.textContent = j.ok ? 'Class live ✔' : ('Error: '+(j.error||'failed'));
  if(j.ok){ load(); }
};

cEl('closeBtn').onclick = async ()=>{
  const fd = new FormData(); fd.append('class_id', cEl('class_id').value);
  const r = await fetch('../modules/attendance/close_class.php',{method:'POST',body:fd});
  let j; try { j=await r.json(); } catch(e){ j={ok:false,error:'Bad response'}; }
  msg.textContent = j.ok ? 'Closed ✔' : ('Error: '+(j.error||'failed'));
  if(j.ok){ load(); }
};

// auto-load default class
load();
</script>
</body>
</html>
