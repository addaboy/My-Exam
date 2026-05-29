<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}
$name = $_SESSION['name'];
$username = $_SESSION['username'];

$exams = [
    [
        'id'          => 'it_fundamentals',
        'title'       => 'IT Fundamentals',
        'description' => 'Core concepts in information technology, hardware, software, and networking basics.',
        'questions'   => 50,
        'duration'    => 60,
        'difficulty'  => 'Moderate',
        'icon'        => '💻',
        'color'       => '#3b82f6',
    ],
    [
        'id'          => 'networking',
        'title'       => 'Computer Networking',
        'description' => 'TCP/IP, OSI model, protocols, subnetting, and network administration essentials.',
        'questions'   => 50,
        'duration'    => 60,
        'difficulty'  => 'Moderate',
        'icon'        => '🌐',
        'color'       => '#06b6d4',
    ],
    [
        'id'          => 'cybersecurity',
        'title'       => 'Cybersecurity Basics',
        'description' => 'Security threats, firewalls, encryption, best practices, and data protection.',
        'questions'   => 50,
        'duration'    => 60,
        'difficulty'  => 'Moderate',
        'icon'        => '🔐',
        'color'       => '#8b5cf6',
    ],
    [
        'id'          => 'software_installation',
        'title'       => 'Software Installation',
        'description' => 'Core concepts about Softwares, drivers, and application basics.',
        'questions'   => 50,
        'duration'    => 60,
        'difficulty'  => 'Moderate',
        'icon'        => '💻',
        'color'       => '#3b82f6',
    ],
    [
        'id'          => 'sql_basics',
        'title'       => 'Sql Basics',
        'description' => 'Core concepts in databases,SQL,Tables and Relation basics.',
        'questions'   => 50,
        'duration'    => 60,
        'difficulty'  => 'Moderate',
        'icon'        => '💻',
        'color'       => '#3b82f6',
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ExamPro — Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
  --bg:#0a0e1a; --surface:#111827; --border:#1f2d45;
  --accent:#3b82f6; --accent2:#06b6d4; --text:#e2e8f0; --muted:#64748b;
  --grad:linear-gradient(135deg,#3b82f6,#06b6d4);
}
body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }

body::before {
  content:''; position:fixed; inset:0; pointer-events:none;
  background-image:linear-gradient(rgba(59,130,246,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(59,130,246,.03) 1px,transparent 1px);
  background-size:60px 60px;
}

nav {
  position:sticky; top:0; z-index:100;
  background:rgba(10,14,26,.85); backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);
  padding:.75rem 2rem;
  display:flex; align-items:center; justify-content:space-between;
}
.nav-logo { display:flex;align-items:center;gap:.6rem; font-family:'Syne',sans-serif; font-size:1.3rem; font-weight:800; }
.nav-logo-icon { width:36px;height:36px;background:var(--grad);border-radius:10px;display:grid;place-items:center;font-size:.9rem; }
.nav-logo span { background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent; }

.nav-right { display:flex;align-items:center;gap:1rem; }
.user-chip {
  display:flex;align-items:center;gap:.5rem;
  background:rgba(59,130,246,.1); border:1px solid rgba(59,130,246,.2);
  padding:.4rem .9rem; border-radius:999px; font-size:.85rem;
}
.user-avatar {
  width:28px;height:28px;background:var(--grad);border-radius:50%;
  display:grid;place-items:center;font-size:.75rem;font-weight:700;
}
.btn-logout {
  padding:.4rem .9rem; background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.25);
  color:#fca5a5; border-radius:999px; font-size:.8rem; cursor:pointer; text-decoration:none;
  transition:background .2s;
}
.btn-logout:hover { background:rgba(239,68,68,.2); }

.hero {
  padding:4rem 2rem 2rem;
  max-width:1100px; margin:0 auto; text-align:center;
  animation:fadeUp .6s ease;
}
@keyframes fadeUp{ from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }

.hero-badge {
  display:inline-flex;align-items:center;gap:.4rem;
  background:rgba(59,130,246,.1); border:1px solid rgba(59,130,246,.2);
  color:var(--accent2); padding:.35rem .85rem; border-radius:999px;
  font-size:.8rem; font-weight:500; margin-bottom:1.25rem; letter-spacing:.04em;
}
.hero h1 { font-family:'Syne',sans-serif; font-size:clamp(1.8rem,4vw,2.8rem); font-weight:800; line-height:1.2; margin-bottom:.75rem; }
.hero h1 span { background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent; }
.hero p { color:var(--muted); font-size:1rem; max-width:480px; margin:0 auto 3rem; }

.stats-bar {
  display:flex;gap:1.5rem;justify-content:center;flex-wrap:wrap;margin-bottom:3rem;
}
.stat {
  background:rgba(17,24,39,.8); border:1px solid var(--border); border-radius:14px;
  padding:1rem 1.5rem; text-align:center; min-width:120px;
}
.stat-num { font-family:'Syne',sans-serif; font-size:1.6rem; font-weight:800; background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent; }
.stat-label { font-size:.75rem; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; margin-top:.2rem; }

.exams-grid {
  display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
  gap:1.5rem; max-width:1100px; margin:0 auto; padding:0 2rem 4rem;
}

.exam-card {
  background:rgba(17,24,39,.8); border:1px solid var(--border); border-radius:20px;
  padding:2rem; cursor:pointer; position:relative; overflow:hidden;
  transition:transform .25s cubic-bezier(.16,1,.3,1), box-shadow .25s, border-color .25s;
  animation:fadeUp .6s ease both;
}
.exam-card:nth-child(1){animation-delay:.1s}
.exam-card:nth-child(2){animation-delay:.2s}
.exam-card:nth-child(3){animation-delay:.3s}
.exam-card::before {
  content:''; position:absolute; inset:0;
  background:var(--card-grad,linear-gradient(135deg,rgba(59,130,246,.06),transparent));
  opacity:0; transition:opacity .3s;
}
.exam-card:hover { transform:translateY(-6px); box-shadow:0 20px 60px rgba(0,0,0,.4); border-color:rgba(255,255,255,.15); }
.exam-card:hover::before { opacity:1; }

.card-icon {
  width:56px;height:56px;border-radius:16px;font-size:1.6rem;
  display:grid;place-items:center;margin-bottom:1.25rem;
  box-shadow:0 4px 20px rgba(0,0,0,.3);
}
.card-title { font-family:'Syne',sans-serif; font-size:1.2rem; font-weight:700; margin-bottom:.5rem; }
.card-desc { color:var(--muted); font-size:.875rem; line-height:1.6; margin-bottom:1.5rem; }

.card-meta { display:flex; gap:.75rem; flex-wrap:wrap; margin-bottom:1.5rem; }
.badge {
  display:inline-flex;align-items:center;gap:.3rem;
  background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.08);
  padding:.3rem .7rem; border-radius:8px; font-size:.75rem; color:var(--muted);
}

.btn-start {
  width:100%; padding:.875rem;
  background:var(--grad); border:none; border-radius:12px;
  color:#fff; font-family:'Syne',sans-serif; font-size:.95rem; font-weight:700;
  cursor:pointer; letter-spacing:.04em;
  transition:transform .15s, box-shadow .2s;
  box-shadow:0 4px 15px rgba(59,130,246,.25);
}
.btn-start:hover { transform:translateY(-1px); box-shadow:0 6px 25px rgba(59,130,246,.4); }

/* Modal */
.modal-overlay {
  position:fixed;inset:0;z-index:200;
  background:rgba(0,0,0,.7); backdrop-filter:blur(8px);
  display:none; align-items:center; justify-content:center; padding:1rem;
}
.modal-overlay.show { display:flex; }
.modal {
  background:var(--surface); border:1px solid var(--border); border-radius:24px;
  padding:2.5rem; max-width:480px; width:100%;
  box-shadow:0 40px 80px rgba(0,0,0,.5);
  animation:popIn .3s cubic-bezier(.16,1,.3,1);
}
@keyframes popIn{ from{opacity:0;transform:scale(.9)} to{opacity:1;transform:scale(1)} }

.modal h3 { font-family:'Syne',sans-serif; font-size:1.4rem; font-weight:700; margin-bottom:.5rem; }
.modal p { color:var(--muted); font-size:.9rem; line-height:1.7; margin-bottom:1.5rem; }
.modal-info { background:rgba(59,130,246,.07); border:1px solid rgba(59,130,246,.15); border-radius:12px; padding:1rem 1.25rem; margin-bottom:1.5rem; }
.modal-info-row { display:flex;justify-content:space-between;align-items:center; padding:.35rem 0; font-size:.875rem; }
.modal-info-row:not(:last-child) { border-bottom:1px solid rgba(255,255,255,.06); }
.modal-info-row .key { color:var(--muted); }
.modal-info-row .val { font-weight:500; }
.modal-buttons { display:flex; gap:.75rem; }
.btn-cancel { flex:1;padding:.875rem;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:12px;color:var(--muted);cursor:pointer;font-size:.9rem;transition:background .2s; }
.btn-cancel:hover { background:rgba(255,255,255,.08); }
.btn-confirm { flex:2;padding:.875rem;background:var(--grad);border:none;border-radius:12px;color:#fff;font-family:'Syne',sans-serif;font-weight:700;cursor:pointer;font-size:.95rem;transition:transform .15s,box-shadow .2s;box-shadow:0 4px 15px rgba(59,130,246,.3); }
.btn-confirm:hover { transform:translateY(-1px);box-shadow:0 6px 25px rgba(59,130,246,.45); }

@media(max-width:600px){
  nav{padding:.75rem 1rem;}
  .hero{padding:2rem 1rem 1.5rem;}
  .exams-grid{padding:0 1rem 3rem;}
  .stats-bar{gap:1rem;}
}
</style>
</head>
<body>

<nav>
  <div class="nav-logo">
    <div class="nav-logo-icon">📋</div>
    Exam<span>Pro</span>
  </div>
  <div class="nav-right">
    <div class="user-chip">
      <div class="user-avatar"><?= strtoupper(substr($name, 0, 1)) ?></div>
      <?= htmlspecialchars($name) ?>
    </div>
    <a href="logout.php" class="btn-logout">Sign Out</a>
  </div>
</nav>

<div class="hero">
  <div class="hero-badge">🎓 Certification Center</div>
  <h1>Hello, <span><?= htmlspecialchars(explode(' ', $name)[0]) ?></span>!</h1>
  <p>Choose an exam below to begin. Each exam has 50 questions and 60 minutes to complete.</p>

  <div class="stats-bar">
    <div class="stat"><div class="stat-num">50</div><div class="stat-label">Questions</div></div>
    <div class="stat"><div class="stat-num">60</div><div class="stat-label">Minutes</div></div>
    <div class="stat"><div class="stat-num">70%</div><div class="stat-label">Pass Score</div></div>
  </div>
</div>

<div class="exams-grid">
  <?php foreach ($exams as $exam): ?>
  <div class="exam-card" style="--card-grad:linear-gradient(135deg,<?= $exam['color'] ?>18,transparent)"
       onclick="openModal('<?= $exam['id'] ?>', '<?= htmlspecialchars($exam['title'], ENT_QUOTES) ?>', '<?= $exam['questions'] ?>', '<?= $exam['duration'] ?>', '<?= $exam['difficulty'] ?>')">
    <div class="card-icon" style="background:linear-gradient(135deg,<?= $exam['color'] ?>33,<?= $exam['color'] ?>11);border:1px solid <?= $exam['color'] ?>33;">
      <?= $exam['icon'] ?>
    </div>
    <div class="card-title"><?= $exam['title'] ?></div>
    <div class="card-desc"><?= $exam['description'] ?></div>
    <div class="card-meta">
      <span class="badge">📝 <?= $exam['questions'] ?> Questions</span>
      <span class="badge">⏱ <?= $exam['duration'] ?> min</span>
      <span class="badge">📊 <?= $exam['difficulty'] ?></span>
    </div>
    <button class="btn-start">Start Exam →</button>
  </div>
  <?php endforeach; ?>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
  <div class="modal">
    <h3 id="modalTitle">Exam Name</h3>
    <p>Please review the exam details below before starting. Once the exam begins, the timer cannot be paused.</p>
    <div class="modal-info">
      <div class="modal-info-row"><span class="key">👤 Candidate</span><span class="val"><?= htmlspecialchars($name) ?></span></div>
      <div class="modal-info-row"><span class="key">📝 Questions</span><span class="val" id="modalQ">—</span></div>
      <div class="modal-info-row"><span class="key">⏱ Time Limit</span><span class="val" id="modalT">—</span></div>
      <div class="modal-info-row"><span class="key">📊 Difficulty</span><span class="val" id="modalD">—</span></div>
      <div class="modal-info-row"><span class="key">✅ Passing Score</span><span class="val">70% (35/50)</span></div>
    </div>
    <div class="modal-buttons">
      <button class="btn-cancel" onclick="document.getElementById('modalOverlay').classList.remove('show')">Cancel</button>
      <button class="btn-confirm" id="modalConfirm" onclick="startExam()">Start Exam 🚀</button>
    </div>
  </div>
</div>

<script>
let selectedExamId = '';
function openModal(id, title, q, t, d) {
  selectedExamId = id;
  document.getElementById('modalTitle').textContent = title;
  document.getElementById('modalQ').textContent = q + ' Questions';
  document.getElementById('modalT').textContent = t + ' Minutes';
  document.getElementById('modalD').textContent = d;
  document.getElementById('modalOverlay').classList.add('show');
}
function closeModal(e) {
  if (e.target === document.getElementById('modalOverlay'))
    document.getElementById('modalOverlay').classList.remove('show');
}
function startExam() {
  window.location.href = 'exam.php?exam=' + selectedExamId;
}
</script>
</body>
</html>
