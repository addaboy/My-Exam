<?php
session_start();
if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Demo users
    $users = [
        'john_doe'    => ['password' => 'pass123',  'name' => 'John Doe'],
        'jane_smith'  => ['password' => 'pass456',  'name' => 'Jane Smith'],
        'alex_jones'  => ['password' => 'pass789',  'name' => 'Alex Jones'],
        'maria_garcia'=> ['password' => 'passabc',  'name' => 'Maria Garcia'],
        'demo'        => ['password' => 'demo',     'name' => 'Demo User'],
    ];

    if (isset($users[$username]) && $users[$username]['password'] === $password) {
        $_SESSION['username'] = $username;
        $_SESSION['name']     = $users[$username]['name'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ExamPro — Login</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg:       #0a0e1a;
  --surface:  #111827;
  --border:   #1f2d45;
  --accent:   #3b82f6;
  --accent2:  #06b6d4;
  --text:     #e2e8f0;
  --muted:    #64748b;
  --danger:   #ef4444;
  --grad:     linear-gradient(135deg, #3b82f6, #06b6d4);
}

body {
  font-family: 'DM Sans', sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
}

/* Animated background grid */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background-image:
    linear-gradient(rgba(59,130,246,.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(59,130,246,.04) 1px, transparent 1px);
  background-size: 60px 60px;
  animation: gridMove 20s linear infinite;
}
@keyframes gridMove { from{background-position:0 0} to{background-position:60px 60px} }

/* Glowing orbs */
.orb {
  position: fixed;
  border-radius: 50%;
  filter: blur(80px);
  opacity: .35;
  animation: float 8s ease-in-out infinite;
}
.orb1 { width:400px;height:400px;background:#3b82f6;top:-100px;left:-100px;animation-delay:0s; }
.orb2 { width:300px;height:300px;background:#06b6d4;bottom:-80px;right:-80px;animation-delay:3s; }
.orb3 { width:200px;height:200px;background:#8b5cf6;top:50%;left:50%;transform:translate(-50%,-50%);animation-delay:6s; }
@keyframes float {
  0%,100%{transform:translateY(0) scale(1)}
  50%{transform:translateY(-30px) scale(1.05)}
}

.card {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 440px;
  margin: 1rem;
  background: rgba(17,24,39,.85);
  border: 1px solid var(--border);
  border-radius: 24px;
  padding: 3rem 2.5rem;
  backdrop-filter: blur(20px);
  box-shadow: 0 0 80px rgba(59,130,246,.1), 0 32px 64px rgba(0,0,0,.4);
  animation: slideUp .6s cubic-bezier(.16,1,.3,1);
}
@keyframes slideUp {
  from{opacity:0;transform:translateY(30px)}
  to{opacity:1;transform:translateY(0)}
}

.logo {
  display: flex;
  align-items: center;
  gap: .75rem;
  margin-bottom: 2.5rem;
}
.logo-icon {
  width: 48px; height: 48px;
  background: var(--grad);
  border-radius: 14px;
  display: grid;
  place-items: center;
  font-size: 1.4rem;
  box-shadow: 0 0 20px rgba(59,130,246,.4);
}
.logo-text { font-family:'Syne',sans-serif; font-size:1.6rem; font-weight:800; }
.logo-text span { background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent; }

h2 { font-family:'Syne',sans-serif; font-size:1.6rem; font-weight:700; margin-bottom:.5rem; }
.subtitle { color:var(--muted); font-size:.9rem; margin-bottom:2rem; }

.field { margin-bottom:1.25rem; }
label { display:block; font-size:.8rem; font-weight:500; color:var(--muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:.5rem; }

.input-wrap { position:relative; }
.input-icon {
  position:absolute; left:1rem; top:50%; transform:translateY(-50%);
  color:var(--muted); font-size:1rem; pointer-events:none;
}
input {
  width:100%;
  padding:.875rem 1rem .875rem 2.75rem;
  background:rgba(255,255,255,.04);
  border:1px solid var(--border);
  border-radius:12px;
  color:var(--text);
  font-family:'DM Sans',sans-serif;
  font-size:.95rem;
  outline:none;
  transition:border-color .2s, box-shadow .2s, background .2s;
}
input:focus {
  border-color:var(--accent);
  background:rgba(59,130,246,.07);
  box-shadow:0 0 0 3px rgba(59,130,246,.15);
}
input::placeholder { color:var(--muted); }

.toggle-pw {
  position:absolute;right:1rem;top:50%;transform:translateY(-50%);
  background:none;border:none;color:var(--muted);cursor:pointer;font-size:1rem;
  transition:color .2s;
}
.toggle-pw:hover { color:var(--text); }

.error-msg {
  background:rgba(239,68,68,.1);
  border:1px solid rgba(239,68,68,.3);
  color:#fca5a5;
  padding:.75rem 1rem;
  border-radius:10px;
  font-size:.85rem;
  margin-bottom:1.25rem;
  display:flex;align-items:center;gap:.5rem;
}

.btn-login {
  width:100%;
  padding:1rem;
  background:var(--grad);
  border:none;
  border-radius:12px;
  color:#fff;
  font-family:'Syne',sans-serif;
  font-size:1rem;
  font-weight:700;
  letter-spacing:.04em;
  cursor:pointer;
  position:relative;
  overflow:hidden;
  transition:transform .15s, box-shadow .2s;
  box-shadow:0 4px 20px rgba(59,130,246,.35);
  margin-top:.5rem;
}
.btn-login:hover { transform:translateY(-2px); box-shadow:0 8px 30px rgba(59,130,246,.45); }
.btn-login:active { transform:translateY(0); }
.btn-login::after {
  content:'';position:absolute;inset:0;
  background:linear-gradient(rgba(255,255,255,.15),transparent);
}

.demo-hint {
  margin-top:2rem;
  padding:1rem;
  background:rgba(59,130,246,.06);
  border:1px solid rgba(59,130,246,.15);
  border-radius:12px;
  font-size:.8rem;
  color:var(--muted);
}
.demo-hint strong { color:var(--accent2); }
</style>
</head>
<body>
<div class="orb orb1"></div>
<div class="orb orb2"></div>
<div class="orb orb3"></div>

<div class="card">
  <div class="logo">
    <div class="logo-icon">📋</div>
    <div class="logo-text">Exam<span>Pro</span></div>
  </div>

  <h2>Welcome Back</h2>
  <p class="subtitle">Sign in to access your exams</p>

  <?php if ($error): ?>
  <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="field">
      <label>Username</label>
      <div class="input-wrap">
        <span class="input-icon">👤</span>
        <input type="text" name="username" placeholder="Enter your username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autocomplete="username">
      </div>
    </div>

    <div class="field">
      <label>Password</label>
      <div class="input-wrap">
        <span class="input-icon">🔒</span>
        <input type="password" name="password" id="pwField" placeholder="Enter your password" required autocomplete="current-password">
        <button type="button" class="toggle-pw" onclick="togglePw()" id="toggleBtn">👁</button>
      </div>
    </div>

    <button type="submit" class="btn-login">Sign In →</button>
  </form>

  <div class="demo-hint">
    🧪 <strong>Demo credentials:</strong> username <strong>demo</strong> / password <strong>demo</strong>
  </div>
</div>

<script>
function togglePw(){
  const f=document.getElementById('pwField');
  const b=document.getElementById('toggleBtn');
  if(f.type==='password'){f.type='text';b.textContent='🙈';}
  else{f.type='password';b.textContent='👁';}
}
</script>
</body>
</html>
