<?php /* Generate MCQs with GPT and render in a table; copy/save JSON. */ ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>EduPulse • Faculty Quiz Generator</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>body{background:#0b1220;color:#e5e7eb}.card{background:#111827;border-radius:1rem;padding:1.25rem;box-shadow:0 10px 25px rgba(0,0,0,.35)}</style>
</head>
<body class="min-h-screen p-4">
  <div class="max-w-6xl mx-auto space-y-4">
    <div class="card">
      <h1 class="text-2xl font-semibold mb-3">Faculty Quiz Generator</h1>
      <div class="grid md:grid-cols-5 gap-3">
        <div><label class="text-sm">Course ID</label><input id="course_id" class="w-full mt-1 px-3 py-2 rounded-lg text-black" type="number" value="1"></div>
        <div class="md:col-span-2"><label class="text-sm">Topic</label><input id="topic" class="w-full mt-1 px-3 py-2 rounded-lg text-black" placeholder="e.g., Stack vs Queue"></div>
        <div><label class="text-sm">Difficulty</label>
          <select id="difficulty" class="w-full mt-1 px-3 py-2 rounded-lg text-black">
            <option>easy</option><option selected>medium</option><option>hard</option>
          </select>
        </div>
        <div><label class="text-sm">Count</label><input id="count" class="w-full mt-1 px-3 py-2 rounded-lg text-black" type="number" value="5" min="1" max="20"></div>
      </div>
      <div class="mt-3 flex gap-2">
        <button id="genBtn" class="px-4 py-2 bg-emerald-600 rounded-lg hover:bg-emerald-500">Generate</button>
        <button id="copyBtn" class="px-4 py-2 bg-slate-700 rounded-lg hover:bg-slate-600">Copy JSON</button>
        <button id="saveBtn" class="px-4 py-2 bg-slate-700 rounded-lg hover:bg-slate-600">Download JSON</button>
      </div>
      <div id="msg" class="text-sm opacity-80 mt-2">—</div>
    </div>

    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-left text-slate-300"><tr>
            <th class="py-2 pr-4">#</th><th class="py-2 pr-4">Question</th>
            <th class="py-2 pr-4">A</th><th class="py-2 pr-4">B</th><th class="py-2 pr-4">C</th><th class="py-2 pr-4">D</th>
            <th class="py-2 pr-4">Answer</th><th class="py-2 pr-4">Explanation</th>
          </tr></thead>
          <tbody id="tbody"></tbody>
        </table>
      </div>
    </div>
  </div>

<script>
let lastJSON = { questions: [] };

function render(questions){
  const tbody = document.getElementById('tbody');
  tbody.innerHTML = '';
  questions.forEach((q,i)=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="py-2 pr-4">${i+1}</td>
      <td class="py-2 pr-4">${q.q||'-'}</td>
      <td class="py-2 pr-4">${q.options?.[0]||'-'}</td>
      <td class="py-2 pr-4">${q.options?.[1]||'-'}</td>
      <td class="py-2 pr-4">${q.options?.[2]||'-'}</td>
      <td class="py-2 pr-4">${q.options?.[3]||'-'}</td>
      <td class="py-2 pr-4">${q.answer||'-'}</td>
      <td class="py-2 pr-4">${q.explanation||'-'}</td>
    `;
    tbody.appendChild(tr);
  });
}

document.getElementById('genBtn').onclick = async ()=>{
  const fd = new FormData();
  fd.append('course_id', document.getElementById('course_id').value || '0');
  fd.append('topic', document.getElementById('topic').value.trim());
  fd.append('difficulty', document.getElementById('difficulty').value);
  fd.append('count', document.getElementById('count').value || '5');
  document.getElementById('msg').textContent = 'Generating…';
  const r = await fetch('../modules/ai/faculty_quiz.php',{method:'POST', body: fd});
  let j; try { j = await r.json(); } catch(e){ j = {ok:false, error:'Bad response'}; }
  if(!j.ok){ document.getElementById('msg').textContent = 'Error: '+(j.error||'failed'); return; }
  lastJSON = { questions: j.questions, meta: j.meta };
  render(j.questions);
  document.getElementById('msg').textContent = 'Ready ✔';
};

document.getElementById('copyBtn').onclick = async ()=>{
  try { await navigator.clipboard.writeText(JSON.stringify(lastJSON,null,2)); alert('Copied JSON'); } catch(e){ alert('Copy failed'); }
};
document.getElementById('saveBtn').onclick = ()=>{
  const blob = new Blob([JSON.stringify(lastJSON,null,2)], {type:'application/json'});
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob); a.download = 'quiz.json'; a.click();
  URL.revokeObjectURL(a.href);
};
</script>
</body>
</html>
