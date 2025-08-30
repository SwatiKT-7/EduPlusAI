<?php
require_once __DIR__.'/../config.php';
$conn=db();
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
if($class_id<=0){ echo "Provide ?class_id=..."; exit; }

$q=$conn->prepare("SELECT c.id, c.status, crs.name AS course_name, c.room FROM classes c JOIN courses crs ON crs.id=c.course_id WHERE c.id=?");
$q->bind_param("i",$class_id); $q->execute(); $res=$q->get_result();
if($res->num_rows===0){ echo "Class not found"; exit; }
$class=$res->fetch_assoc();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Faculty Session</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>body{background:#0b1220;color:#e5e7eb}.card{background:#111827;border-radius:1rem;padding:1rem;box-shadow:0 10px 25px rgba(0,0,0,.35)}</style>
</head>
<body class="min-h-screen p-4">
  <div class="max-w-4xl mx-auto card">
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold">Class #<?=htmlspecialchars($class_id)?> — <?=htmlspecialchars($class['course_name'])?></h1>
      <button id="startBtn" class="px-4 py-2 bg-indigo-600 rounded-lg hover:bg-indigo-500">Start / Go Live</button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
      <div class="card">
        <h2 class="text-lg mb-2">Rotating Token</h2>
        <div id="tokenBox" class="bg-white p-4 rounded-lg w-64 h-64 flex items-center justify-center text-black text-center">Waiting…</div>
        <div class="text-sm mt-2">Window: <span id="windowSpan">-</span></div>
        <div class="text-xs mt-1 opacity-70">Students can type this token in their scanner.</div>
      </div>
      <div class="card">
        <h2 class="text-lg mb-2">Live Attendance</h2>
        <div class="text-4xl"><span id="present">0</span> Present</div>
        <div class="opacity-80 mt-2">Late: <span id="late">0</span> • Absent: <span id="absent">0</span></div>
        <div id="status" class="text-xs opacity-60 mt-4">Waiting for SSE…</div>
      </div>
    </div>
  </div>

<script>
const classId = <?= (int)$class_id ?>;
const startBtn = document.getElementById('startBtn');
const tokenBox = document.getElementById('tokenBox');
const windowSpan = document.getElementById('windowSpan');
const presentEl = document.getElementById('present');
const lateEl = document.getElementById('late');
const absentEl = document.getElementById('absent');
const statusEl = document.getElementById('status');

startBtn.onclick = async () => {
  const fd=new FormData(); fd.append('class_id',classId);
  const r=await fetch('../modules/attendance/start_class.php',{method:'POST',body:fd});
  const j=await r.json(); if(!j.ok){ alert(j.error||'Error'); return; }
  startToken(); startSSE();
};

async function startToken(){
  async function refresh(){
    const r = await fetch(`../modules/attendance/qr_token.php?class_id=${classId}`);
    const j = await r.json();
    if(!j.ok){ tokenBox.textContent = j.error; return; }
    tokenBox.textContent = j.token;
    windowSpan.textContent = j.valid_from + " → " + j.valid_to;
  }
  await refresh();
  setInterval(refresh, 5000);
}
function startSSE(){
  const es = new EventSource(`../modules/attendance/sse_live.php?class_id=${classId}`);
  es.onmessage = ev => {
    statusEl.textContent = "Live";
    try{ const d=JSON.parse(ev.data);
      presentEl.textContent=d.present; lateEl.textContent=d.late; absentEl.textContent=d.absent;
    }catch(e){}
  };
  es.onerror = () => { statusEl.textContent = "SSE disconnected"; };
}
</script>
</body>
</html>
