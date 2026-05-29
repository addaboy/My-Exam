<?php
session_start();
if (!isset($_SESSION['username'])) { header('Location: index.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['result'])) { header('Location: dashboard.php'); exit; }

$data = json_decode($_POST['result'], true);
if (!$data) { header('Location: dashboard.php'); exit; }

// Calculate score server-side
$answers = $data['answers'];
$correct = $data['correctAnswers'];
$total = count($correct);
$score = 0;
for($i=0;$i<$total;$i++){
    if(isset($answers[$i]) && $answers[$i] == $correct[$i]) $score++;
}
$pct = round(($score/$total)*100);
$pass = $pct >= 70;
$timeTaken = $data['timeTaken'];
$mins = floor($timeTaken/60);
$secs = $timeTaken%60;

$_SESSION['last_result'] = [
    'score'      => $score,
    'total'      => $total,
    'pct'        => $pct,
    'pass'       => $pass,
    'examTitle'  => $data['examTitle'],
    'timeTaken'  => $timeTaken,
];

$name = $_SESSION['name'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ExamPro — Confirm Submission</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing:border-box;margin:0;padding:0; }
:root {
  --bg:#0a0e1a;--surface:#111827;--border:#1f2d45;
  --accent:#3b82f6;--accent2:#06b6d4;--text:#e2e8f0;--muted:#64748b;
  --success:#22c55e;--danger:#ef4444;
  --grad:linear-gradient(135deg,#3b82f6,#06b6d4);
}
body {
  font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text);
  min-height:100vh; display:flex; align-items:center; justify-content:center;
  padding:1.5rem;
}
body::before {
  content:'';position:fixed;inset:0;pointer-events:none;
  background-image:linear-gradient(rgba(59,130,246,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(59,130,246,.03) 1px,transparent 1px);
  background-size:60px 60px;
}
.container {
  position:relative;z-index:1;
  max-width:560px;width:100%;
  background:rgba(17,24,39,.9); border:1px solid var(--border); border-radius:24px;
  padding:3rem 2.5rem; text-align:center;
  box-shadow:0 40px 80px rgba(0,0,0,.4);
  animation:slideUp .6s cubic-bezier(.16,1,.3,1);
}
@keyframes slideUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}

.icon-wrap {
  width:80px;height:80px;border-radius:50%;
  background:rgba(59,130,246,.12); border:2px solid rgba(59,130,246,.3);
  display:grid;place-items:center;font-size:2.2rem;margin:0 auto 1.5rem;
  animation:iconPop .5s cubic-bezier(.16,1,.3,1) .2s both;
}
@keyframes iconPop{from{opacity:0;transform:scale(.5)}to{opacity:1;transform:scale(1)}}

h2 { font-family:'Syne',sans-serif; font-size:1.6rem; font-weight:800; margin-bottom:.5rem; }
.subtitle { color:var(--muted); font-size:.9rem; margin-bottom:2rem; line-height:1.6; }

.info-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem; }
.info-item {
  background:rgba(255,255,255,.04); border:1px solid var(--border); border-radius:14px;
  padding:1rem;
}
.info-item .label { font-size:.7rem; color:var(--muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:.4rem; }
.info-item .value { font-family:'Syne',sans-serif; font-size:1rem; font-weight:700; }

.warning-box {
  background:rgba(245,158,11,.08); border:1px solid rgba(245,158,11,.25);
  border-radius:12px; padding:1rem 1.25rem; margin-bottom:2rem;
  font-size:.85rem; color:#fbbf24; line-height:1.6;
}

.buttons { display:flex; gap:.75rem; }
.btn-back {
  flex:1;padding:.875rem;background:rgba(255,255,255,.05);border:1px solid var(--border);
  border-radius:12px;color:var(--muted);cursor:pointer;font-size:.9rem;
  text-decoration:none;display:grid;place-items:center;transition:background .2s;
}
.btn-back:hover { background:rgba(255,255,255,.08); }
.btn-confirm {
  flex:2;padding:.875rem;background:var(--grad);border:none;border-radius:12px;
  color:#fff;font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;
  cursor:pointer;transition:transform .15s,box-shadow .2s;
  box-shadow:0 4px 15px rgba(59,130,246,.3);
}
.btn-confirm:hover { transform:translateY(-2px);box-shadow:0 8px 25px rgba(59,130,246,.45); }
</style>
</head>
<body>
<div class="container">
  <div class="icon-wrap">📤</div>
  <h2>Confirm Submission</h2>
  <p class="subtitle">
    You're about to submit your exam for <strong><?= htmlspecialchars($data['examTitle']) ?></strong>.<br>
    This action cannot be undone.
  </p>

  <div class="info-grid">
    <div class="info-item">
      <div class="label">👤 Candidate</div>
      <div class="value"><?= htmlspecialchars($name) ?></div>
    </div>
    <div class="info-item">
      <div class="label">📋 Exam</div>
      <div class="value" style="font-size:.875rem"><?= htmlspecialchars($data['examTitle']) ?></div>
    </div>
    <div class="info-item">
      <div class="label">📝 Answered</div>
      <div class="value"><?= count(array_filter($answers, fn($a)=>$a!==null)) ?> / <?= $total ?></div>
    </div>
    <div class="info-item">
      <div class="label">⏱ Time Taken</div>
      <div class="value"><?= $mins ?>m <?= $secs ?>s</div>
    </div>
  </div>

  <?php if(count(array_filter($answers,fn($a)=>$a===null)) > 0): ?>
  <div class="warning-box">
    ⚠️ You have <strong><?= count(array_filter($answers,fn($a)=>$a===null)) ?></strong> unanswered questions. 
    Unanswered questions will be marked as incorrect.
  </div>
  <?php endif; ?>

  <div class="buttons">
    <a href="javascript:history.back()" class="btn-back">← Go Back</a>
    <form method="POST" action="result.php" style="flex:2;display:flex">
      <input type="hidden" name="score" value="<?= $score ?>">
      <input type="hidden" name="total" value="<?= $total ?>">
      <input type="hidden" name="pct" value="<?= $pct ?>">
      <input type="hidden" name="pass" value="<?= $pass?1:0 ?>">
      <input type="hidden" name="timeTaken" value="<?= $timeTaken ?>">
      <button type="submit" class="btn-confirm" style="width:100%">Confirm & View Results →</button>
    </form>
  </div>
</div>
</body>
</html>
