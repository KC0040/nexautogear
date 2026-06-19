<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_once __DIR__ . '/../includes/crm_webhook.php';
require_member();

$memberId = $_SESSION['member_id'];
$stmt = db()->prepare('SELECT * FROM b2b_members WHERE id = ?');
$stmt->execute([$memberId]);
$member = $stmt->fetch();

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product  = trim($_POST['product_line'] ?? '');
    $items    = trim($_POST['items'] ?? '');
    $qty      = (int)($_POST['quantity'] ?? 0);
    $notes    = trim($_POST['notes'] ?? '');
    $shipping = trim($_POST['shipping_country'] ?? '');

    if (!$product || !$items || $qty < 1) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
        $docPath  = upload_document('document', 'orders');
        $orderNum = 'NEX-' . strtoupper(substr(md5(uniqid()), 0, 8));
        db()->prepare('INSERT INTO b2b_orders (member_id, order_number, notes, document_path, status, created_at)
                       VALUES (?, ?, ?, ?, "pending", NOW())')
           ->execute([$memberId, $orderNum,
               "Product: $product\nItems: $items\nQty: $qty\nShip to: $shipping\nNotes: $notes",
               $docPath]);

        // Notify admin
        $body  = "New B2B Order Inquiry\n\n";
        $body .= "Member:   {$member['company']} ({$member['contact_name']})\n";
        $body .= "Email:    {$member['email']}\n";
        $body .= "Order #:  $orderNum\n\n";
        $body .= "Product:  $product\n";
        $body .= "Items:    $items\n";
        $body .= "Qty:      $qty units\n";
        $body .= "Ship to:  $shipping\n\n";
        $body .= "Notes:    $notes\n\n";
        $body .= "Review at: https://www.nexautogear.com/admin/\n";
        mail('Sales@nexautogear.com', "New Order Inquiry [$orderNum] â€” {$member['company']}", $body,
            "From: noreply@nexautogear.com\r\nReply-To: {$member['email']}");

        crm_push('new_order', [
            'order_number' => $orderNum,
            'company'      => $member['company'],
            'email'        => $member['email'],
            'account_tier' => $member['account_tier'],
            'product_line' => $product,
            'items'        => $items,
            'quantity'     => $qty,
            'ship_to'      => $shipping,
            'notes'        => $notes,
        ]);
        $success = true;
        } catch (RuntimeException $e) { $error = $e->getMessage(); }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>New Order Inquiry | NEX AUTO GEAR</title>
  <meta name="robots" content="noindex"/>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body{background:#081425;color:#e8edf2;font-family:'Inter',sans-serif;}
    .sidebar-link{display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:.8rem;font-family:'JetBrains Mono',monospace;text-transform:uppercase;letter-spacing:.06em;color:#8da0b3;transition:all .2s;}
    .sidebar-link:hover,.sidebar-link.active{color:#ffd165;background:#122440;}
    label{display:block;font-size:.7rem;font-family:'JetBrains Mono',monospace;text-transform:uppercase;letter-spacing:.08em;color:#8da0b3;margin-bottom:6px;}
    input,select,textarea{width:100%;background:#122440;border:1px solid #1e3455;color:#e8edf2;padding:11px 14px;font-family:'Inter',sans-serif;font-size:.875rem;outline:none;transition:border-color .2s;}
    input:focus,select:focus,textarea:focus{border-color:#ffd165;}
    select option{background:#122440;}
    textarea{resize:vertical;min-height:100px;}
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
    <a href="/members/dashboard.php" class="sidebar-link">
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
    <a href="/members/new-order.php" class="sidebar-link active">
      <span class="material-symbols-outlined" style="font-size:1rem;">send</span> New Inquiry
    </a>
    <div class="my-4" style="border-top:1px solid #1e3455;"></div>
    <a href="/members/support.php" class="sidebar-link">
      <span class="material-symbols-outlined" style="font-size:1rem;">support_agent</span> Support
    </a>
  </aside>

  <!-- MAIN -->
  <main class="flex-1 p-8 overflow-auto">
    <div class="max-w-2xl">

      <div class="mb-8">
        <h1 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.5rem;" class="mb-1">New Order Inquiry</h1>
        <p style="color:#8da0b3;font-size:.875rem;">Submit your order request. Our team will confirm pricing and availability within 24 hours.</p>
      </div>

      <?php if ($success): ?>
      <div class="p-6 border mb-6" style="border-color:#ffd165;background:#ffd16510;">
        <p style="font-family:'Space Grotesk',sans-serif;font-weight:700;color:#ffd165;" class="mb-1">Inquiry Submitted</p>
        <p style="color:#8da0b3;font-size:.875rem;">Our sales team will contact you within 24 hours to confirm pricing and availability.</p>
        <div class="mt-4 flex gap-3">
          <a href="/members/dashboard.php" class="text-xs font-mono px-4 py-2" style="background:#ffd165;color:#081425;font-weight:700;">â† Back to Dashboard</a>
          <a href="/members/new-order.php" class="text-xs font-mono px-4 py-2" style="border:1px solid #1e3455;color:#8da0b3;">New Inquiry</a>
        </div>
      </div>

      <?php else: ?>

      <?php if ($error): ?>
      <div class="p-4 mb-6 border text-sm font-mono" style="border-color:#ef4444;background:#ef444415;color:#ef4444;"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" class="space-y-6">

        <!-- Product Line -->
        <div>
          <label>Product Line <span style="color:#ef4444;">*</span></label>
          <select name="product_line" required>
            <option value="">â€” Select product category â€”</option>
            <optgroup label="AegisRim Wheels">
              <option value="AegisRim â€” Flow Series (Cast)">Flow Series (Cast)</option>
              <option value="AegisRim â€” Sport Series (Flow Formed)">Sport Series (Flow Formed)</option>
              <option value="AegisRim â€” Pro Series (Forged)">Pro Series (Forged)</option>
              <option value="AegisRim â€” Custom / OEM">Custom / OEM</option>
            </optgroup>
            <optgroup label="NEX TPMS">
              <option value="NEX TPMS â€” Universal Sensor">Universal Sensor</option>
              <option value="NEX TPMS â€” OEM Replacement">OEM Replacement</option>
              <option value="NEX TPMS â€” Fleet Kit">Fleet Kit</option>
            </optgroup>
            <optgroup label="Pressure Mind TPMS">
              <option value="Pressure Mind â€” Standard Kit">Standard Kit</option>
              <option value="Pressure Mind â€” Pro Kit">Pro Kit</option>
            </optgroup>
          </select>
        </div>

        <!-- Items / Spec -->
        <div>
          <label>Item Details / Specifications <span style="color:#ef4444;">*</span></label>
          <textarea name="items" required placeholder="e.g. 18Ã—8.5 ET35 5Ã—114.3, Matte Black â€” or â€” NEX-T1 Universal 315MHz kit&#10;List each item on a new line if multiple SKUs"><?= htmlspecialchars($_POST['items'] ?? '') ?></textarea>
        </div>

        <!-- Qty + Shipping side by side -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label>Total Quantity (units) <span style="color:#ef4444;">*</span></label>
            <input type="number" name="quantity" min="1" placeholder="e.g. 200" value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>" required/>
          </div>
          <div>
            <label>Ship To (Country)</label>
            <input type="text" name="shipping_country" placeholder="e.g. United States" value="<?= htmlspecialchars($_POST['shipping_country'] ?? '') ?>"/>
          </div>
        </div>

        <!-- PO Upload -->
        <div>
          <label>Purchase Order / Reference Document <span style="color:#4a6278;font-size:.65rem;">(Optional â€” PDF/JPG max 8MB)</span></label>
          <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" style="width:100%;background:#122440;border:1px solid #1e3455;color:#e8edf2;padding:11px 14px;cursor:pointer;"/>
        </div>

        <!-- Notes -->
        <div>
          <label>Additional Notes</label>
          <textarea name="notes" placeholder="Target price, timeline, packaging requirements, certifications needed, etc."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
        </div>

        <!-- Member Info (read-only reminder) -->
        <div class="p-4 border" style="background:#0c1c30;border-color:#1e3455;">
          <p class="text-xs font-mono uppercase mb-2" style="color:#4a6278;">Inquiry will be sent from</p>
          <p class="text-sm"><?= htmlspecialchars($member['contact_name']) ?> Â· <?= htmlspecialchars($member['company']) ?></p>
          <p class="text-xs mt-1" style="color:#8da0b3;"><?= htmlspecialchars($member['email']) ?></p>
        </div>

        <button type="submit" class="w-full py-3 text-sm font-mono font-bold uppercase tracking-widest" style="background:#ffd165;color:#081425;">
          Submit Inquiry â†’
        </button>

      </form>
      <?php endif; ?>

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


