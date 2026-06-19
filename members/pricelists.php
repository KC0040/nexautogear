<?php
require_once __DIR__ . '/../includes/auth.php';
require_member();
$member = db()->prepare('SELECT * FROM b2b_members WHERE id = ?');
$member->execute([$_SESSION['member_id']]);
$member = $member->fetch();
$tier   = strtolower($member['account_tier'] ?? 'standard');

$pl = json_decode(file_get_contents(__DIR__ . '/../data/pricelists.json'), true);
$bonusPct = $pl['member_bonus'][$tier] ?? 0;

function b2b_price(float $msrp, int $offPct, int $bonus): string {
    if ($msrp <= 0) return '<span style="color:#4a6278;">TBD</span>';
    $final = $msrp * (1 - ($offPct + $bonus) / 100);
    return '$' . number_format($final, 2);
}
function msrp_display(float $msrp): string {
    return $msrp > 0 ? '$' . number_format($msrp, 2) : 'â€”';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>B2B Price List | NEX AUTO GEAR</title>
  <meta name="robots" content="noindex"/>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body{background:#081425;color:#e8edf2;font-family:'Inter',sans-serif;}
    .sidebar-link{display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:.8rem;font-family:'JetBrains Mono',monospace;text-transform:uppercase;letter-spacing:.06em;color:#8da0b3;transition:all .2s;}
    .sidebar-link:hover,.sidebar-link.active{color:#ffd165;background:#122440;}
    th{font-family:'JetBrains Mono',monospace;font-size:.65rem;text-transform:uppercase;letter-spacing:.08em;color:#4a6278;padding:10px 16px;text-align:left;}
    td{padding:10px 16px;font-size:.875rem;border-top:1px solid #1e3455;}
    .price-col{font-family:'JetBrains Mono',monospace;font-weight:600;}
    .tier-badge{font-size:.65rem;font-family:'JetBrains Mono',monospace;text-transform:uppercase;padding:2px 8px;letter-spacing:.06em;}
  </style>
</head>
<body class="min-h-screen">

<header class="h-14 border-b flex items-center justify-between px-6 sticky top-0 z-50" style="background:#081425;border-color:#1e3455;">
  <a href="/" style="font-family:'Space Grotesk',sans-serif;font-weight:700;">
    <span style="color:#e8edf2">NEX</span><span style="color:#ffd165">AUTO</span><span style="color:#e8edf2">GEAR</span>
  </a>
  <div class="flex items-center gap-4">
    <span class="text-xs font-mono" style="color:#8da0b3;"><?= htmlspecialchars($member['company']) ?></span>
    <span class="tier-badge" style="background:#ffd16520;color:#ffd165;border:1px solid #ffd16540;"><?= strtoupper($tier) ?></span>
    <a href="/members/logout.php" class="text-xs font-mono" style="color:#8da0b3;">Sign Out</a>
  </div>
</header>

<div class="flex min-h-[calc(100vh-56px)]">
  <aside class="w-52 border-r shrink-0 py-6" style="border-color:#1e3455;background:#0c1c30;">
    <a href="/members/dashboard.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">dashboard</span> Dashboard</a>
    <a href="/members/orders.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">package_2</span> Orders</a>
    <a href="/members/pricelists.php" class="sidebar-link active"><span class="material-symbols-outlined" style="font-size:1rem;">price_check</span> Price List</a>
    <a href="/members/catalog.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">menu_book</span> Catalog</a>
    <a href="/members/new-order.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">send</span> New Inquiry</a>
    <a href="/members/payment-info.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">account_balance</span> Payment Info</a>
    <div class="my-4" style="border-top:1px solid #1e3455;"></div>
    <a href="/members/support.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">support_agent</span> Support</a>
  </aside>

  <main class="flex-1 p-8 overflow-auto">
    <div class="max-w-5xl">

      <!-- Header -->
      <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
        <div>
          <h1 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.5rem;" class="mb-1">B2B Price List</h1>
          <p style="color:#8da0b3;font-size:.875rem;">Updated <?= $pl['updated'] ?> Â· Prices in USD Â· Subject to change without notice</p>
        </div>
        <?php if ($bonusPct > 0): ?>
        <div class="px-4 py-2 border" style="border-color:#ffd165;background:#ffd16510;">
          <p class="text-xs font-mono uppercase" style="color:#ffd165;"><?= strtoupper($tier) ?> Member Bonus</p>
          <p class="text-sm mt-1">Extra <strong style="color:#ffd165;"><?= $bonusPct ?>%</strong> off applied to all your prices</p>
        </div>
        <?php endif; ?>
      </div>

      <!-- DISCOUNT TIERS -->
      <div class="mb-10">
        <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;" class="uppercase mb-4 text-sm">Quantity Discount Tiers</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <?php foreach ($pl['discount_tiers'] as $t): ?>
          <?php $effective = $t['off_pct'] > 0 ? $t['off_pct'] + $bonusPct : 0; ?>
          <div class="p-4 border" style="background:#122440;border-color:<?= $t['off_pct']===45?'#ffd16540':'#1e3455' ?>;">
            <p class="text-xs font-mono uppercase mb-2" style="color:#8da0b3;"><?= htmlspecialchars($t['label']) ?></p>
            <?php if ($t['off_pct'] > 0): ?>
            <p style="font-family:'Space Grotesk',sans-serif;font-size:1.5rem;font-weight:700;color:#ffd165;"><?= $effective ?>%<span style="font-size:.75rem;color:#8da0b3;font-weight:400;"> off MSRP</span></p>
            <?php else: ?>
            <p style="font-family:'Space Grotesk',sans-serif;font-size:1rem;font-weight:700;color:#8da0b3;">Custom</p>
            <?php endif; ?>
            <p class="text-xs mt-2" style="color:#4a6278;"><?= htmlspecialchars($t['note']) ?></p>
          </div>
          <?php endforeach; ?>
        </div>
        <?php if ($bonusPct > 0): ?>
        <p class="text-xs mt-3 font-mono" style="color:#ffd165;">â˜… Your <?= strtoupper($tier) ?> tier adds an extra <?= $bonusPct ?>% on top of the above discounts.</p>
        <?php endif; ?>
      </div>

      <!-- AEGISRIM WHEELS -->
      <div class="mb-10">
        <div class="flex items-center gap-3 mb-5">
          <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;" class="uppercase">AegisRim Wheels</h2>
          <span class="text-xs font-mono px-2 py-1" style="background:#1e3455;color:#8da0b3;">Per wheel (pc)</span>
        </div>
        <p class="text-xs mb-4" style="color:#4a6278;"><?= htmlspecialchars($pl['wheels']['note']) ?></p>

        <?php foreach ($pl['wheels']['series'] as $series): ?>
        <div class="mb-6">
          <div class="flex items-center gap-3 mb-2 px-4 py-3" style="background:#0c1c30;border:1px solid #1e3455;">
            <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;"><?= htmlspecialchars($series['name']) ?></span>
            <span class="text-xs font-mono px-2 py-1" style="background:#122440;color:#8da0b3;"><?= htmlspecialchars($series['type']) ?></span>
            <span class="text-xs" style="color:#4a6278;"><?= implode(' Â· ', $series['certifications']) ?></span>
          </div>
          <div class="border overflow-hidden" style="border-color:#1e3455;">
            <table class="w-full">
              <thead style="background:#162a4a;">
                <tr>
                  <th>Size</th>
                  <th>MSRP (retail)</th>
                  <th>1 pc (30% off)</th>
                  <th>4+ pcs (45% off<?= $bonusPct>0?"+{$bonusPct}%":'' ?>)</th>
                  <th>100+ pcs</th>
                  <th>MOQ</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($series['sizes'] as $row): ?>
              <tr style="<?= $row['msrp']>0?'':'background:#0c1c30;' ?>">
                <td class="font-mono font-semibold"><?= htmlspecialchars($row['size']) ?></td>
                <td class="price-col" style="color:#8da0b3;"><?= msrp_display($row['msrp']) ?></td>
                <td class="price-col"><?= b2b_price($row['msrp'], 30, $bonusPct) ?></td>
                <td class="price-col" style="color:#ffd165;"><?= b2b_price($row['msrp'], 45, $bonusPct) ?></td>
                <td class="price-col" style="color:#10b981;"><?= $row['msrp']>0 ? b2b_price($row['msrp'], 50, $bonusPct).'<span style="font-size:.7rem;color:#4a6278;"> est.</span>' : '<span style="color:#4a6278;">Contact Sales</span>' ?></td>
                <td class="text-xs font-mono" style="color:#4a6278;"><?= $series['name']==='Pro Series'?'1 pc':'4 pcs' ?></td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php endforeach; ?>

        <div class="p-4 border mt-2" style="border-color:#ffd16530;background:#ffd16508;">
          <p class="text-xs font-mono" style="color:#ffd165;">Custom finishes (powder coat, chrome, custom offset/PCD) available â€” add 10â€“20% or contact Sales for exact quote.</p>
        </div>
      </div>

      <!-- TPMS -->
      <div class="mb-10">
        <div class="flex items-center gap-3 mb-5">
          <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;" class="uppercase">TPMS Sensors</h2>
          <span class="text-xs font-mono px-2 py-1" style="background:#1e3455;color:#8da0b3;">Per set / kit</span>
        </div>
        <p class="text-xs mb-4" style="color:#4a6278;"><?= htmlspecialchars($pl['tpms']['note']) ?></p>
        <div class="border overflow-hidden" style="border-color:#1e3455;">
          <table class="w-full">
            <thead style="background:#162a4a;">
              <tr>
                <th>Product</th>
                <th>Brand</th>
                <th>MSRP</th>
                <th>B2B Price (45% off<?= $bonusPct>0?"+{$bonusPct}%":'' ?>)</th>
                <th>MOQ</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($pl['tpms']['products'] as $i => $p): ?>
            <tr style="<?= $i%2?'background:#0f2035':'' ?>">
              <td><?= htmlspecialchars($p['name']) ?></td>
              <td class="text-xs font-mono" style="color:#8da0b3;"><?= htmlspecialchars($p['brand']) ?></td>
              <td class="price-col" style="color:#8da0b3;"><?= msrp_display($p['msrp']) ?></td>
              <td class="price-col" style="color:#ffd165;"><?= b2b_price($p['msrp'], 45, $bonusPct) ?></td>
              <td class="text-xs font-mono" style="color:#4a6278;"><?= $p['moq'] ?> <?= $p['unit'] ?>s</td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- WEB SERVICES -->
      <div class="mb-10">
        <div class="flex items-center gap-3 mb-2">
          <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;" class="uppercase">Digital Solutions</h2>
          <span class="text-xs font-mono px-2 py-1" style="background:#3b82f620;color:#3b82f6;border:1px solid #3b82f640;">Partner Service</span>
        </div>
        <p class="text-xs mb-5" style="color:#4a6278;"><?= htmlspecialchars($pl['web_services']['note']) ?></p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <?php foreach ($pl['web_services']['packages'] as $pkg): ?>
          <div class="p-6 border" style="background:#122440;border-color:<?= $pkg['price_usd']===1500?'#ffd16540':'#1e3455' ?>;">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.1rem;"><?= htmlspecialchars($pkg['name']) ?></p>
                <p class="text-xs font-mono mt-1" style="color:#8da0b3;text-transform:uppercase;"><?= htmlspecialchars($pkg['billing']) ?></p>
              </div>
              <p style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.75rem;color:#ffd165;">$<?= number_format($pkg['price_usd']) ?></p>
            </div>
            <ul class="space-y-2">
              <?php foreach ($pkg['includes'] as $item): ?>
              <li class="flex items-start gap-2 text-sm" style="color:#8da0b3;">
                <span style="color:#ffd165;flex-shrink:0;">Â·</span> <?= htmlspecialchars($item) ?>
              </li>
              <?php endforeach; ?>
            </ul>
            <a href="mailto:Sales@nexautogear.com?subject=Digital Solutions â€” <?= urlencode($pkg['name']) ?> Package"
               class="block mt-5 py-2 text-center text-xs font-mono font-bold uppercase tracking-widest"
               style="background:#ffd165;color:#081425;">Inquire â†’</a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Footer note -->
      <div class="p-5 border" style="border-color:#1e3455;background:#0c1c30;">
        <p class="text-xs" style="color:#4a6278;">All prices are in USD. Prices shown are estimated based on MSRP discounts and are subject to final confirmation by our sales team. Shipping, customs duties, and applicable taxes are not included. For large volume orders or custom specifications, contact <a href="mailto:Sales@nexautogear.com" style="color:#ffd165;">Sales@nexautogear.com</a>.</p>
      </div>

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


