<?php
require_once __DIR__ . '/../includes/auth.php';
session_start_safe();
if (!empty($_SESSION['member_id'])) { header('Location: /members/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (member_login($email, $pass)) {
        $next = $_GET['next'] ?? '/members/dashboard.php';
        header('Location: ' . $next); exit;
    }
    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Member Login | NEX AUTO GEAR</title>
  <meta name="robots" content="noindex"/>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>body{background:#081425;color:#e8edf2;font-family:'Inter',sans-serif;} input{background:#122440;border:1px solid #1e3455;color:#e8edf2;outline:none;} input:focus{border-color:#ffd165;} input::placeholder{color:#4a6278;}</style>
</head>
<body class="min-h-screen flex items-center justify-center px-4">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <a href="/" class="inline-block mb-6">
        <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.25rem;letter-spacing:-0.02em;">
          <span style="color:#e8edf2">NEX</span><span style="color:#ffd165">AUTO</span><span style="color:#e8edf2">GEAR</span>
        </span>
      </a>
      <h1 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.5rem;" class="mb-2">B2B Member Login</h1>
      <p style="color:#8da0b3;font-size:0.875rem;">Access your wholesale account</p>
    </div>

    <?php if ($error): ?>
    <div class="bg-red-900/20 border border-red-500/40 text-red-400 px-4 py-3 text-sm font-mono mb-6"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4 bg-surface-container p-8 border" style="background:#122440;border-color:#1e3455;">
      <div>
        <label class="block text-xs font-mono uppercase tracking-widest mb-2" style="color:#8da0b3;">Email</label>
        <input type="email" name="email" required placeholder="you@company.com" class="w-full px-4 py-3 text-sm transition-colors"/>
      </div>
      <div>
        <label class="block text-xs font-mono uppercase tracking-widest mb-2" style="color:#8da0b3;">Password</label>
        <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 text-sm transition-colors"/>
      </div>
      <button type="submit" class="w-full py-4 text-sm font-mono font-bold uppercase tracking-widest transition-all" style="background:#ffd165;color:#081425;">
        Sign In →
      </button>
    </form>

    <div class="mt-6 text-center space-y-3">
      <p style="color:#8da0b3;font-size:0.8rem;">Don't have an account? <a href="/members/register.php" style="color:#ffd165;">Apply for B2B Access</a></p>
      <p style="color:#8da0b3;font-size:0.8rem;">Need help? <a href="mailto:Support@nexautogear.com" style="color:#8da0b3;text-decoration:underline;">Support@nexautogear.com</a></p>
    </div>
  </div>
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/6a2d7d92d6a95f1c2c58ca23/1jr0r50oo';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
</body>
</html>

