<?php
require_once __DIR__ . '/../includes/auth.php';
session_start_safe();
if (!empty($_SESSION['admin_id'])) { header('Location: /admin/'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (admin_login(trim($_POST['username'] ?? ''), $_POST['password'] ?? '')) {
        header('Location: /admin/'); exit;
    }
    $error = 'Invalid credentials.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login | NEX AUTO GEAR</title>
  <meta name="robots" content="noindex, nofollow"/>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0;}
    body{background:#081425;color:#e8edf2;font-family:'JetBrains Mono',monospace;min-height:100vh;display:flex;align-items:center;justify-content:center;}
    input{background:#122440;border:1px solid #1e3455;color:#e8edf2;padding:12px 16px;width:100%;font-family:inherit;font-size:.875rem;outline:none;}
    input:focus{border-color:#ffd165;}
    label{font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;color:#8da0b3;display:block;margin-bottom:6px;}
    button{width:100%;padding:14px;background:#ffd165;color:#081425;font-family:inherit;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;border:none;cursor:pointer;}
  </style>
</head>
<body>
  <div style="width:100%;max-width:360px;padding:16px;">
    <p style="color:#ffd165;font-weight:700;font-size:1.1rem;margin-bottom:32px;font-family:'Space Grotesk',sans-serif;">NEX ADMIN</p>
    <?php if ($error): ?><p style="color:#ef4444;font-size:.8rem;margin-bottom:16px;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="POST" style="display:flex;flex-direction:column;gap:16px;">
      <div><label>Username</label><input type="text" name="username" required autofocus/></div>
      <div><label>Password</label><input type="password" name="password" required/></div>
      <button type="submit">Sign In →</button>
    </form>
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

