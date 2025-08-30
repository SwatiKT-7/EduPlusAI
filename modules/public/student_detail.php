<?php
// View details for a student and generate a GPT mentor plan.
// Usage: /public/student_detail.php?user_id=3  (optionally &course_id=1)
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>EduPulse • Student Detail</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body{background:#0b1220;color:#e5e7eb}
    .card{background:#111827;border-radius:1rem;padding:1.25rem;box-shadow:0 10px 25px rgba(0,0,0,.35)}
    .chip{padding:.15rem .55rem;border-radius:.5rem;font-size:.8rem}
    pre{white-space:pre-wrap}
  </style>
</head>
<body class="min-h-screen p-4">
  <div class="max-w-5xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Student Detail</h1>
      <div class="flex gap-2">
        <input id="uid" class="px-3 py-2 rounded-lg text-black" type="number" placeholder="user_id" value="<?= $user_id ?: '' ?>">
        <input id="cid" class="px-3 py-2 rounded-lg text-black" type="number" placeholder="course_id (optional)" value="<?= $course_id ?: '' ?>">
        <button id="loadBtn" class="px-4 py-2 bg-slate-700 rounded-lg hover:bg-slate-600">Load</button>
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
      <div class="card">
        <h2 class="text-lg font-semibold mb-2">Profile</h2>
        <div id="profile">—</div>
        <h3 class="mt-4 font-medium">Reasons</h3>
        <ul id="reasons" class="list-disc ml-5 mt-1 text-sm opacity-90"></ul>
      </div>

      <div class="card">
        <h2 class="text-lg font-semibold mb-2">Generate Mentor Plan (GPT)</h2>
        <div class="text-sm opacity-80 mb-2">Creates a one-week plan using risk + attendance. Uses your API key in <code>config.php</code>.</div>
        <button id="planBtn" class="px-4 py-2 bg-emerald-600 rounded-lg hover:bg-emerald-500">Generate Plan</button>
        <div id="planMsg" class="text-sm mt-2 opacity-80">—</div>
        <pre id="planText" class="mt-3 bg-black/30 p-3 rounded-lg text-sm"></pre>
      </div>
    </div>

    <div class="card">
      <h2 class="text-lg font-semibold mb-2">Metrics</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-left text-slate-300">
            <tr>
              <th class="py-2 pr-4">Course</th>
              <th class="py-2 pr-4">Attn %</th>
              <th class="py-2 pr-4">Risk</th>
              <th class="py-2 pr-4">Projection %</th>
              <th class="py-2 pr-4">Updated</th>
            </tr>
          </thead>
          <tbody id="metricBody"></tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <h2 class="text-lg font-semibold mb-2">Recent Attendance</h2>
      <ul id="attList" class="text-sm space-y-1"></ul>
    </div>
  </div>

<script>
const uidInput = document.getElementById('uid');
const cidInput = document.getElementById('cid');
const loadBtn  = document.getElementById('loadBtn');
const profDiv  = document.getElementById('profile');
const reasonsUl= document.getElementById('reasons');
const metricBody = document.getElementById('metricBody');
const attList  = document.getElementById('attList');
const planBtn  = document.getElementById('planBtn');
const planMsg  = document.getElementById('planMsg');
const planText = document.getElementById('planText');

function courseLabel(m){
  if (!m) return '-';
  const c = (m.course_code || '-');
  const n = (m.course_name || '');
  return `${c} ${n ? '('+n+')' : ''}`;
}
function chipColor(r){
  if (r===null || r===undefined) return '#374151';
  if (r<=20) return '#065f46';
  if (r<=40) return '#166534';
  if (r<=60) return '#92400e';
  if (r<=80) return '#b91c1c';
  return '#7f1d1d';
}

async function load(){
  const uid = parseInt(uidInput.value||'0',10);
  if(!uid){ alert('Enter user_id'); return; }
  const c = cidInput.value ? `&course_id=${encodeURIComponent(cidInput.value)}` : '';
  const r = await fetch(`../modules/analytics/student_detail.php?user_id=${uid}${c}`);
  let j; try{ j = await r.json(); }catch(e){ j={ok:false,error:'Bad response'}; }
  if(!j.ok){ profDiv.textContent = 'Error: '+(j.error||'failed'); return; }

  // Profile
  const p = j.profile;
  profDiv.innerHTML = `
    <div class="text-sm">Name: <b>${p.name}</b></div>
    <div class="text-sm">Email: ${p.email||'-'} | Enroll: ${p.enrollment_no||'-'} | Role: ${p.role}</div>
  `;

  // Reasons
  reasonsUl.innerHTML = '';
  (j.reasons||[]).forEach(x=>{
    const li=document.createElement('li'); li.textContent=x; reasonsUl.appendChild(li);
  });

  // Metrics table
  metricBody.innerHTML = '';
  (j.metrics||[]).forEach(m=>{
    const tr=document.createElement('tr');
    tr.innerHTML = `
      <td class="py-2 pr-4">${courseLabel(m)}</td>
      <td class="py-2 pr-4">${m.attn_cum!==null?m.attn_cum.toFixed(2):'-'}</td>
      <td class="py-2 pr-4"><span class="chip" style="background:${chipColor(m.risk_score)}">${m.risk_score ?? '-'}</span></td>
      <td class="py-2 pr-4">${m.term_attn_projection!==null?m.term_attn_projection.toFixed(2):'-'}</td>
      <td class="py-2 pr-4">${m.updated_at||'-'}</td>
    `;
    metricBody.appendChild(tr);
  });

  // Attendance list
  attList.innerHTML = '';
  (j.recent_attendance||[]).forEach(a=>{
    const li=document.createElement('li');
    li.textContent = `[${a.created_at}] ${a.course_code || '-'} • class ${a.class_id} • status: ${a.status}`;
    attList.appendChild(li);
  });

  // Stash for mentor plan
  window.__last_detail = j;
}

loadBtn.onclick = load;

// GPT: build course summary and call mentor API
planBtn.onclick = async ()=>{
  const j = window.__last_detail;
  if(!j){ planMsg.textContent='Load student first.'; return; }
  const userName = j.profile?.name || 'Student';
  // Build minimal courses array for the mentor endpoint
  const courses = (j.metrics||[]).map(m=>({
    code: m.course_code || 'COURSE',
    attn: m.attn_cum || 0,
    pending: [] // you can add actual pending from your LMS later
  }));
  const riskAvg = (()=> {
    const r = (j.metrics||[]).map(m=> (m.risk_score ?? 0));
    if (!r.length) return 0;
    return Math.round(r.reduce((a,b)=>a+b,0)/r.length);
  })();

  planMsg.textContent = 'Generating plan…';
  const fd = new FormData();
  fd.append('user_name', userName);
  fd.append('risk_score', String(riskAvg));
  fd.append('courses', JSON.stringify(courses));
  const r = await fetch('../modules/ai/mentor.php', { method:'POST', body: fd });
  let resp; try { resp = await r.json(); } catch(e){ resp = {ok:false,error:'Bad response'}; }
  if(!resp.ok){
    planMsg.textContent = 'Error: ' + (resp.error||'GPT failed');
    planText.textContent = '';
    return;
  }
  planMsg.textContent = 'Plan ready ✔';
  planText.textContent = resp.plan || '';
};

// auto-load if query has user_id
<?php if ($user_id): ?>
load();
<?php endif; ?>
</script>
</body>
</html>
