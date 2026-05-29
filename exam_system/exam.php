<?php
session_start();
if (!isset($_SESSION['username'])) { header('Location: index.php'); exit; }

require_once 'questions.php';

$examId = $_GET['exam'] ?? '';
$questions = getQuestions($examId);
$examTitle = $examNames[$examId] ?? '';

if (empty($questions) || empty($examTitle)) {
    header('Location: dashboard.php');
    exit;
}

$_SESSION['exam_id']    = $examId;
$_SESSION['exam_title'] = $examTitle;
$_SESSION['exam_start'] = time();
$totalQ = count($questions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ExamPro — <?= htmlspecialchars($examTitle) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
  --bg:#0a0e1a; --surface:#111827; --surface2:#0f1729; --border:#1f2d45;
  --accent:#3b82f6; --accent2:#06b6d4; --text:#e2e8f0; --muted:#64748b;
  --success:#22c55e; --danger:#ef4444; --warn:#f59e0b;
  --grad:linear-gradient(135deg,#3b82f6,#06b6d4);
}
html, body { height:100%; }
body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; display:flex; flex-direction:column; }

/* TOP BAR */
.topbar {
  background:rgba(17,24,39,.95); backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);
  padding:.75rem 1.5rem;
  display:flex; align-items:center; justify-content:space-between; gap:1rem;
  position:sticky; top:0; z-index:50;
}
.exam-info { display:flex; align-items:center; gap:.75rem; }
.exam-badge {
  font-family:'Syne',sans-serif; font-size:.8rem; font-weight:700;
  background:var(--grad); padding:.3rem .8rem; border-radius:8px; letter-spacing:.04em;
}
.exam-title { font-family:'Syne',sans-serif; font-size:1rem; font-weight:700; }

.timer-wrap { display:flex; align-items:center; gap:.5rem; }
.timer-icon { font-size:1.1rem; }
.timer {
  font-family:'Syne',sans-serif; font-size:1.3rem; font-weight:800;
  background:var(--grad); -webkit-background-clip:text; -webkit-text-fill-color:transparent;
  min-width:72px; text-align:center;
}
.timer.warn { background:linear-gradient(135deg,#f59e0b,#ef4444); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
.timer.danger { -webkit-text-fill-color:#ef4444; background:none; animation:pulse .5s ease-in-out infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.6} }

.progress-bar-wrap { height:3px; background:var(--border); position:sticky; top:57px; z-index:49; }
.progress-bar-fill { height:100%; background:var(--grad); transition:width .3s ease; }

/* LAYOUT */
.exam-layout {
  display:flex; gap:0; flex:1;
  max-width:1200px; margin:0 auto; padding:1.5rem;
  width:100%;
}

/* SIDEBAR — question grid */
.sidebar {
  width:220px; flex-shrink:0;
  background:rgba(17,24,39,.8); border:1px solid var(--border);
  border-radius:16px; padding:1.25rem; height:fit-content;
  position:sticky; top:80px; margin-right:1.5rem;
}
.sidebar-title { font-size:.75rem; text-transform:uppercase; letter-spacing:.1em; color:var(--muted); margin-bottom:1rem; font-weight:600; }
.q-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:.4rem; }
.q-dot {
  width:100%; aspect-ratio:1; border-radius:8px;
  background:rgba(255,255,255,.05); border:1px solid var(--border);
  font-size:.75rem; font-weight:600; display:grid; place-items:center;
  cursor:pointer; transition:all .15s; color:var(--muted);
}
.q-dot:hover { background:rgba(59,130,246,.15); border-color:var(--accent); color:var(--text); }
.q-dot.answered { background:rgba(34,197,94,.12); border-color:rgba(34,197,94,.4); color:#4ade80; }
.q-dot.current { background:var(--grad); border-color:transparent; color:#fff; }

.sidebar-stats { margin-top:1.25rem; padding-top:1.25rem; border-top:1px solid var(--border); display:grid; grid-template-columns:1fr 1fr; gap:.75rem; }
.s-stat { text-align:center; }
.s-stat-num { font-family:'Syne',sans-serif; font-size:1.2rem; font-weight:800; }
.s-stat-num.green { color:#4ade80; }
.s-stat-num.yellow { color:#fbbf24; }
.s-stat-label { font-size:.7rem; color:var(--muted); margin-top:.2rem; text-transform:uppercase; letter-spacing:.06em; }

/* MAIN CONTENT */
.main { flex:1; min-width:0; }

.q-card {
  background:rgba(17,24,39,.8); border:1px solid var(--border);
  border-radius:20px; padding:2rem; margin-bottom:1.25rem;
  animation:fadeIn .3s ease;
}
@keyframes fadeIn{ from{opacity:0;transform:translateX(10px)} to{opacity:1;transform:translateX(0)} }

.q-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; }
.q-num {
  font-family:'Syne',sans-serif; font-size:.8rem; font-weight:700;
  background:rgba(59,130,246,.15); border:1px solid rgba(59,130,246,.25);
  color:var(--accent2); padding:.3rem .75rem; border-radius:8px; letter-spacing:.06em;
}
.q-flag {
  background:none; border:1px solid var(--border); padding:.3rem .75rem; border-radius:8px;
  color:var(--muted); cursor:pointer; font-size:.8rem; transition:all .2s;
}
.q-flag:hover, .q-flag.flagged { background:rgba(245,158,11,.12); border-color:rgba(245,158,11,.4); color:#fbbf24; }

.q-text { font-size:1.05rem; line-height:1.7; margin-bottom:1.75rem; font-weight:400; }

.options { display:grid; gap:.75rem; }
.option {
  display:flex; align-items:center; gap:.875rem;
  padding:1rem 1.25rem;
  background:rgba(255,255,255,.03); border:1.5px solid var(--border);
  border-radius:12px; cursor:pointer;
  transition:all .2s cubic-bezier(.16,1,.3,1);
  position:relative; overflow:hidden;
}
.option:hover { background:rgba(59,130,246,.07); border-color:rgba(59,130,246,.35); }
.option.selected { background:rgba(59,130,246,.12); border-color:var(--accent); }
.option input[type=radio] { display:none; }
.option-circle {
  width:22px; height:22px; border-radius:50%;
  border:2px solid var(--border); flex-shrink:0;
  display:grid; place-items:center; transition:all .2s;
}
.option.selected .option-circle { background:var(--grad); border-color:transparent; }
.option.selected .option-circle::after { content:'✓'; color:#fff; font-size:.75rem; font-weight:700; }
.option-letter {
  width:28px; height:28px; border-radius:8px;
  background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.08);
  display:grid; place-items:center; font-size:.8rem; font-weight:700;
  color:var(--muted); flex-shrink:0; transition:all .2s; font-family:'Syne',sans-serif;
}
.option.selected .option-letter { background:rgba(59,130,246,.2); border-color:rgba(59,130,246,.4); color:var(--accent2); }
.option-text { font-size:.95rem; line-height:1.5; }

/* NAVIGATION */
.nav-footer {
  display:flex; justify-content:space-between; align-items:center; gap:1rem;
  flex-wrap:wrap;
}
.btn-nav {
  display:flex; align-items:center; gap:.5rem;
  padding:.75rem 1.5rem; border-radius:12px; font-size:.9rem; font-weight:600;
  cursor:pointer; transition:all .2s; border:1px solid var(--border);
  background:rgba(255,255,255,.04); color:var(--text);
  font-family:'DM Sans',sans-serif;
}
.btn-nav:hover { background:rgba(255,255,255,.08); border-color:rgba(255,255,255,.15); }
.btn-nav:disabled { opacity:.35; cursor:not-allowed; }
.btn-next { background:rgba(59,130,246,.1); border-color:rgba(59,130,246,.3); color:#93c5fd; }
.btn-next:hover { background:rgba(59,130,246,.2); }

.btn-submit {
  padding:.875rem 2rem; background:linear-gradient(135deg,#22c55e,#16a34a);
  border:none; border-radius:12px; color:#fff; font-family:'Syne',sans-serif;
  font-size:1rem; font-weight:700; cursor:pointer; letter-spacing:.04em;
  box-shadow:0 4px 15px rgba(34,197,94,.25); transition:transform .15s, box-shadow .2s;
}
.btn-submit:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(34,197,94,.35); }

/* SUBMIT CONFIRMATION MODAL */
.modal-overlay {
  position:fixed;inset:0;z-index:200;
  background:rgba(0,0,0,.75); backdrop-filter:blur(10px);
  display:none; align-items:center; justify-content:center; padding:1rem;
}
.modal-overlay.show { display:flex; }
.modal {
  background:#111827; border:1px solid var(--border); border-radius:24px;
  padding:2.5rem; max-width:480px; width:100%;
  box-shadow:0 40px 80px rgba(0,0,0,.5);
  animation:popIn .3s cubic-bezier(.16,1,.3,1);
}
@keyframes popIn{ from{opacity:0;transform:scale(.9)} to{opacity:1;transform:scale(1)} }
.modal-icon { font-size:3rem; text-align:center; margin-bottom:1.25rem; }
.modal h3 { font-family:'Syne',sans-serif; font-size:1.4rem; font-weight:700; margin-bottom:.5rem; text-align:center; }
.modal p { color:var(--muted); font-size:.9rem; line-height:1.7; margin-bottom:1.5rem; text-align:center; }
.modal-stats { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; margin-bottom:1.5rem; }
.m-stat { background:rgba(255,255,255,.04); border:1px solid var(--border); border-radius:12px; padding:1rem; text-align:center; }
.m-stat-num { font-family:'Syne',sans-serif; font-size:1.4rem; font-weight:800; color:var(--accent2); }
.m-stat-label { font-size:.75rem; color:var(--muted); margin-top:.25rem; }
.modal-buttons { display:flex; gap:.75rem; }
.btn-cancel { flex:1;padding:.875rem;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:12px;color:var(--muted);cursor:pointer;font-size:.9rem; }
.btn-cancel:hover { background:rgba(255,255,255,.08); }
.btn-confirm-submit { flex:2;padding:.875rem;background:linear-gradient(135deg,#22c55e,#16a34a);border:none;border-radius:12px;color:#fff;font-family:'Syne',sans-serif;font-weight:700;cursor:pointer;font-size:.95rem;transition:transform .15s; }
.btn-confirm-submit:hover { transform:translateY(-1px); }

/* Time Up Modal */
.timeup-modal { position:fixed;inset:0;z-index:300;background:rgba(0,0,0,.85);backdrop-filter:blur(12px);display:none;align-items:center;justify-content:center; }
.timeup-modal.show { display:flex; }
.timeup-inner { background:#111827;border:1px solid rgba(239,68,68,.3);border-radius:24px;padding:3rem 2.5rem;max-width:400px;width:100%;text-align:center; }
.timeup-inner h2 { font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;color:#ef4444;margin-bottom:.5rem; }

@media(max-width:768px){
  .sidebar { display:none; }
  .exam-layout { padding:1rem; }
  .exam-title { display:none; }
  .q-card { padding:1.25rem; }
}
@media(max-width:480px){
  .topbar { padding:.6rem 1rem; }
  .timer { font-size:1.1rem; }
  .option { padding:.75rem 1rem; gap:.6rem; }
  .btn-nav { padding:.65rem 1rem; font-size:.85rem; }
}
</style>
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">
  <div class="exam-info">
    <div class="exam-badge">📋 EXAM</div>
    <div class="exam-title"><?= htmlspecialchars($examTitle) ?></div>
  </div>
  <div class="timer-wrap">
    <span class="timer-icon">⏱</span>
    <div class="timer" id="timer">60:00</div>
  </div>
</div>
<div class="progress-bar-wrap">
  <div class="progress-bar-fill" id="progressFill" style="width:0%"></div>
</div>

<div class="exam-layout">
  <!-- SIDEBAR -->
  <div class="sidebar">
    <div class="sidebar-title">Questions</div>
    <div class="q-grid" id="qGrid">
      <?php for($i=0;$i<$totalQ;$i++): ?>
      <div class="q-dot <?= $i===0?'current':'' ?>" id="dot<?=$i?>" onclick="goToQuestion(<?=$i?>)"><?=$i+1?></div>
      <?php endfor; ?>
    </div>
    <div class="sidebar-stats">
      <div class="s-stat">
        <div class="s-stat-num green" id="answeredCount">0</div>
        <div class="s-stat-label">Answered</div>
      </div>
      <div class="s-stat">
        <div class="s-stat-num yellow" id="unansweredCount"><?=$totalQ?></div>
        <div class="s-stat-label">Remaining</div>
      </div>
    </div>
  </div>

  <!-- MAIN -->
  <div class="main">
    <div class="q-card" id="qCard">
      <div class="q-header">
        <div class="q-num" id="qNum">QUESTION 1 / <?=$totalQ?></div>
        <button class="q-flag" id="flagBtn" onclick="toggleFlag()">🚩 Flag</button>
      </div>
      <div class="q-text" id="qText"></div>
      <div class="options" id="optionsContainer"></div>
    </div>

    <div class="nav-footer">
      <button class="btn-nav" id="btnPrev" onclick="navigate(-1)" disabled>← Previous</button>
      <div id="submitArea" style="display:none">
        <button class="btn-submit" onclick="showSubmitModal()">Submit Exam ✓</button>
      </div>
      <button class="btn-nav btn-next" id="btnNext" onclick="navigate(1)">Next →</button>
    </div>
  </div>
</div>

<!-- Submit Modal -->
<div class="modal-overlay" id="submitModal">
  <div class="modal">
    <div class="modal-icon">📤</div>
    <h3>Submit Exam?</h3>
    <p>You're about to submit your exam. Please review your progress below.</p>
    <div class="modal-stats">
      <div class="m-stat"><div class="m-stat-num" id="mAnswered">0</div><div class="m-stat-label">Answered</div></div>
      <div class="m-stat"><div class="m-stat-num" style="color:#fbbf24" id="mUnanswered">0</div><div class="m-stat-label">Unanswered</div></div>
    </div>
    <div class="modal-buttons">
      <button class="btn-cancel" onclick="document.getElementById('submitModal').classList.remove('show')">Review</button>
      <button class="btn-confirm-submit" onclick="submitExam()">Confirm Submit →</button>
    </div>
  </div>
</div>

<!-- Time Up Modal -->
<div class="timeup-modal" id="timeupModal">
  <div class="timeup-inner">
    <div style="font-size:3rem;margin-bottom:1rem">⏰</div>
    <h2>Time's Up!</h2>
    <p style="color:#94a3b8;margin:1rem 0 2rem">Your exam time has ended. Submitting your answers now...</p>
    <div style="width:100%;height:4px;background:#1f2d45;border-radius:2px;overflow:hidden">
      <div id="autoSubmitBar" style="height:100%;background:linear-gradient(90deg,#ef4444,#f97316);width:0%;transition:width 3s linear"></div>
    </div>
  </div>
</div>

<script>
const questions = <?= json_encode($questions) ?>;
const totalQ = questions.length;
let current = 0;
let answers = new Array(totalQ).fill(null);
let flags = new Array(totalQ).fill(false);
let timeLeft = 60 * 60; // 60 minutes in seconds

function renderQuestion(idx) {
  const q = questions[idx];
  document.getElementById('qNum').textContent = `QUESTION ${idx+1} / ${totalQ}`;
  document.getElementById('qText').textContent = q.q;
  document.getElementById('flagBtn').className = 'q-flag' + (flags[idx]?' flagged':'');
  document.getElementById('flagBtn').textContent = flags[idx] ? '🚩 Flagged' : '🚩 Flag';

  const letters = ['A','B','C','D'];
  const container = document.getElementById('optionsContainer');
  container.innerHTML = '';
  q.opts.forEach((opt,i) => {
    const div = document.createElement('div');
    div.className = 'option' + (answers[idx]===i?' selected':'');
    div.onclick = () => selectAnswer(idx, i);
    div.innerHTML = `
      <div class="option-letter">${letters[i]}</div>
      <div class="option-circle"></div>
      <div class="option-text">${opt}</div>`;
    container.appendChild(div);
  });

  // Update dots
  document.querySelectorAll('.q-dot').forEach((d,i) => {
    d.className = 'q-dot';
    if (answers[i] !== null) d.classList.add('answered');
    if (i === idx) d.classList.add('current');
  });

  document.getElementById('btnPrev').disabled = idx === 0;
  document.getElementById('btnNext').style.display = idx === totalQ-1 ? 'none' : '';
  document.getElementById('submitArea').style.display = idx === totalQ-1 ? '' : 'none';

  // Animate
  const card = document.getElementById('qCard');
  card.style.animation = 'none';
  void card.offsetWidth;
  card.style.animation = 'fadeIn .3s ease';

  updateProgress();
}

function selectAnswer(qIdx, optIdx) {
  answers[qIdx] = optIdx;
  renderQuestion(qIdx);
}

function navigate(dir) {
  current = Math.max(0, Math.min(totalQ-1, current + dir));
  renderQuestion(current);
}

function goToQuestion(idx) {
  current = idx;
  renderQuestion(current);
}

function toggleFlag() {
  flags[current] = !flags[current];
  renderQuestion(current);
}

function updateProgress() {
  const answered = answers.filter(a => a !== null).length;
  document.getElementById('answeredCount').textContent = answered;
  document.getElementById('unansweredCount').textContent = totalQ - answered;
  document.getElementById('progressFill').style.width = ((current+1)/totalQ*100)+'%';
}

function showSubmitModal() {
  const answered = answers.filter(a=>a!==null).length;
  document.getElementById('mAnswered').textContent = answered;
  document.getElementById('mUnanswered').textContent = totalQ - answered;
  document.getElementById('submitModal').classList.add('show');
}

function submitExam() {
  // Save to sessionStorage to pass to result page
  const data = {
    examId: '<?= $examId ?>',
    examTitle: '<?= htmlspecialchars($examTitle) ?>',
    answers: answers,
    correctAnswers: questions.map(q=>q.ans),
    timeTaken: (60*60) - timeLeft,
  };
  sessionStorage.setItem('examResult', JSON.stringify(data));
  // Send via form POST
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'submit.php';
  const inp = document.createElement('input');
  inp.type = 'hidden'; inp.name = 'result'; inp.value = JSON.stringify(data);
  form.appendChild(inp);
  document.body.appendChild(form);
  form.submit();
}

// TIMER
function startTimer() {
  const el = document.getElementById('timer');
  const interval = setInterval(() => {
    timeLeft--;
    const m = Math.floor(timeLeft/60).toString().padStart(2,'0');
    const s = (timeLeft%60).toString().padStart(2,'0');
    el.textContent = `${m}:${s}`;
    if (timeLeft <= 300) el.className = 'timer warn';
    if (timeLeft <= 60) el.className = 'timer danger';
    if (timeLeft <= 0) {
      clearInterval(interval);
      autoSubmit();
    }
  }, 1000);
}

function autoSubmit() {
  document.getElementById('timeupModal').classList.add('show');
  setTimeout(() => {
    document.getElementById('autoSubmitBar').style.width = '100%';
  }, 100);
  setTimeout(() => submitExam(), 3200);
}

// Init
renderQuestion(0);
startTimer();
</script>
</body>
</html>
