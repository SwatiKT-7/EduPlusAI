<?php /* Simple page to view risk scores and recompute on click */ ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>EduPulse • Risk Heatmap</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body{background:#0b1220;color:#e5e7eb}
    .card{background:#111827;border-radius:1rem;padding:1.25rem;box-shadow:0 10px 25px rgba(0,0,0,.35)}
    .chip{padding:.15rem .55rem;border-radius:.5rem;font-size:.8rem}
  </style>
</head>
<body class="min-h-screen p-4">
  <div class="max-w-6xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Risk Heatmap</h1>
      <div class="flex items-center gap-2">
        <input id="courseId" type="number" class="px-3 py-2 rounded-lg text-black" placeholder="Course ID (optional)">
        <button id="recomputeBtn" class="px-4 py-2 bg-emerald-600 rounded-lg hover:bg-emerald-500">Recompute now</button>
        <button id="refreshBtn" class="px-4 py-2 bg-slate-700 rounded-lg hover:bg-slate-600">Refresh</button>
      </div>
    </div>

    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-left text-slate-300">
            <tr>
              <th class="py-2 pr-4">Student</th>
              <th class="py-2 pr-4">Enroll #</th>
              <th class="py-2 pr-4">Course</th>
              <th class="py-2 pr-4">Attn %</th>
              <th class="py-2 pr-4">Risk</th>
              <th class="py-2 pr-4">Projection %</th>
              <th class="py-2 pr-4">Updated</th>
            </tr>
          </thead>
          <tbody id="tbody"></tbody>
        </table>
      </div>
      <div id="msg" class="text-sm opacity-70 mt-3">Ready.</div>
    </div>

    <div class="card">
      <h2 class="text-lg font-semibold mb-2">Legend</h2>
      <div class="flex items-center gap-2">
        <span class="chip" style="background:#064e3b">0–20 (Low)</span>
        <span class="chip" style="background:#166534">21–40</span>
        <span class="chip" style="background:#92400e">41–60</span>
        <span class="chip" style="background:#b91c1c">61–80</span>
        <span class="chip" style="background:#7f1d1d">81–100 (High)</span>
      </div>
      <p class="text-xs opacity-70 mt-2">
        Risk in this demo = 100 − Attendance%. Lower attendance → higher risk. You can refine later by adding internal marks,
        submission ratios, and trends; then render the same page.
      </p>
    </div>
  </div>

<script>
const tbody = document.getElementById('tbody');
const msg   = document.getElementById('msg');
const courseInput = document.getElementById('courseId');

function colorForRisk(r){
  if (r===null || r===undefined) return '#374151';        // gray
  if (r<=20) return '#065f46';
  if (r<=40) return '#166534';
  if (r<=60) return '#92400e';
  if (r<=80) return '#b91c1c';
  return '#7f1d1d';
}

async function fetchRows(){
  const c = courseInput.value ? `?course_id=${encodeURIComponent(courseInput.value)}` : '';
  const r = await fetch(`../modules/analytics/list_metrics.php${c}`);
  let j; try { j = await r.json(); } catch(e){ j = {ok:false,error:'Bad response'}; }
  if(!j.ok){ msg.textContent = 'Error: ' + (j.error||'fetch failed'); return; }
  tbody.innerHTML = '';
  (j.rows||[]).forEach(row=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="py-2 pr-4">${row.student_name || '-'}</td>
      <td class="py-2 pr-4">${row.enrollment_no || '-'}</td>
      <td class="py-2 pr-4">${(row.course_code||'-')} <span class="opacity-70">${row.course_name||''}</span></td>
      <td class="py-2 pr-4">${row.attn_cum!==null ? row.attn_cum.toFixed(2) : '-'}</td>
      <td class="py-2 pr-4">
        <span class="chip" style="background:${colorForRisk(row.risk_score)}">${row.risk_score ?? '-'}</span>
      </td>
      <td class="py-2 pr-4">${row.term_attn_projection!==null ? row.term_attn_projection.toFixed(2) : '-'}</td>
      <td class="py-2 pr-4">${row.updated_at || '-'}</td>
    `;
    tbody.appendChild(tr);
  });
  msg.textContent = `Loaded ${ (j.rows||[]).length } rows.`;
}

document.getElementById('refreshBtn').onclick = fetchRows;

document.getElementById('recomputeBtn').onclick = async ()=>{
  msg.textContent = 'Recomputing…';
  const fd = new FormData();
  if (courseInput.value) fd.append('course_id', courseInput.value);
  const r = await fetch('../modules/analytics/recompute.php', { method:'POST', body: fd });
  let j; try { j = await r.json(); } catch(e){ j = {ok:false, error:'Bad response'}; }
  if(!j.ok){ msg.textContent = 'Error: ' + (j.error||'recompute failed'); return; }
  await fetchRows();
  msg.textContent = 'Recomputed ✔ and refreshed.';
};

// initial load
fetchRows();
</script>
</body>
</html>
