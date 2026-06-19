<?php
require_once __DIR__ . '/../includes/auth.php';
require_member();

$memberId = $_SESSION['member_id'];
$stmt = db()->prepare('SELECT * FROM b2b_members WHERE id = ?');
$stmt->execute([$memberId]);
$member = $stmt->fetch();

$orders = db()->prepare('SELECT * FROM b2b_orders WHERE member_id = ? ORDER BY created_at DESC LIMIT 20');
$orders->execute([$memberId]);
$orderList = $orders->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Account | NEX AUTO GEAR</title>
  <meta name="robots" content="noindex"/>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body{background:#081425;color:#e8edf2;font-family:'Inter',sans-serif;}
    .sidebar-link{display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:.8rem;font-family:'JetBrains Mono',monospace;text-transform:uppercase;letter-spacing:.06em;color:#8da0b3;transition:all .2s;}
    .sidebar-link:hover,.sidebar-link.active{color:#ffd165;background:#122440;}
    .status-badge{font-size:.65rem;font-family:'JetBrains Mono',monospace;text-transform:uppercase;padding:2px 8px;letter-spacing:.06em;}
  </style>
</head>
<body class="min-h-screen">

<!-- TOP NAV -->
<header class="h-14 border-b flex items-center justify-between px-6 sticky top-0 z-50" style="background:#081425;border-color:#1e3455;">
  <a href="/" style="font-family:'Space Grotesk',sans-serif;font-weight:700;">
    <span style="color:#e8edf2">NEX</span><span style="color:#ffd165">AUTO</span><span style="color:#e8edf2">GEAR</span>
  </a>
  <div class="flex items-center gap-4">
    <span class="text-xs font-mono" style="color:#8da0b3;"><?= htmlspecialchars($member['company']) ?></span>
    <span class="text-xs px-2 py-1 font-mono uppercase" style="background:#ffd16520;color:#ffd165;border:1px solid #ffd16540;"><?= strtoupper($member['account_tier']) ?></span>
    <a href="/members/logout.php" class="text-xs font-mono" style="color:#8da0b3;">Sign Out</a>
  </div>
</header>

<div class="flex min-h-[calc(100vh-56px)]">
  <!-- SIDEBAR -->
  <aside class="w-52 border-r shrink-0 py-6" style="border-color:#1e3455;background:#0c1c30;">
    <a href="/members/dashboard.php" class="sidebar-link active">
      <span class="material-symbols-outlined" style="font-size:1rem;">dashboard</span> Dashboard
    </a>
    <a href="/members/orders.php" class="sidebar-link">
      <span class="material-symbols-outlined" style="font-size:1rem;">package_2</span> Orders
    </a>
    <a href="/members/pricelists.php" class="sidebar-link">
      <span class="material-symbols-outlined" style="font-size:1rem;">price_check</span> Price List
    </a>
    <a href="/members/catalog.php" class="sidebar-link">
      <span class="material-symbols-outlined" style="font-size:1rem;">menu_book</span> Catalog
    </a>
    <a href="/members/new-order.php" class="sidebar-link">
      <span class="material-symbols-outlined" style="font-size:1rem;">send</span> New Inquiry
    </a>
    <a href="/members/payment-info.php" class="sidebar-link">
      <span class="material-symbols-outlined" style="font-size:1rem;">account_balance</span> Payment Info
    </a>
    <div class="my-4" style="border-top:1px solid #1e3455;"></div>
    <a href="/members/support.php" class="sidebar-link">
      <span class="material-symbols-outlined" style="font-size:1rem;">support_agent</span> Support
    </a>
  </aside>

  <!-- MAIN -->
  <main class="flex-1 p-8 overflow-auto">
    <div class="mb-8">
      <h1 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.5rem;" class="mb-1">
        Welcome, <?= htmlspecialchars($member['contact_name']) ?>
      </h1>
      <p style="color:#8da0b3;font-size:.875rem;"><?= htmlspecialchars($member['company']) ?> Â· <?= htmlspecialchars($member['country'] ?? '') ?></p>
    </div>

    <!-- STATS -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
      <?php
      $totalOrders = count($orderList);
      $activeOrders = count(array_filter($orderList, fn($o) => in_array($o['status'], ['pending','confirmed','processing','shipped'])));
      ?>
      <div class="p-5 border" style="background:#122440;border-color:#1e3455;">
        <p class="text-xs font-mono uppercase mb-2" style="color:#8da0b3;">Total Orders</p>
        <p style="font-family:'Space Grotesk',sans-serif;font-size:1.75rem;font-weight:700;"><?= $totalOrders ?></p>
      </div>
      <div class="p-5 border" style="background:#122440;border-color:#1e3455;">
        <p class="text-xs font-mono uppercase mb-2" style="color:#8da0b3;">Active Orders</p>
        <p style="font-family:'Space Grotesk',sans-serif;font-size:1.75rem;font-weight:700;color:#ffd165;"><?= $activeOrders ?></p>
      </div>
      <div class="p-5 border" style="background:#122440;border-color:#1e3455;">
        <p class="text-xs font-mono uppercase mb-2" style="color:#8da0b3;">Account Tier</p>
        <p style="font-family:'Space Grotesk',sans-serif;font-size:1.25rem;font-weight:700;text-transform:uppercase;"><?= $member['account_tier'] ?></p>
      </div>
      <div class="p-5 border" style="background:#122440;border-color:#1e3455;">
        <p class="text-xs font-mono uppercase mb-2" style="color:#8da0b3;">Member Since</p>
        <p style="font-family:'Space Grotesk',sans-serif;font-size:1rem;font-weight:600;"><?= date('M Y', strtotime($member['created_at'])) ?></p>
      </div>
    </div>

    <!-- RECENT ORDERS -->
    <div class="mb-10">
      <div class="flex justify-between items-center mb-5">
        <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;" class="uppercase">Recent Orders</h2>
        <a href="/members/orders.php" class="text-xs font-mono" style="color:#ffd165;">View All â†’</a>
      </div>
      <?php if (empty($orderList)): ?>
      <div class="p-8 border text-center" style="border-color:#1e3455;background:#122440;">
        <p style="color:#8da0b3;">No orders yet. <a href="/members/new-order.php" style="color:#ffd165;">Submit your first inquiry â†’</a></p>
      </div>
      <?php else: ?>
      <div class="border overflow-hidden" style="border-color:#1e3455;">
        <table class="w-full text-sm">
          <thead style="background:#162a4a;">
            <tr>
              <th class="text-left px-4 py-3 font-mono text-xs uppercase" style="color:#8da0b3;">Order #</th>
              <th class="text-left px-4 py-3 font-mono text-xs uppercase" style="color:#8da0b3;">Status</th>
              <th class="text-left px-4 py-3 font-mono text-xs uppercase" style="color:#8da0b3;">Amount</th>
              <th class="text-left px-4 py-3 font-mono text-xs uppercase" style="color:#8da0b3;">Date</th>
              <th class="text-left px-4 py-3 font-mono text-xs uppercase" style="color:#8da0b3;">Tracking</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($orderList as $i => $order): ?>
            <tr style="border-top:1px solid #1e3455;<?= $i%2?'background:#0f2035':'' ?>">
              <td class="px-4 py-3 font-mono text-xs"><?= htmlspecialchars($order['order_number']) ?></td>
              <td class="px-4 py-3">
                <?php
                $statusColors = ['pending'=>'#f59e0b','confirmed'=>'#3b82f6','processing'=>'#8b5cf6','shipped'=>'#10b981','delivered'=>'#22c55e','cancelled'=>'#ef4444'];
                $c = $statusColors[$order['status']] ?? '#8da0b3';
                ?>
                <span class="status-badge" style="background:<?=$c?>22;color:<?=$c?>;border:1px solid <?=$c?>44;"><?= $order['status'] ?></span>
              </td>
              <td class="px-4 py-3 font-mono text-xs"><?= $order['total_usd'] ? '$'.number_format($order['total_usd'],2) : 'â€”' ?></td>
              <td class="px-4 py-3 text-xs" style="color:#8da0b3;"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
              <td class="px-4 py-3 font-mono text-xs"><?= $order['tracking_number'] ? htmlspecialchars($order['tracking_number']) : 'â€”' ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

    <!-- QUICK LINKS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <a href="/members/pricelists.php" class="p-5 border hover:border-yellow-500/50 transition-all" style="background:#122440;border-color:#1e3455;">
        <span class="material-symbols-outlined mb-3 block" style="color:#ffd165;">price_check</span>
        <p style="font-family:'Space Grotesk',sans-serif;font-weight:600;" class="mb-1">B2B Price List</p>
        <p class="text-xs" style="color:#8da0b3;">Current wholesale pricing for your account tier</p>
      </a>
      <a href="/members/catalog.php" class="p-5 border hover:border-yellow-500/50 transition-all" style="background:#122440;border-color:#1e3455;">
        <span class="material-symbols-outlined mb-3 block" style="color:#ffd165;">menu_book</span>
        <p style="font-family:'Space Grotesk',sans-serif;font-weight:600;" class="mb-1">Product Catalog</p>
        <p class="text-xs" style="color:#8da0b3;">Full AegisRim wheels + TPMS catalog PDF</p>
      </a>
      <a href="/inquiry/" class="p-5 border hover:border-yellow-500/50 transition-all" style="background:#122440;border-color:#1e3455;">
        <span class="material-symbols-outlined mb-3 block" style="color:#ffd165;">send</span>
        <p style="font-family:'Space Grotesk',sans-serif;font-weight:600;" class="mb-1">New Inquiry</p>
        <p class="text-xs" style="color:#8da0b3;">Submit a new wholesale order inquiry</p>
      </a>
    </div>
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


