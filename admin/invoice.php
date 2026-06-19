<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_admin();

$orderId = (int)($_GET['id'] ?? 0);
if (!$orderId) { header('Location: /admin/'); exit; }

$stmt = db()->prepare('SELECT o.*, m.company, m.contact_name, m.email, m.country, m.shipping_address
                       FROM b2b_orders o JOIN b2b_members m ON o.member_id=m.id WHERE o.id=?');
$stmt->execute([$orderId]);
$order = $stmt->fetch();
if (!$order) { header('Location: /admin/'); exit; }

$flash = '';

// Upload quote/invoice PDF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'upload_quote') {
        try {
            $path = upload_document('quote_file', 'quotes');
            if ($path) {
                db()->prepare('UPDATE b2b_orders SET quote_path=?, status="confirmed", confirmed_at=NOW() WHERE id=?')
                   ->execute([$path, $orderId]);
                // Email member
                $dl  = "https://www.nexautogear.com/admin/download.php?type=quote&id=$orderId";
                $body = "Hello {$order['contact_name']},\n\n";
                $body .= "Your order {$order['order_number']} has been reviewed.\n\n";
                $body .= "Please find your quotation attached or download it here:\n$dl\n\n";
                $body .= "To confirm your order, please reply to this email or contact Sales@nexautogear.com.\n\n";
                $body .= "NEXAutogear Sales Team";
                mail($order['email'], "Quotation for {$order['order_number']} — NEXAutogear", $body,
                    "From: Sales@nexautogear.com\r\nReply-To: Sales@nexautogear.com");
                $flash = "Quote uploaded and sent to {$order['email']}. Order status → Confirmed.";
            }
        } catch (RuntimeException $e) { $flash = 'Upload error: ' . $e->getMessage(); }
    } elseif ($_POST['action'] === 'update_status') {
        $status = $_POST['status'] ?? '';
        $track  = trim($_POST['tracking'] ?? '');
        $validStatuses = ['pending','confirmed','processing','shipped','delivered','cancelled'];
        if (in_array($status, $validStatuses)) {
            db()->prepare('UPDATE b2b_orders SET status=?, tracking_number=? WHERE id=?')
               ->execute([$status, $track ?: null, $orderId]);
            if ($status === 'shipped' && $track) {
                $body  = "Hello {$order['contact_name']},\n\nYour order {$order['order_number']} has shipped!\n\n";
                $body .= "Tracking: $track\n\nThank you for your business.\nNEXAutogear Sales Team";
                mail($order['email'], "Your Order {$order['order_number']} Has Shipped", $body,
                    "From: Sales@nexautogear.com\r\nReply-To: Sales@nexautogear.com");
            }
            $flash = "Order updated to: $status";
            // Reload order data
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
        }
    }
}

$statusColors = ['pending'=>'#f59e0b','confirmed'=>'#3b82f6','processing'=>'#8b5cf6','shipped'=>'#10b981','delivered'=>'#22c55e','cancelled'=>'#ef4444'];
$sc = $statusColors[$order['status']] ?? '#8da0b3';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Order <?= htmlspecialchars($order['order_number']) ?> | NEX Admin</title>
  <meta name="robots" content="noindex"/>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body{background:#081425;color:#e8edf2;font-family:'Inter',sans-serif;}
    label{display:block;font-size:.7rem;font-family:'JetBrains Mono',monospace;text-transform:uppercase;letter-spacing:.08em;color:#8da0b3;margin-bottom:5px;}
    input,select{background:#122440;border:1px solid #1e3455;color:#e8edf2;padding:9px 12px;font-family:'JetBrains Mono',monospace;font-size:.8rem;width:100%;outline:none;}
    input:focus,select:focus{border-color:#ffd165;}
    select option{background:#122440;}
  </style>
</head>
<body class="min-h-screen">

<header class="h-14 border-b flex items-center justify-between px-6 sticky top-0 z-50" style="background:#081425;border-color:#1e3455;">
  <div class="flex items-center gap-4">
    <a href="/admin/" class="text-xs font-mono" style="color:#8da0b3;">← Back</a>
    <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;color:#ffd165;">NEX ADMIN</span>
  </div>
  <a href="/admin/logout.php" class="text-xs font-mono" style="color:#8da0b3;">Sign Out</a>
</header>

<div class="max-w-4xl mx-auto px-6 py-8">

  <?php if ($flash): ?>
  <div class="mb-6 px-4 py-3 border text-sm font-mono" style="border-color:#ffd165;background:#ffd16515;color:#ffd165;"><?= htmlspecialchars($flash) ?></div>
  <?php endif; ?>

  <!-- Order Header -->
  <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
    <div>
      <h1 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.5rem;" class="mb-1"><?= htmlspecialchars($order['order_number']) ?></h1>
      <p class="text-sm" style="color:#8da0b3;"><?= date('F j, Y', strtotime($order['created_at'])) ?></p>
    </div>
    <span class="text-sm font-mono px-3 py-2" style="background:<?=$sc?>22;color:<?=$sc?>;border:1px solid <?=$sc?>44;"><?= strtoupper($order['status']) ?></span>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Left: Details -->
    <div class="lg:col-span-2 space-y-6">

      <!-- Customer -->
      <div class="p-5 border" style="background:#122440;border-color:#1e3455;">
        <p class="text-xs font-mono uppercase mb-4" style="color:#4a6278;">Customer</p>
        <p class="font-medium mb-1"><?= htmlspecialchars($order['company']) ?></p>
        <p class="text-sm" style="color:#8da0b3;"><?= htmlspecialchars($order['contact_name']) ?></p>
        <p class="text-sm" style="color:#8da0b3;"><?= htmlspecialchars($order['email']) ?></p>
        <?php if ($order['country']): ?>
        <p class="text-sm mt-1" style="color:#8da0b3;"><?= htmlspecialchars($order['country']) ?></p>
        <?php endif; ?>
        <?php if ($order['shipping_address']): ?>
        <p class="text-xs mt-2 p-3" style="background:#0c1c30;color:#8da0b3;white-space:pre-line;"><?= htmlspecialchars($order['shipping_address']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Order Notes -->
      <div class="p-5 border" style="background:#122440;border-color:#1e3455;">
        <p class="text-xs font-mono uppercase mb-4" style="color:#4a6278;">Order Details</p>
        <pre class="text-sm whitespace-pre-wrap" style="color:#8da0b3;font-family:'Inter',sans-serif;"><?= htmlspecialchars($order['notes']) ?></pre>
      </div>

      <!-- Customer PO document -->
      <?php if ($order['document_path']): ?>
      <div class="p-5 border" style="background:#122440;border-color:#1e3455;">
        <p class="text-xs font-mono uppercase mb-3" style="color:#4a6278;">Customer Document (PO / Reference)</p>
        <a href="/admin/download.php?type=order_doc&id=<?= $orderId ?>"
           class="inline-flex items-center gap-2 text-sm font-mono px-4 py-2" style="background:#0c1c30;color:#ffd165;border:1px solid #1e3455;">
          ↓ Download Document
        </a>
      </div>
      <?php endif; ?>

      <!-- Quote document -->
      <?php if ($order['quote_path']): ?>
      <div class="p-5 border" style="border-color:#10b98140;background:#10b98108;">
        <p class="text-xs font-mono uppercase mb-3" style="color:#10b981;">Quotation Sent</p>
        <a href="/admin/download.php?type=quote&id=<?= $orderId ?>"
           class="inline-flex items-center gap-2 text-sm font-mono px-4 py-2" style="background:#0c1c30;color:#10b981;border:1px solid #10b98140;">
          ↓ View / Download Quote
        </a>
        <?php if ($order['confirmed_at']): ?>
        <p class="text-xs mt-2" style="color:#4a6278;">Sent <?= date('M j, Y H:i', strtotime($order['confirmed_at'])) ?></p>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    </div>

    <!-- Right: Actions -->
    <div class="space-y-5">

      <!-- Upload Quote -->
      <div class="p-5 border" style="background:#122440;border-color:#1e3455;">
        <p class="text-xs font-mono uppercase mb-4" style="color:#ffd165;">Send Quotation</p>
        <p class="text-xs mb-4" style="color:#8da0b3;">Upload your quote PDF. The customer will receive an email with a download link and status will change to "Confirmed".</p>
        <form method="POST" enctype="multipart/form-data" class="space-y-3">
          <input type="hidden" name="action" value="upload_quote"/>
          <div>
            <label>Quote / Invoice PDF</label>
            <input type="file" name="quote_file" accept=".pdf" required style="cursor:pointer;"/>
          </div>
          <button type="submit" class="w-full py-2 text-xs font-mono font-bold uppercase" style="background:#ffd165;color:#081425;">
            Upload & Email Customer →
          </button>
        </form>
      </div>

      <!-- Update Status -->
      <div class="p-5 border" style="background:#122440;border-color:#1e3455;">
        <p class="text-xs font-mono uppercase mb-4" style="color:#4a6278;">Update Status</p>
        <form method="POST" class="space-y-3">
          <input type="hidden" name="action" value="update_status"/>
          <div>
            <label>Status</label>
            <select name="status">
              <?php foreach(['pending','confirmed','processing','shipped','delivered','cancelled'] as $s): ?>
              <option value="<?=$s?>" <?= $order['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label>Tracking Number</label>
            <input type="text" name="tracking" placeholder="Optional" value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>"/>
          </div>
          <button type="submit" class="w-full py-2 text-xs font-mono font-bold uppercase" style="border:1px solid #ffd165;color:#ffd165;background:transparent;">
            Update Order
          </button>
        </form>
      </div>

      <!-- Financial -->
      <div class="p-5 border" style="background:#122440;border-color:#1e3455;">
        <p class="text-xs font-mono uppercase mb-4" style="color:#4a6278;">Payment</p>
        <p class="text-xs mb-1" style="color:#8da0b3;">Amount</p>
        <p class="font-mono text-lg font-bold"><?= $order['total_usd'] ? '$'.number_format($order['total_usd'],2).' USD' : 'TBD' ?></p>
        <?php if ($order['payment_method']): ?>
        <p class="text-xs mt-2" style="color:#8da0b3;">Method: <?= htmlspecialchars($order['payment_method']) ?></p>
        <?php endif; ?>
      </div>

    </div>
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

