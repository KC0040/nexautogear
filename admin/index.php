<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'approve' && $id) {
        $stmt = db()->prepare('SELECT * FROM b2b_applications WHERE id = ? AND status = "pending"');
        $stmt->execute([$id]);
        $app = $stmt->fetch();
        if ($app) {
            // Generate temp password
            $tempPass = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789'), 0, 10);
            $hash = password_hash($tempPass, PASSWORD_BCRYPT);

            db()->prepare('INSERT INTO b2b_members (application_id,company,contact_name,email,password_hash,country) VALUES (?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE status="active"')
                ->execute([$app['id'],$app['company'],$app['contact_name'],$app['email'],$hash,$app['country']]);

            db()->prepare('UPDATE b2b_applications SET status="approved", reviewed_at=NOW(), reviewed_by=? WHERE id=?')
                ->execute([$_SESSION['admin_name'], $id]);

            // Send credentials email
            $body = "Hello {$app['contact_name']},\n\nYour NEXAutogear B2B account has been approved!\n\n";
            $body .= "Login URL: https://www.nexautogear.com/members/login.php\n";
            $body .= "Email:     {$app['email']}\n";
            $body .= "Password:  $tempPass\n\n";
            $body .= "Please change your password after first login.\n\nWelcome to NEXAutogear B2B,\nSales Team";
            mail($app['email'], 'Your NEXAutogear B2B Account is Approved', $body,
                "From: Sales@nexautogear.com\r\nReply-To: Sales@nexautogear.com");

            $flash = "✓ {$app['company']} approved. Credentials sent to {$app['email']}.";
        }
    } elseif ($action === 'reject' && $id) {
        db()->prepare('UPDATE b2b_applications SET status="rejected", reviewed_at=NOW(), reviewed_by=? WHERE id=?')
            ->execute([$_SESSION['admin_name'], $id]);
        $flash = "Application rejected.";
    }
}

$pending  = db()->query('SELECT * FROM b2b_applications WHERE status="pending" ORDER BY created_at DESC')->fetchAll();
$approved = db()->query('SELECT * FROM b2b_applications WHERE status="approved" ORDER BY reviewed_at DESC LIMIT 20')->fetchAll();
$members  = db()->query('SELECT * FROM b2b_members ORDER BY created_at DESC')->fetchAll();
$orders   = db()->query('SELECT o.*, m.company FROM b2b_orders o JOIN b2b_members m ON o.member_id=m.id ORDER BY o.created_at DESC LIMIT 30')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin | NEX AUTO GEAR</title>
  <meta name="robots" content="noindex, nofollow"/>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body{background:#081425;color:#e8edf2;font-family:'Inter',sans-serif;}
    .tab-btn{padding:8px 20px;font-size:.75rem;font-family:'JetBrains Mono',monospace;text-transform:uppercase;letter-spacing:.06em;color:#8da0b3;border-bottom:2px solid transparent;cursor:pointer;}
    .tab-btn.active{color:#ffd165;border-color:#ffd165;}
    .panel{display:none;} .panel.active{display:block;}
  </style>
</head>
<body class="min-h-screen">

<header class="h-14 border-b flex items-center justify-between px-6 sticky top-0 z-50" style="background:#081425;border-color:#1e3455;">
  <div class="flex items-center gap-3">
    <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;color:#ffd165;">NEX</span>
    <span class="text-xs font-mono px-2 py-1" style="background:#ffd16520;color:#ffd165;border:1px solid #ffd16540;">ADMIN</span>
  </div>
  <div class="flex items-center gap-4">
    <span class="text-xs font-mono" style="color:#8da0b3;"><?= htmlspecialchars($_SESSION['admin_name']) ?></span>
    <a href="/admin/logout.php" class="text-xs font-mono" style="color:#8da0b3;">Sign Out</a>
  </div>
</header>

<div class="max-w-6xl mx-auto px-6 py-8">

  <?php if (!empty($flash)): ?>
  <div class="mb-6 px-4 py-3 border text-sm font-mono" style="border-color:#ffd165;background:#ffd16515;color:#ffd165;"><?= htmlspecialchars($flash) ?></div>
  <?php endif; ?>

  <!-- STATS BAR -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="p-4 border" style="background:#122440;border-color:#1e3455;">
      <p class="text-xs font-mono uppercase mb-1" style="color:#8da0b3;">Pending</p>
      <p style="font-family:'Space Grotesk',sans-serif;font-size:1.75rem;font-weight:700;color:#f59e0b;"><?= count($pending) ?></p>
    </div>
    <div class="p-4 border" style="background:#122440;border-color:#1e3455;">
      <p class="text-xs font-mono uppercase mb-1" style="color:#8da0b3;">Members</p>
      <p style="font-family:'Space Grotesk',sans-serif;font-size:1.75rem;font-weight:700;color:#ffd165;"><?= count($members) ?></p>
    </div>
    <div class="p-4 border" style="background:#122440;border-color:#1e3455;">
      <p class="text-xs font-mono uppercase mb-1" style="color:#8da0b3;">Orders</p>
      <p style="font-family:'Space Grotesk',sans-serif;font-size:1.75rem;font-weight:700;"><?= count($orders) ?></p>
    </div>
    <div class="p-4 border" style="background:#122440;border-color:#1e3455;">
      <p class="text-xs font-mono uppercase mb-1" style="color:#8da0b3;">Active Orders</p>
      <p style="font-family:'Space Grotesk',sans-serif;font-size:1.75rem;font-weight:700;color:#10b981;"><?= count(array_filter($orders, fn($o)=>in_array($o['status'],['processing','shipped']))) ?></p>
    </div>
  </div>

  <!-- TABS -->
  <div class="flex border-b mb-6" style="border-color:#1e3455;">
    <button class="tab-btn active" onclick="showPanel('applications')">Applications <?php if(count($pending)): ?><span style="background:#f59e0b;color:#000;border-radius:9999px;padding:1px 6px;font-size:.65rem;margin-left:4px;"><?= count($pending) ?></span><?php endif; ?></button>
    <button class="tab-btn" onclick="showPanel('members')">Members</button>
    <button class="tab-btn" onclick="showPanel('orders')">Orders</button>
  </div>

  <!-- APPLICATIONS -->
  <div id="panel-applications" class="panel active">
    <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;" class="uppercase mb-4">Pending Applications</h2>
    <?php if (empty($pending)): ?>
    <p style="color:#8da0b3;font-size:.875rem;">No pending applications.</p>
    <?php else: ?>
    <div class="space-y-4">
    <?php foreach ($pending as $app): ?>
      <div class="border p-5" style="background:#122440;border-color:#1e3455;">
        <div class="flex flex-wrap justify-between items-start gap-4 mb-4">
          <div>
            <h3 style="font-family:'Space Grotesk',sans-serif;font-weight:700;" class="text-lg"><?= htmlspecialchars($app['company']) ?></h3>
            <p class="text-sm" style="color:#8da0b3;"><?= htmlspecialchars($app['contact_name']) ?> · <?= htmlspecialchars($app['email']) ?> · <?= htmlspecialchars($app['country'] ?? '') ?></p>
          </div>
          <div class="flex gap-2">
            <form method="POST" style="display:inline">
              <input type="hidden" name="id" value="<?= $app['id'] ?>"/>
              <input type="hidden" name="action" value="approve"/>
              <button type="submit" class="px-4 py-2 text-xs font-mono uppercase font-bold" style="background:#ffd165;color:#081425;">✓ Approve</button>
            </form>
            <form method="POST" style="display:inline" onsubmit="return confirm('Reject this application?')">
              <input type="hidden" name="id" value="<?= $app['id'] ?>"/>
              <input type="hidden" name="action" value="reject"/>
              <button type="submit" class="px-4 py-2 text-xs font-mono uppercase" style="border:1px solid #ef4444;color:#ef4444;">✗ Reject</button>
            </form>
          </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
          <div><p class="font-mono uppercase mb-1" style="color:#4a6278;">Business Type</p><p><?= htmlspecialchars($app['business_type'] ?? '—') ?></p></div>
          <div><p class="font-mono uppercase mb-1" style="color:#4a6278;">Products</p><p><?= htmlspecialchars($app['products_interest'] ?? '—') ?></p></div>
          <div><p class="font-mono uppercase mb-1" style="color:#4a6278;">Annual Volume</p><p><?= htmlspecialchars($app['annual_volume'] ?? '—') ?></p></div>
          <div><p class="font-mono uppercase mb-1" style="color:#4a6278;">Applied</p><p><?= date('M d, Y', strtotime($app['created_at'])) ?></p></div>
        </div>
        <?php if ($app['message']): ?>
        <div class="mt-3 p-3 text-xs" style="background:#0c1c30;color:#8da0b3;border-left:2px solid #1e3455;"><?= nl2br(htmlspecialchars($app['message'])) ?></div>
        <?php endif; ?>
        <?php if (!empty($app['document_path'])): ?>
        <div class="mt-2">
          <a href="/admin/download.php?type=app_doc&id=<?= $app['id'] ?>" class="text-xs font-mono px-3 py-1" style="background:#0c1c30;color:#ffd165;border:1px solid #1e3455;">↓ Business Document</a>
        </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- MEMBERS -->
  <div id="panel-members" class="panel">
    <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;" class="uppercase mb-4">Active Members</h2>
    <div class="border overflow-hidden" style="border-color:#1e3455;">
      <table class="w-full text-sm">
        <thead style="background:#162a4a;">
          <tr>
            <th class="text-left px-4 py-3 text-xs font-mono uppercase" style="color:#8da0b3;">Company</th>
            <th class="text-left px-4 py-3 text-xs font-mono uppercase" style="color:#8da0b3;">Contact</th>
            <th class="text-left px-4 py-3 text-xs font-mono uppercase" style="color:#8da0b3;">Country</th>
            <th class="text-left px-4 py-3 text-xs font-mono uppercase" style="color:#8da0b3;">Tier</th>
            <th class="text-left px-4 py-3 text-xs font-mono uppercase" style="color:#8da0b3;">Last Login</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($members as $i => $m): ?>
          <tr style="border-top:1px solid #1e3455;<?= $i%2?'background:#0f2035':'' ?>">
            <td class="px-4 py-3 font-medium"><?= htmlspecialchars($m['company']) ?></td>
            <td class="px-4 py-3 text-xs" style="color:#8da0b3;"><?= htmlspecialchars($m['contact_name']) ?><br/><?= htmlspecialchars($m['email']) ?></td>
            <td class="px-4 py-3 text-xs"><?= htmlspecialchars($m['country'] ?? '—') ?></td>
            <td class="px-4 py-3"><span class="text-xs font-mono px-2 py-1" style="background:#ffd16520;color:#ffd165;border:1px solid #ffd16540;"><?= strtoupper($m['account_tier']) ?></span></td>
            <td class="px-4 py-3 text-xs" style="color:#8da0b3;"><?= $m['last_login'] ? date('M d Y', strtotime($m['last_login'])) : 'Never' ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ORDERS -->
  <div id="panel-orders" class="panel">
    <div class="flex justify-between items-center mb-4">
      <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;" class="uppercase">Orders</h2>
      <a href="/admin/new-order.php" class="px-4 py-2 text-xs font-mono uppercase font-bold" style="background:#ffd165;color:#081425;">+ New Order</a>
    </div>
    <div class="border overflow-hidden" style="border-color:#1e3455;">
      <table class="w-full text-sm">
        <thead style="background:#162a4a;">
          <tr>
            <th class="text-left px-4 py-3 text-xs font-mono uppercase" style="color:#8da0b3;">Order #</th>
            <th class="text-left px-4 py-3 text-xs font-mono uppercase" style="color:#8da0b3;">Company</th>
            <th class="text-left px-4 py-3 text-xs font-mono uppercase" style="color:#8da0b3;">Status</th>
            <th class="text-left px-4 py-3 text-xs font-mono uppercase" style="color:#8da0b3;">Amount</th>
            <th class="text-left px-4 py-3 text-xs font-mono uppercase" style="color:#8da0b3;">Date</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($orders)): ?>
          <tr><td colspan="6" class="px-4 py-8 text-center text-xs" style="color:#8da0b3;">No orders yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($orders as $i => $o): ?>
          <?php $sc = ['pending'=>'#f59e0b','confirmed'=>'#3b82f6','processing'=>'#8b5cf6','shipped'=>'#10b981','delivered'=>'#22c55e','cancelled'=>'#ef4444'][$o['status']] ?? '#8da0b3'; ?>
          <tr style="border-top:1px solid #1e3455;<?= $i%2?'background:#0f2035':'' ?>">
            <td class="px-4 py-3 font-mono text-xs"><?= htmlspecialchars($o['order_number']) ?></td>
            <td class="px-4 py-3 text-xs"><?= htmlspecialchars($o['company']) ?></td>
            <td class="px-4 py-3"><span class="text-xs font-mono px-2 py-1" style="background:<?=$sc?>22;color:<?=$sc?>;border:1px solid <?=$sc?>44;"><?= $o['status'] ?></span></td>
            <td class="px-4 py-3 font-mono text-xs"><?= $o['total_usd'] ? '$'.number_format($o['total_usd'],2) : '—' ?></td>
            <td class="px-4 py-3 text-xs" style="color:#8da0b3;"><?= date('M d Y', strtotime($o['created_at'])) ?></td>
            <td class="px-4 py-3"><a href="/admin/invoice.php?id=<?= $o['id'] ?>" class="text-xs font-mono" style="color:#ffd165;">Manage →</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script>
function showPanel(name) {
  document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('panel-' + name).classList.add('active');
  event.target.classList.add('active');
}
</script>
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

