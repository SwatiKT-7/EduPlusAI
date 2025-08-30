<?php /* Upload a JD and list top student matches (keyword baseline). */ ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>EduPulse • Placements</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>body{background:#0b1220;color:#e5e7eb}.card{background:#111827;border-radius:1rem;padding:1.25rem;box-shadow:0 10px 25px rgba(0,0,0,.35)}</style>
</head>
<body class="min-h-screen p-4">
  <div class="max-w-6xl mx-auto space-y-4">
    <div class="card">
      <h1 class="text-2xl font-semibold mb-3">Placement JD → Matches</h1>
      <div class="grid md:grid-cols-3 gap-3">
        <div><label class="text-sm">Company</label><input id="company" class="w-full mt-1 px-3 py-2 rounded-lg text-black" placeholder="ACME Inc."></div>
        <div><label class="text-sm">Role</label><input id="role" class="w-full mt-1 px-3 py-2 rounded-lg text-black" placeholder="Software Intern"></div>
        <div><label class="text-sm">Top N</label><input id="topn" class="w-full mt-1 px-3 py-2 rounded-lg text-black" type="number" value="10"></div>
      </div>
      <div class="mt-3">
        <label class="text-sm">Job Description</label>
        <textarea id="text" rows="6" class="w-full mt-1 px-3 py-2 rounded-lg text-black" placeholder="Paste JD here..."></textarea>
      </div>
      <div class="mt-3 flex gap-2">
        <button id="uploadBtn" class="px-4 py-2 bg-emerald-600 rounded-lg hover:bg-emerald-500">Upload & Match</button>
        <div id="msg" class="text-sm opacity-80">—</div>
      </div>
    </div>

    <div class="card">
      <h2 class="text-lg font-semibold mb-2">Matches</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-left text-slate-300"><tr>
            <th class="py-2 pr-4">#</th><th class="py-2 pr-4">Name</th><th class="py-2 pr-4">Email</th><th class="py-2 pr-4">Score</th>
          </tr></thead>
          <tbody id="tbody"></tbody>
        </table>
      </div>
    </div>
  </div>

<script>
const msg = document.getElementById('msg');
const tbody = document.getElementById('tbody');

document.getElementById('uploadBtn').onclick = async ()=>{
  const fd = new FormData();
  fd.append('company', document.getElementById('company').value.trim());
  fd.append('role', document.getElementById('role').value.trim());
  fd.append('text', document.getElementById('text').value.trim());
  msg.textContent = 'Uploading…';
  const r = await fetch('../modules/placement/upload_jd.php',{method:'POST', body: fd});
  let j; try { j = await r.json(); } catch(e){ j = {ok:false,error:'Bad response'}; }
  if(!j.ok){ msg.textContent = 'Error: ' + (j.error||'upload failed'); return; }
  const jd_id = j.jd_id;
  msg.textContent = 'Matching…';
  const m = await fetch(`../modules/placement/match_students.php?jd_id=${jd_id}`);
  let k; try { k = await m.json(); } catch(e){ k = {ok:false,error:'Bad response'}; }
  if(!k.ok){ msg.textContent = 'Error: ' + (k.error||'match failed'); return; }
  render(k.matches || [], parseInt(document.getElementById('topn').value||'10',10));
  msg.textContent = `Matched ✔ (role: ${k.jd.role})`;
};

function render(rows, topn){
  tbody.innerHTML = '';
  rows.slice(0, topn).forEach((r,i)=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="py-2 pr-4">${i+1}</td>
      <td class="py-2 pr-4">${r.name}</td>
      <td class="py-2 pr-4">${r.email}</td>
      <td class="py-2 pr-4">${r.score}</td>
    `;
    tbody.appendChild(tr);
  });
}
</script>
</body>
</html>
