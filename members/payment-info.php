<?php
require_once __DIR__ . '/../includes/auth.php';
require_member();
$member = db()->prepare('SELECT * FROM b2b_members WHERE id = ?');
$member->execute([$_SESSION['member_id']]);
$member = $member->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Payment Information | NEX AUTO GEAR</title>
  <meta name="robots" content="noindex"/>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body{background:#081425;color:#e8edf2;font-family:'Inter',sans-serif;}
    .sidebar-link{display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:.8rem;font-family:'JetBrains Mono',monospace;text-transform:uppercase;letter-spacing:.06em;color:#8da0b3;transition:all .2s;}
    .sidebar-link:hover,.sidebar-link.active{color:#ffd165;background:#122440;}
    .info-box{background:#122440;border:1px solid #1e3455;padding:24px;}
    .info-label{font-family:'JetBrains Mono',monospace;font-size:.65rem;text-transform:uppercase;letter-spacing:.08em;color:#4a6278;margin-bottom:4px;}
    .info-val{font-family:'JetBrains Mono',monospace;font-size:.875rem;color:#e8edf2;user-select:all;}
    .copy-btn{font-size:.65rem;font-family:'JetBrains Mono',monospace;text-transform:uppercase;padding:2px 8px;background:#ffd16520;color:#ffd165;border:1px solid #ffd16540;cursor:pointer;letter-spacing:.06em;}
    .copy-btn:hover{background:#ffd16540;}
  </style>
</head>
<body class="min-h-screen">

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
  <aside class="w-52 border-r shrink-0 py-6" style="border-color:#1e3455;background:#0c1c30;">
    <a href="/members/dashboard.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">dashboard</span> Dashboard</a>
    <a href="/members/orders.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">package_2</span> Orders</a>
    <a href="/members/pricelists.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">price_check</span> Price List</a>
    <a href="/members/catalog.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">menu_book</span> Catalog</a>
    <a href="/members/new-order.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">send</span> New Inquiry</a>
    <a href="/members/payment-info.php" class="sidebar-link active"><span class="material-symbols-outlined" style="font-size:1rem;">account_balance</span> Payment Info</a>
    <div class="my-4" style="border-top:1px solid #1e3455;"></div>
    <a href="/members/support.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">support_agent</span> Support</a>
  </aside>

  <main class="flex-1 p-8 overflow-auto">
    <div class="max-w-3xl">
      <div class="mb-8">
        <h1 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.5rem;" class="mb-1">Payment Information</h1>
        <p style="color:#8da0b3;font-size:.875rem;">Use the details below to remit payment after receiving your invoice. Reference your Order # on all payments.</p>
      </div>

      <!-- US ACH / Zelle -->
      <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
          <span class="text-xs font-mono px-3 py-1 uppercase" style="background:#10b98120;color:#10b981;border:1px solid #10b98140;">US Customers</span>
          <span style="color:#8da0b3;font-size:.8rem;">ACH Transfer Â· Zelle</span>
        </div>
        <div class="info-box space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="info-label">Bank Name</p>
              <p class="info-val">Novo</p>
            </div>
            <div>
              <p class="info-label">Account Name</p>
              <p class="info-val">NEXAutogear LLC</p>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="info-label">Routing Number (ABA)</p>
              <div class="flex items-center gap-2 mt-1">
                <p class="info-val" id="routing">â€” contact Sales â€”</p>
                <button class="copy-btn" onclick="copyText('routing')">Copy</button>
              </div>
            </div>
            <div>
              <p class="info-label">Account Number</p>
              <div class="flex items-center gap-2 mt-1">
                <p class="info-val" id="account">â€” contact Sales â€”</p>
                <button class="copy-btn" onclick="copyText('account')">Copy</button>
              </div>
            </div>
          </div>
          <div class="pt-3" style="border-top:1px solid #1e3455;">
            <p class="info-label">Zelle</p>
            <div class="flex items-center gap-2 mt-1">
              <p class="info-val" id="zelle">Sales@nexautogear.com</p>
              <button class="copy-btn" onclick="copyText('zelle')">Copy</button>
            </div>
            <p class="text-xs mt-2" style="color:#4a6278;">Zelle available for orders under $5,000 USD</p>
          </div>
        </div>
        <p class="text-xs mt-3" style="color:#4a6278;">âš¡ ACH typically settles in 1â€“3 business days. Please include your Order # in the payment memo.</p>
      </div>

      <!-- International Wire -->
      <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
          <span class="text-xs font-mono px-3 py-1 uppercase" style="background:#3b82f620;color:#3b82f6;border:1px solid #3b82f640;">International</span>
          <span style="color:#8da0b3;font-size:.8rem;">Wire Transfer (SWIFT)</span>
        </div>
        <div class="info-box space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="info-label">Bank Name</p>
              <p class="info-val">Novo (Middlesex Federal Savings)</p>
            </div>
            <div>
              <p class="info-label">SWIFT / BIC Code</p>
              <div class="flex items-center gap-2 mt-1">
                <p class="info-val" id="swift">â€” contact Sales â€”</p>
                <button class="copy-btn" onclick="copyText('swift')">Copy</button>
              </div>
            </div>
          </div>
          <div>
            <p class="info-label">Beneficiary Address</p>
            <p class="info-val text-sm">NEXAutogear LLC Â· United States</p>
          </div>
          <div class="pt-3" style="border-top:1px solid #1e3455;">
            <p class="text-xs" style="color:#8da0b3;">International wire fees are typically $15â€“40 per transfer, charged by your bank. Please send the exact invoice amount â€” NEXAutogear does not cover sender fees.</p>
          </div>
        </div>
      </div>

      <!-- Europe note -->
      <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
          <span class="text-xs font-mono px-3 py-1 uppercase" style="background:#8b5cf620;color:#8b5cf6;border:1px solid #8b5cf640;">Europe</span>
          <span style="color:#8da0b3;font-size:.8rem;">SEPA / Wise</span>
        </div>
        <div class="info-box">
          <p style="color:#8da0b3;font-size:.875rem;">For European customers, we can provide EUR bank details via Wise (local IBAN). Please contact <a href="mailto:Sales@nexautogear.com" style="color:#ffd165;">Sales@nexautogear.com</a> to request EUR payment instructions â€” fees are typically under 1%.</p>
        </div>
      </div>

      <!-- Important notes -->
      <div class="info-box" style="border-color:#ffd16540;background:#ffd16508;">
        <p class="text-xs font-mono uppercase mb-3" style="color:#ffd165;">Important</p>
        <ul class="space-y-2 text-sm" style="color:#8da0b3;">
          <li>Â· Always reference your <strong style="color:#e8edf2;">Order Number</strong> (NEX-XXXXXXXX) in the payment memo</li>
          <li>Â· Orders are confirmed after payment is received and stock is verified with our factory</li>
          <li>Â· For full banking details, contact <a href="mailto:Sales@nexautogear.com" style="color:#ffd165;">Sales@nexautogear.com</a></li>
          <li>Â· We do not accept personal checks or cash payments</li>
        </ul>
      </div>

    </div>
  </main>
</div>

<script>
function copyText(id) {
  var el = document.getElementById(id);
  navigator.clipboard.writeText(el.textContent.trim()).then(function() {
    var btn = el.nextElementSibling;
    var orig = btn.textContent;
    btn.textContent = 'Copied!';
    btn.style.color = '#10b981';
    setTimeout(function(){ btn.textContent = orig; btn.style.color = ''; }, 1500);
  });
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


