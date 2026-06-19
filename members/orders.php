<?php
require_once __DIR__ . '/../includes/auth.php';
require_member();

$memberId = $_SESSION['member_id'];
$member = db()->prepare('SELECT * FROM b2b_members WHERE id = ?');
$member->execute([$memberId]);
$member = $member->fetch();

// Pagination
$perPage = 20;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$total = db()->prepare('SELECT COUNT(*) FROM b2b_orders WHERE member_id = ?');
$total->execute([$memberId]);
$totalCount = (int)$total->fetchColumn();
$totalPages = ceil($totalCount / $perPage);

$orders = db()->prepare('SELECT * FROM b2b_orders WHERE member_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?');
$orders->execute([$memberId, $perPage, $offset]);
$orderList = $orders->fetchAll();

$statusColors = ['pending'=>'#f59e0b','confirmed'=>'#3b82f6','processing'=>'#8b5cf6','shipped'=>'#10b981','delivered'=>'#22c55e','cancelled'=>'#ef4444'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Orders | NEX AUTO GEAR</title>
  <meta name="robots" content="noindex"/>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body{background:#081425;color:#e8edf2;font-family:'Inter',sans-serif;}
    .sidebar-link{display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:.8rem;font-family:'JetBrains Mono',monospace;text-transform:uppercase;letter-spacing:.06em;color:#8da0b3;transition:all .2s;}
    .sidebar-link:hover,.sidebar-link.active{color:#ffd165;background:#122440;}
  </style>
</head>
<body class="min-h-screen">

<header class="h-14 border-b flex items-center justify-between px-6 sticky top-0 z-50" style="background:#081425;border-color:#1e3455;">
  <a href="/" style="font-family:'Space Grotesk',sans-serif;font-weight:700;"><span style="color:#e8edf2">NEX</span><span style="color:#ffd165">AUTO</span><span style="color:#e8edf2">GEAR</span></a>
  <div class="flex items-center gap-4">
    <span class="text-xs font-mono" style="color:#8da0b3;"><?= htmlspecialchars($member['company']) ?></span>
    <span class="text-xs px-2 py-1 font-mono uppercase" style="background:#ffd16520;color:#ffd165;border:1px solid #ffd16540;"><?= strtoupper($member['account_tier']) ?></span>
    <a href="/members/logout.php" class="text-xs font-mono" style="color:#8da0b3;">Sign Out</a>
  </div>
</header>

<div class="flex min-h-[calc(100vh-56px)]">
  <aside class="w-52 border-r shrink-0 py-6" style="border-color:#1e3455;background:#0c1c30;">
    <a href="/members/dashboard.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">dashboard</span> Dashboard</a>
    <a href="/members/orders.php" class="sidebar-link active"><span class="material-symbols-outlined" style="font-size:1rem;">package_2</span> Orders</a>
    <a href="/members/pricelists.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">price_check</span> Price List</a>
    <a href="/members/catalog.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">menu_book</span> Catalog</a>
    <a href="/members/new-order.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">send</span> New Inquiry</a>
    <a href="/members/payment-info.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">account_balance</span> Payment Info</a>
    <div class="my-4" style="border-top:1px solid #1e3455;"></div>
    <a href="/members/support.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">support_agent</span> Support</a>
  </aside>

  <main class="flex-1 p-8 overflow-auto">
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.5rem;" class="mb-1">Order History</h1>
        <p style="color:#8da0b3;font-size:.875rem;"><?= $totalCount ?> total orders</p>
      </div>
      <a href="/members/new-order.php" class="text-xs font-mono font-bold uppercase px-4 py-2" style="background:#ffd165;color:#081425;">+ New Inquiry</a>
    </div>

    <?php if (empty($orderList)): ?>
    <div class="p-12 border text-center" style="border-color:#1e3455;background:#122440;">
      <p style="color:#8da0b3;">No orders yet.</p>
      <a href="/members/new-order.php" class="inline-block mt-4 text-sm font-mono" style="color:#ffd165;">Submit your first inquiry â†’</a>
    </div>
    <?php else: ?>

    <div class="border overflow-hidden" style="border-color:#1e3455;">
      <table class="w-full text-sm">
        <thead style="background:#162a4a;">
          <tr>
            <th class="text-left px-4 py-3 text-xs font-mono uppercase" style="color:#8da0b3;">Order #</th>
            <th class="text-left px-4 py-3 text-xs font-mono uppercase" style="color:#8da0b3;">Status</th>
            <th class="text-left px-4 py-3 text-xs font-mono uppercase" style="color:#8da0b3;">Amount</th>
            <th class="text-left px-4 py-3 text-xs font-mono uppercase" style="color:#8da0b3;">Tracking</th>
            <th class="text-left px-4 py-3 text-xs font-mono uppercase" style="color:#8da0b3;">Date</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($orderList as $i => $o):
          $c = $statusColors[$o['status']] ?? '#8da0b3'; ?>
        <tr style="border-top:1px solid #1e3455;<?= $i%2?'background:#0f2035':'' ?>">
          <td class="px-4 py-3 font-mono text-xs font-semibold"><?= htmlspecialchars($o['order_number']) ?></td>
          <td class="px-4 py-3">
            <span class="text-xs font-mono px-2 py-1" style="background:<?=$c?>22;color:<?=$c?>;border:1px solid <?=$c?>44;"><?= $o['status'] ?></span>
          </td>
          <td class="px-4 py-3 font-mono text-xs"><?= $o['total_usd'] ? '$'.number_format($o['total_usd'],2) : 'â€”' ?></td>
          <td class="px-4 py-3 font-mono text-xs" style="color:#8da0b3;"><?= $o['tracking_number'] ? htmlspecialchars($o['tracking_number']) : 'â€”' ?></td>
          <td class="px-4 py-3 text-xs" style="color:#8da0b3;"><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
          <td class="px-4 py-3 text-xs">
            <?php
            $notes_preview = strtok($o['notes'] ?? '', "\n");
            if (strlen($notes_preview) > 40) $notes_preview = substr($notes_preview,0,40).'â€¦';
            ?>
            <span style="color:#4a6278;"><?= htmlspecialchars($notes_preview) ?></span>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex gap-2 mt-6">
      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
      <a href="?page=<?=$p?>" class="text-xs font-mono px-3 py-2"
         style="<?= $p===$page ? 'background:#ffd165;color:#081425;font-weight:700;' : 'background:#122440;color:#8da0b3;border:1px solid #1e3455;' ?>">
        <?= $p ?>
      </a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
  </main>
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


