<?php
session_start();
if (!isset($_SESSION['username'])) { header('Location: index.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard.php'); exit; }

$score     = (int)$_POST['score'];
$total     = (int)$_POST['total'];
$pct       = (int)$_POST['pct'];
$pass      = (bool)$_POST['pass'];
$timeTaken = (int)$_POST['timeTaken'];
$examTitle = $_SESSION['last_result']['examTitle'] ?? 'Exam';
$name      = $_SESSION['name'];
$username  = $_SESSION['username'];

$mins = floor($timeTaken/60);
$secs = $timeTaken%60;
$grade = $pct >= 90 ? 'A' : ($pct >= 80 ? 'B' : ($pct >= 70 ? 'C' : ($pct >= 60 ? 'D' : 'F')));
$gradeColor = $pct>=70 ? '#22c55e' : '#ef4444';
$passBg = $pass ? 'rgba(34,197,94,.1)' : 'rgba(239,68,68,.1)';
$passBorder = $pass ? 'rgba(34,197,94,.3)' : 'rgba(239,68,68,.3)';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ExamPro — Results</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing:border-box;margin:0;padding:0; }
:root {
  --bg:#0a0e1a;--surface:#111827;--border:#1f2d45;
  --accent:#3b82f6;--accent2:#06b6d4;--text:#e2e8f0;--muted:#64748b;
  --success:#22c55e;--danger:#ef4444;
  --grad:linear-gradient(135deg,#3b82f6,#06b6d4);
}
body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; padding:2rem 1rem 4rem; }

body::before {
  content:'';position:fixed;inset:0;pointer-events:none;
  background-image:linear-gradient(rgba(59,130,246,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(59,130,246,.03) 1px,transparent 1px);
  background-size:60px 60px;
}

.page { position:relative;z-index:1;max-width:700px;margin:0 auto; }

/* HERO RESULT */
.result-hero {
  text-align:center; padding:3rem 2rem 2rem;
  background:rgba(17,24,39,.9); border:1px solid var(--border); border-radius:24px;
  margin-bottom:1.5rem;
  box-shadow:0 20px 60px rgba(0,0,0,.3);
  animation:slideUp .6s cubic-bezier(.16,1,.3,1);
}
@keyframes slideUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}

.result-badge {
  display:inline-flex;align-items:center;gap:.5rem;
  padding:.4rem 1rem; border-radius:999px; font-size:.85rem; font-weight:600;
  margin-bottom:1.5rem;
  background:<?= $passBg ?>; border:1px solid <?= $passBorder ?>;
  color:<?= $pass?'#4ade80':'#fca5a5' ?>;
  animation:badgePop .5s cubic-bezier(.16,1,.3,1) .3s both;
}
@keyframes badgePop{from{opacity:0;transform:scale(.7)}to{opacity:1;transform:scale(1)}}

.score-ring {
  position:relative; width:160px; height:160px; margin:0 auto 1.5rem;
}
.score-ring svg { width:100%;height:100%;transform:rotate(-90deg); }
.ring-bg { fill:none; stroke:var(--border); stroke-width:10; }
.ring-fill { fill:none; stroke-width:10; stroke-linecap:round; transition:stroke-dashoffset 1.5s cubic-bezier(.16,1,.3,1); }
.score-inner {
  position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
  text-align:center;
}
.score-pct { font-family:'Syne',sans-serif; font-size:2.2rem; font-weight:800; color:<?= $gradeColor ?>; }
.score-label { font-size:.7rem; color:var(--muted); text-transform:uppercase; letter-spacing:.08em; }

.result-name { font-family:'Syne',sans-serif; font-size:1.5rem; font-weight:800; margin-bottom:.4rem; }
.result-name span { background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent; }
.result-exam { color:var(--muted); font-size:.9rem; margin-bottom:2rem; }

.stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; }
.r-stat { background:rgba(255,255,255,.04); border:1px solid var(--border); border-radius:14px; padding:1rem .75rem; text-align:center; }
.r-stat-num { font-family:'Syne',sans-serif; font-size:1.4rem; font-weight:800; }
.r-stat-label { font-size:.65rem; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; margin-top:.3rem; }

/* PERFORMANCE BARS */
.section {
  background:rgba(17,24,39,.9); border:1px solid var(--border); border-radius:20px;
  padding:1.75rem; margin-bottom:1.5rem;
  animation:slideUp .6s cubic-bezier(.16,1,.3,1) .2s both;
}
.section h3 { font-family:'Syne',sans-serif; font-size:1.05rem; font-weight:700; margin-bottom:1.25rem; display:flex;align-items:center;gap:.5rem; }

.perf-bar-wrap { margin-bottom:1.25rem; }
.perf-bar-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;font-size:.85rem; }
.perf-bar-label { color:var(--muted); }
.perf-bar-val { font-weight:600; }
.perf-bar-track { height:10px; background:rgba(255,255,255,.06); border-radius:99px; overflow:hidden; }
.perf-bar-fill { height:100%; border-radius:99px; transition:width 1.2s cubic-bezier(.16,1,.3,1); width:0%; }

/* MESSAGE */
.message-box {
  border-radius:16px; padding:1.5rem; text-align:center;
  background:<?= $passBg ?>; border:1px solid <?= $passBorder ?>;
  margin-bottom:1.5rem;
  animation:slideUp .6s cubic-bezier(.16,1,.3,1) .3s both;
}
.message-icon { font-size:2.5rem; margin-bottom:.75rem; }
.message-title { font-family:'Syne',sans-serif; font-size:1.3rem; font-weight:800; color:<?= $pass?'#4ade80':'#fca5a5' ?>; margin-bottom:.4rem; }
.message-text { color:var(--muted); font-size:.9rem; line-height:1.6; }

/* ACTIONS */
.actions { display:flex; gap:.75rem; flex-wrap:wrap; animation:slideUp .6s cubic-bezier(.16,1,.3,1) .4s both; }
.btn-action {
  flex:1;min-width:140px;padding:.875rem 1.5rem;border-radius:12px;
  font-family:'Syne',sans-serif;font-size:.9rem;font-weight:700;letter-spacing:.03em;
  cursor:pointer;transition:all .2s;text-decoration:none;text-align:center;display:grid;place-items:center;
}
.btn-primary { background:var(--grad);border:none;color:#fff;box-shadow:0 4px 15px rgba(59,130,246,.3); }
.btn-primary:hover { transform:translateY(-2px);box-shadow:0 8px 25px rgba(59,130,246,.45); }
.btn-secondary { background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--text); }
.btn-secondary:hover { background:rgba(255,255,255,.08); }

/* Confetti container */
#confetti { position:fixed;inset:0;pointer-events:none;z-index:999;overflow:hidden; }
.confetti-piece {
  position:absolute;width:10px;height:10px;border-radius:2px;
  animation:confettiFall linear forwards;
}
@keyframes confettiFall {
  0%{opacity:1;transform:translateY(-20px) rotate(0deg)}
  100%{opacity:0;transform:translateY(100vh) rotate(720deg)}
}

@media(max-width:500px){
  .stats-row { grid-template-columns:repeat(2,1fr); }
  .result-hero { padding:2rem 1.25rem 1.5rem; }
  .section { padding:1.25rem; }
}
</style>
</head>
<body>

<?php if($pass): ?>
<div id="confetti"></div>
<?php endif; ?>

<div class="page">
  <!-- HERO -->
  <div class="result-hero">
    <div class="result-badge">
      <?= $pass ? '🏆 PASSED' : '❌ FAILED' ?>
    </div>

    <div class="score-ring">
      <svg viewBox="0 0 160 160">
        <circle class="ring-bg" cx="80" cy="80" r="70"/>
        <circle class="ring-fill" cx="80" cy="80" r="70"
          stroke="<?= $pass?'#22c55e':'#ef4444' ?>"
          stroke-dasharray="<?= 2*M_PI*70 ?>"
          stroke-dashoffset="<?= 2*M_PI*70 ?>"
          id="ringFill"/>
      </svg>
      <div class="score-inner">
        <div class="score-pct"><?= $pct ?>%</div>
        <div class="score-label">Score</div>
      </div>
    </div>

    <div class="result-name">
      <span><?= htmlspecialchars($name) ?></span>
    </div>
    <div class="result-exam">📋 <?= htmlspecialchars($examTitle) ?></div>

    <div class="stats-row">
      <div class="r-stat">
        <div class="r-stat-num" style="color:#4ade80"><?= $score ?></div>
        <div class="r-stat-label">Correct</div>
      </div>
      <div class="r-stat">
        <div class="r-stat-num" style="color:#f87171"><?= $total-$score ?></div>
        <div class="r-stat-label">Incorrect</div>
      </div>
      <div class="r-stat">
        <div class="r-stat-num" style="color:var(--accent2)"><?= $grade ?></div>
        <div class="r-stat-label">Grade</div>
      </div>
      <div class="r-stat">
        <div class="r-stat-num" style="color:#fbbf24"><?= $mins ?>:<?= str_pad($secs,2,'0',STR_PAD_LEFT) ?></div>
        <div class="r-stat-label">Time</div>
      </div>
    </div>
  </div>

  <!-- PERFORMANCE -->
  <div class="section">
    <h3>📊 Performance Breakdown</h3>

    <div class="perf-bar-wrap">
      <div class="perf-bar-header">
        <span class="perf-bar-label">Score</span>
        <span class="perf-bar-val"><?= $score ?>/<?= $total ?></span>
      </div>
      <div class="perf-bar-track">
        <div class="perf-bar-fill" style="background:<?= $pass?'#22c55e':'#ef4444' ?>" data-width="<?= $pct ?>"></div>
      </div>
    </div>

    <div class="perf-bar-wrap">
      <div class="perf-bar-header">
        <span class="perf-bar-label">Passing Threshold</span>
        <span class="perf-bar-val">70% (35/50)</span>
      </div>
      <div class="perf-bar-track">
        <div class="perf-bar-fill" style="background:#3b82f6" data-width="70"></div>
      </div>
    </div>

    <div class="perf-bar-wrap">
      <div class="perf-bar-header">
        <span class="perf-bar-label">Time Used</span>
        <span class="perf-bar-val"><?= $mins ?>m <?= $secs ?>s / 60m</span>
      </div>
      <div class="perf-bar-track">
        <div class="perf-bar-fill" style="background:#8b5cf6" data-width="<?= min(100,round($timeTaken/3600*100)) ?>"></div>
      </div>
    </div>
  </div>

  <!-- MESSAGE -->
  <div class="message-box">
    <div class="message-icon"><?= $pass?'🎉':'📚' ?></div>
    <div class="message-title"><?= $pass?'Congratulations!':'Keep Practicing!' ?></div>
    <div class="message-text">
      <?php if($pass): ?>
        You scored <?= $pct ?>% and successfully passed the <?= htmlspecialchars($examTitle) ?> exam.
        Your grade is <strong><?= $grade ?></strong>. Excellent work, <?= htmlspecialchars(explode(' ',$name)[0]) ?>!
      <?php else: ?>
        You scored <?= $pct ?>% and need 70% to pass. You were <?= 70-$pct ?>% away from passing.
        Review the material and try again — you've got this!
      <?php endif; ?>
    </div>
  </div>

  <!-- ACTIONS -->
  <div class="actions">
    <a href="dashboard.php" class="btn-action btn-secondary">← Dashboard</a>
    <a href="dashboard.php" class="btn-action btn-primary">🔄 Retake Exam</a>
  </div>
</div>

<script>
// Animate ring
const circumference = 2 * Math.PI * 70;
const pct = <?= $pct ?>;
const offset = circumference - (pct/100)*circumference;
setTimeout(()=>{
  document.getElementById('ringFill').style.strokeDashoffset = offset;
},300);

// Animate bars
setTimeout(()=>{
  document.querySelectorAll('.perf-bar-fill').forEach(b=>{
    b.style.width = b.dataset.width+'%';
  });
},400);

<?php if($pass): ?>
// Confetti
const colors = ['#3b82f6','#06b6d4','#22c55e','#f59e0b','#8b5cf6','#ec4899'];
const container = document.getElementById('confetti');
for(let i=0;i<80;i++){
  const p = document.createElement('div');
  p.className = 'confetti-piece';
  p.style.cssText = `
    left:${Math.random()*100}vw;
    background:${colors[Math.floor(Math.random()*colors.length)]};
    animation-duration:${2+Math.random()*3}s;
    animation-delay:${Math.random()*2}s;
    transform:scale(${.5+Math.random()});
  `;
  container.appendChild(p);
}
setTimeout(()=>{ container.innerHTML=''; },6000);
<?php endif; ?>
</script>
</body>
</html>
