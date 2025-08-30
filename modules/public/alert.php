<?php /* View & resolve alerts */ ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>EduPulse • Alerts</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>body{background:#0b1220;color:#e5e7eb}.card{background:#111827;border-radius:1rem;padding:1.25rem;box-shadow:0 10px 25px rgba(0,0,0,.35)}.chip{padding:.15rem .55rem;border-radius:.5rem;font-size:.8rem}</style>
</head>
<body class="min-h-screen p-4">
  <div class="max-w-6xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Alerts</h1>
      <div class="flex gap-2">
        <input id="courseId" type="number" class="px-3 py-2 rounded-lg text-black" placeholder="Course ID (optional)">
        <button id="refreshBtn" class="px-4 py-2 bg-slate-700 rounded-lg hover:bg-slate-600">Refresh</button>
      </div>
    </div>

    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-left text-slate-300">
            <tr>
              <th class="py-2 pr-4">#</th>
              <th class="py-2 pr-4">Student</th>
              <th class="py-2 pr-4">Course</th>
              <th class="py-2 pr-4">Type</th>
              <th class="py-2 pr-4">Severity</th>
              <th class="py-2 pr-4">Message</th>
              <th class="py-2 pr-4">Created</th>
              <th class="py-2 pr-4">Resolved</th>
              <th class="py-2 pr-4">Action</th>
            </tr>
          </thead>
          <tbody id="tbody"></tbody>
        </table>
      </div>
      <div id="msg" class="text-sm opacity-70 mt-3">Ready.</div>
    </div>
  </div>

<script>
const tbody = document.getElementById('tbody');
const msg = document.getElementById('msg');
const courseInput = document.getElementById('courseId');

function color(sev){
  if (sev==='high') return '#b91c1c';
  if (sev==='medium') return '#92400e';
  return '#065f46';
}

async function load(){
  const c = courseInput.value ? `?course_id=${encodeURIComponent(courseInput.value)}` : '';
  const r = await fetch(`../modules/analytics/list_alerts.php${c}`);
  let j; try { j = await r.json(); } catch(e){ j = {ok:false,error:'Bad response'}; }
  if(!j.ok){ msg.textContent='Error: '+(j.error||'fetch failed'); return; }
  tbody.innerHTML='';
  (j.rows||[]).forEach((a,i)=>{
    const tr=document.createElement('tr');
    tr.innerHTML = `
      <td class="py-2 pr-4">${i+1}</td>
      <td class="py-2 pr-4">${a.student_name||'-'}</td>
      <td class="py-2 pr-4">${a.course_code||'-'}</td>
      <td class="py-2 pr-4">${a.type}</td>
      <td class="py-2 pr-4"><span class="chip" style="background:${color(a.severity)}">${a.severity}</span></td>
      <td class="py-2 pr-4">${a.message}</td>
      <td class="py-2 pr-4">${a.created_at}</td>
      <td class="py-2 pr-4">${a.resolved_at || '-'}</td>
      <td class="py-2 pr-4">
        ${a.resolved_at ? '<span class="opacity-60">—</span>' : `<button class="px-3 py-1 bg-emerald-600 rounded hover:bg-emerald-500" data-id="${a.id}">Resolve</button>`}
      </td>
    `;
    tbody.appendChild(tr);
  });

  // wire resolve buttons
  tbody.querySelectorAll('button[data-id]').forEach(btn=>{
    btn.onclick = async ()=>{
      const id = btn.getAttribute('data-id');
      const fd = new FormData(); fd.append('alert_id', id);
      const r = await fetch('../modules/analytics/resolve_alert.php',{method:'POST',body:fd});
      let k; try { k = await r.json(); } catch(e){ k={ok:false,error:'Bad response'}; }
      if(!k.ok){ alert('Resolve failed: '+(k.error||'')); return; }
      load();
    };
  });

  msg.textContent = `Loaded ${ (j.rows||[]).length } alerts.`;
}
document.getElementById('refreshBtn').onclick = load;
load();
</script>
</body>
</html>
