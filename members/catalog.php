<?php
require_once __DIR__ . '/../includes/auth.php';
require_member();
$member = db()->prepare('SELECT * FROM b2b_members WHERE id = ?');
$member->execute([$_SESSION['member_id']]);
$member = $member->fetch();
$tier = strtolower($member['account_tier'] ?? 'standard');

$products = json_decode(file_get_contents(__DIR__ . '/../data/products.json'), true);

function stock_badge(string $status): string {
    return match($status) {
        'in_stock' => '<span style="background:#10b98120;color:#10b981;border:1px solid #10b98140;" class="stock-tag">In Stock</span>',
        'limited'  => '<span style="background:#f59e0b20;color:#f59e0b;border:1px solid #f59e0b40;" class="stock-tag">Limited</span>',
        'order'    => '<span style="background:#8b5cf620;color:#8b5cf6;border:1px solid #8b5cf640;" class="stock-tag">Made to Order</span>',
        default    => '<span style="background:#1e345550;color:#8da0b3;border:1px solid #1e3455;" class="stock-tag">Contact for Availability</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>B2B Catalog | NEX AUTO GEAR</title>
  <meta name="robots" content="noindex"/>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body{background:#081425;color:#e8edf2;font-family:'Inter',sans-serif;}
    .sidebar-link{display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:.8rem;font-family:'JetBrains Mono',monospace;text-transform:uppercase;letter-spacing:.06em;color:#8da0b3;transition:all .2s;}
    .sidebar-link:hover,.sidebar-link.active{color:#ffd165;background:#122440;}
    .stock-tag{font-family:'JetBrains Mono',monospace;font-size:.6rem;text-transform:uppercase;letter-spacing:.06em;padding:2px 7px;}
    .product-card{background:#122440;border:1px solid #1e3455;transition:border-color .2s;}
    .product-card:hover{border-color:#ffd16540;}
    .section-header{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.1rem;text-transform:uppercase;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid #1e3455;}
    .spec-label{font-family:'JetBrains Mono',monospace;font-size:.6rem;text-transform:uppercase;letter-spacing:.08em;color:#4a6278;}
  </style>
</head>
<body class="min-h-screen">

<header class="h-14 border-b flex items-center justify-between px-6 sticky top-0 z-50" style="background:#081425;border-color:#1e3455;">
  <a href="/" style="font-family:'Space Grotesk',sans-serif;font-weight:700;"><span style="color:#e8edf2">NEX</span><span style="color:#ffd165">AUTO</span><span style="color:#e8edf2">GEAR</span></a>
  <div class="flex items-center gap-4">
    <span class="text-xs font-mono" style="color:#8da0b3;"><?= htmlspecialchars($member['company']) ?></span>
    <span class="text-xs px-2 py-1 font-mono uppercase" style="background:#ffd16520;color:#ffd165;border:1px solid #ffd16540;"><?= strtoupper($tier) ?></span>
    <a href="/members/logout.php" class="text-xs font-mono" style="color:#8da0b3;">Sign Out</a>
  </div>
</header>

<div class="flex min-h-[calc(100vh-56px)]">
  <aside class="w-52 border-r shrink-0 py-6" style="border-color:#1e3455;background:#0c1c30;">
    <a href="/members/dashboard.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">dashboard</span> Dashboard</a>
    <a href="/members/orders.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">package_2</span> Orders</a>
    <a href="/members/pricelists.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">price_check</span> Price List</a>
    <a href="/members/catalog.php" class="sidebar-link active"><span class="material-symbols-outlined" style="font-size:1rem;">menu_book</span> Catalog</a>
    <a href="/members/new-order.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">send</span> New Inquiry</a>
    <a href="/members/payment-info.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">account_balance</span> Payment Info</a>
    <div class="my-4" style="border-top:1px solid #1e3455;"></div>
    <a href="/members/support.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">support_agent</span> Support</a>
  </aside>

  <main class="flex-1 p-8 overflow-auto">
    <div class="max-w-6xl">

      <div class="mb-8">
        <h1 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.5rem;" class="mb-1">B2B Product Catalog</h1>
        <p style="color:#8da0b3;font-size:.875rem;">
          Stock status updated weekly.
          <span style="color:#f59e0b;">âš  Cast wheel inventory varies â€” always confirm availability before placing bulk orders.</span>
        </p>
      </div>

      <!-- STOCK LEGEND -->
      <div class="flex flex-wrap gap-3 mb-8 p-4 border" style="background:#0c1c30;border-color:#1e3455;">
        <span style="font-size:.7rem;font-family:'JetBrains Mono',monospace;color:#4a6278;text-transform:uppercase;margin-right:4px;">Legend:</span>
        <?= stock_badge('in_stock') ?>
        <?= stock_badge('limited') ?>
        <?= stock_badge('contact') ?>
        <?= stock_badge('order') ?>
      </div>

      <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
           SECTION 1: CAST & FLOW FORMED WHEELS
      â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
      <div class="mb-12">
        <div class="section-header flex items-center justify-between">
          <span>AegisRim Wheels â€” Cast & Flow Formed</span>
          <span class="text-xs font-mono font-normal" style="color:#4a6278;">B2B Price: 30% off (1 pc) Â· 45% off (4+ pcs)</span>
        </div>
        <p class="text-xs mb-6" style="color:#4a6278;"><?= htmlspecialchars($products['wheels']['note']) ?></p>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
          <?php foreach ($products['wheels']['products'] as $p): ?>
          <div class="product-card p-5">
            <!-- Image placeholder -->
            <div class="w-full mb-4 flex items-center justify-center" style="height:160px;background:#0c1c30;border:1px solid #1e3455;">
              <?php if ($p['image'] && !str_starts_with($p['image'], '/images/wheels/')): ?>
              <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="max-height:140px;max-width:100%;object-fit:contain;"/>
              <?php else: ?>
              <span style="color:#1e3455;font-size:3rem;" class="material-symbols-outlined">tire_repair</span>
              <?php endif; ?>
            </div>

            <div class="flex items-start justify-between mb-2">
              <div>
                <p style="font-family:'Space Grotesk',sans-serif;font-weight:700;"><?= htmlspecialchars($p['name']) ?></p>
                <p class="text-xs font-mono mt-1" style="color:#4a6278;"><?= htmlspecialchars($p['sku']) ?> Â· <?= htmlspecialchars($p['type']) ?></p>
              </div>
              <?= stock_badge($p['stock_status']) ?>
            </div>

            <div class="space-y-2 mt-3">
              <div><p class="spec-label">Sizes</p><p class="text-xs"><?= implode(', ', $p['sizes']) ?></p></div>
              <div><p class="spec-label">PCD</p><p class="text-xs"><?= implode(', ', $p['pcd']) ?></p></div>
              <div><p class="spec-label">Finish</p><p class="text-xs"><?= implode(', ', $p['finish']) ?></p></div>
              <div><p class="spec-label">MOQ</p><p class="text-xs"><?= $p['moq'] ?> pcs</p></div>
            </div>

            <a href="/members/new-order.php?product=<?= urlencode($p['sku']) ?>"
               class="block mt-4 py-2 text-center text-xs font-mono font-bold uppercase"
               style="border:1px solid #ffd16540;color:#ffd165;">
              Inquire â†’
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
           SECTION 2: AEGISRIM FORGED (CUSTOM)
      â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
      <div class="mb-12">
        <div class="section-header flex items-center justify-between" style="border-color:#ffd16530;">
          <div class="flex items-center gap-3">
            <span>AegisRim Pro Series â€” Forged Custom</span>
            <span class="text-xs font-mono px-2 py-1" style="background:#ffd16520;color:#ffd165;border:1px solid #ffd16540;">45% OFF MSRP</span>
          </div>
          <span class="text-xs font-mono font-normal" style="color:#4a6278;">Lead time: <?= htmlspecialchars($products['wheels_forged']['lead_time']) ?></span>
        </div>

        <div class="mb-5 p-4 border" style="background:#ffd16508;border-color:#ffd16530;">
          <p class="text-sm" style="color:#8da0b3;">
            <?= htmlspecialchars($products['wheels_forged']['description']) ?>
            As a NEXAutogear B2B member, you receive <strong style="color:#ffd165;">45% off AegisRim retail MSRP</strong> on all forged orders.
            Full custom spec â€” size, offset, PCD, finish.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <?php foreach ($products['wheels_forged']['products'] as $p): ?>
          <div class="product-card p-5" style="border-color:#ffd16520;">
            <div class="w-full mb-4 flex items-center justify-center" style="height:180px;background:#0c1c30;border:1px solid #1e3455;">
              <span style="color:#1e3455;font-size:3rem;" class="material-symbols-outlined">tire_repair</span>
            </div>
            <div class="flex items-start justify-between mb-3">
              <div>
                <p style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.05rem;"><?= htmlspecialchars($p['name']) ?></p>
                <p class="text-xs font-mono mt-1" style="color:#4a6278;"><?= htmlspecialchars($p['sku']) ?></p>
              </div>
              <?= stock_badge($p['stock_status']) ?>
            </div>
            <div class="space-y-2">
              <div><p class="spec-label">Construction</p><p class="text-xs"><?= htmlspecialchars($p['construction']) ?></p></div>
              <div><p class="spec-label">Sizes Available</p><p class="text-xs"><?= htmlspecialchars(implode(', ', (array)$p['sizes'])) ?></p></div>
              <div><p class="spec-label">PCD</p><p class="text-xs"><?= htmlspecialchars(implode(', ', (array)$p['pcd'])) ?></p></div>
              <div><p class="spec-label">Finish</p><p class="text-xs"><?= htmlspecialchars(implode(', ', (array)$p['finish'])) ?></p></div>
              <div><p class="spec-label">MOQ</p><p class="text-xs">1 set Â· Lead time <?= htmlspecialchars($products['wheels_forged']['lead_time']) ?></p></div>
            </div>
            <a href="/members/new-order.php?product=<?= urlencode($p['sku']) ?>"
               class="block mt-4 py-2 text-center text-xs font-mono font-bold uppercase"
               style="background:#ffd165;color:#081425;">
              Request Custom Quote â†’
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
           SECTION 3: TPMS
      â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
      <div class="mb-8">
        <div class="section-header">TPMS Sensors</div>

        <?php foreach ($products['tpms']['brands'] as $brand): ?>
        <div class="mb-8">
          <div class="flex items-center gap-3 mb-4 px-4 py-3 border" style="background:#0c1c30;border-color:#1e3455;">
            <div>
              <p style="font-family:'Space Grotesk',sans-serif;font-weight:700;"><?= htmlspecialchars($brand['name']) ?></p>
              <p class="text-xs mt-1" style="color:#8da0b3;"><?= htmlspecialchars($brand['tagline']) ?></p>
            </div>
            <div class="ml-auto text-right">
              <p class="text-xs font-mono" style="color:#4a6278;">Coverage</p>
              <p class="text-xs font-semibold"><?= htmlspecialchars($brand['coverage']) ?></p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            <?php foreach ($brand['products'] as $p): ?>
            <div class="product-card p-5">
              <div class="w-full mb-4 flex items-center justify-center" style="height:140px;background:#0c1c30;border:1px solid #1e3455;">
                <span style="color:#1e3455;font-size:3rem;" class="material-symbols-outlined">sensors</span>
              </div>
              <div class="flex items-start justify-between mb-2">
                <div>
                  <p style="font-family:'Space Grotesk',sans-serif;font-weight:700;"><?= htmlspecialchars($p['name']) ?></p>
                  <p class="text-xs font-mono mt-1" style="color:#4a6278;"><?= htmlspecialchars($p['sku']) ?></p>
                </div>
                <?= stock_badge($p['stock_status']) ?>
              </div>
              <div class="space-y-2 mt-3">
                <div><p class="spec-label">Type</p><p class="text-xs"><?= htmlspecialchars($p['type']) ?></p></div>
                <div><p class="spec-label">Frequency</p><p class="text-xs"><?= htmlspecialchars($p['frequency']) ?></p></div>
                <div><p class="spec-label">Coverage</p><p class="text-xs"><?= htmlspecialchars($p['coverage']) ?></p></div>
                <div><p class="spec-label">Battery</p><p class="text-xs"><?= htmlspecialchars($p['battery']) ?></p></div>
                <div><p class="spec-label">MOQ</p><p class="text-xs"><?= $p['moq'] ?> sets</p></div>
              </div>
              <a href="/members/new-order.php?product=<?= urlencode($p['sku']) ?>"
                 class="block mt-4 py-2 text-center text-xs font-mono font-bold uppercase"
                 style="border:1px solid #ffd16540;color:#ffd165;">
                Inquire â†’
              </a>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Footer note -->
      <div class="p-5 border" style="border-color:#1e3455;background:#0c1c30;">
        <p class="text-xs" style="color:#4a6278;">
          Catalog updated periodically. Stock availability is indicative only â€” confirm with Sales before placing bulk orders.
          Custom specifications, OEM packaging, and private label available on request.
          Contact <a href="mailto:Sales@nexautogear.com" style="color:#ffd165;">Sales@nexautogear.com</a> for quotes.
        </p>
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


