<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/upload.php';
require_once __DIR__ . '/../includes/crm_webhook.php';

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company   = trim(strip_tags($_POST['company'] ?? ''));
    $name      = trim(strip_tags($_POST['contact_name'] ?? ''));
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim(strip_tags($_POST['phone'] ?? ''));
    $country   = trim(strip_tags($_POST['country'] ?? ''));
    $biz_type  = trim(strip_tags($_POST['business_type'] ?? ''));
    $products  = trim(strip_tags($_POST['products_interest'] ?? ''));
    $volume    = trim(strip_tags($_POST['annual_volume'] ?? ''));
    $address   = trim(strip_tags($_POST['shipping_address'] ?? ''));
    $message   = trim(strip_tags($_POST['message'] ?? ''));

    if (!$company || !$name || !$email) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        try {
            $docPath = upload_document('document', 'applications');
            $stmt = db()->prepare('INSERT INTO b2b_applications (company,contact_name,email,phone,country,business_type,products_interest,annual_volume,shipping_address,document_path,message) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$company,$name,$email,$phone,$country,$biz_type,$products,$volume,$address,$docPath,$message]);

            // Notify admin
            mail('Sales@nexautogear.com',
                "New B2B Application: $company",
                "Company: $company\nContact: $name\nEmail: $email\nCountry: $country\nProducts: $products\n\nMessage:\n$message\n\nReview at: https://www.nexautogear.com/admin/",
                "From: noreply@nexautogear.com\r\nReply-To: $email"
            );
            crm_push('b2b_application', [
                'company'          => $company,
                'contact_name'     => $name,
                'email'            => $email,
                'phone'            => $phone,
                'country'          => $country,
                'business_type'    => $biz_type,
                'products_interest'=> $products,
                'annual_volume'    => $volume,
                'shipping_address' => $address,
            ]);
            $success = true;
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'This email is already registered. <a href="/members/login.php" style="color:#ffd165">Sign in instead</a>.';
            } else {
                $error = 'Submission failed. Please email Sales@nexautogear.com directly.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Apply for B2B Access | NEX AUTO GEAR</title>
  <meta name="description" content="Apply for a NEXAutogear B2B wholesale account. Factory-direct pricing on AegisRim forged wheels and TPMS sensors."/>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body{background:#081425;color:#e8edf2;font-family:'Inter',sans-serif;}
    input,select,textarea{background:#122440;border:1px solid #1e3455;color:#e8edf2;outline:none;transition:border-color .2s;}
    input:focus,select:focus,textarea:focus{border-color:#ffd165;}
    input::placeholder,textarea::placeholder{color:#4a6278;}
    select option{background:#122440;}
    label{color:#8da0b3;font-size:.75rem;font-family:'JetBrains Mono',monospace;text-transform:uppercase;letter-spacing:.08em;}
  </style>
</head>
<body class="min-h-screen py-16 px-4">
  <!-- NAV -->
  <nav class="fixed top-0 left-0 right-0 z-50 border-b flex items-center justify-between px-6 h-14" style="background:#081425ee;border-color:#1e3455;backdrop-filter:blur(12px)">
    <a href="/" style="font-family:'Space Grotesk',sans-serif;font-weight:700;letter-spacing:-.02em;">
      <span style="color:#e8edf2">NEX</span><span style="color:#ffd165">AUTO</span><span style="color:#e8edf2">GEAR</span>
    </a>
    <a href="/members/login.php" class="text-xs font-mono" style="color:#8da0b3;">Already a member? Sign In →</a>
  </nav>

  <div class="max-w-2xl mx-auto mt-10">
    <div class="mb-10">
      <p class="text-xs font-mono uppercase tracking-widest mb-4" style="color:#ffd165;">B2B Program</p>
      <h1 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:2rem;" class="uppercase mb-3">Apply for<br/>Wholesale Access</h1>
      <p style="color:#8da0b3;">Our team reviews all applications within 1–2 business days. Approved accounts receive login credentials and full access to B2B pricing.</p>
    </div>

    <?php if ($success): ?>
    <div class="border p-8 text-center" style="border-color:#ffd165;background:#122440;">
      <div class="text-4xl mb-4">✓</div>
      <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.25rem;" class="mb-3">Application Received</h2>
      <p style="color:#8da0b3;">We'll review your application and send login credentials to <strong style="color:#e8edf2;"><?= htmlspecialchars($_POST['email'] ?? '') ?></strong> within 1–2 business days.</p>
      <a href="/" class="inline-block mt-6 text-sm font-mono" style="color:#ffd165;">← Back to Homepage</a>
    </div>
    <?php else: ?>

    <?php if ($error): ?>
    <div class="border px-4 py-3 text-sm font-mono mb-6" style="border-color:#ef4444;background:rgba(239,68,68,.1);color:#fca5a5;"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-5 p-8 border" style="background:#122440;border-color:#1e3455;">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block mb-2">Company Name *</label>
          <input type="text" name="company" required placeholder="ABC Tire & Wheel" class="w-full px-4 py-3 text-sm"/>
        </div>
        <div>
          <label class="block mb-2">Contact Name *</label>
          <input type="text" name="contact_name" required placeholder="John Smith" class="w-full px-4 py-3 text-sm"/>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block mb-2">Business Email *</label>
          <input type="email" name="email" required placeholder="you@company.com" class="w-full px-4 py-3 text-sm"/>
        </div>
        <div>
          <label class="block mb-2">Phone / WhatsApp</label>
          <input type="text" name="phone" placeholder="+1 555 000 0000" class="w-full px-4 py-3 text-sm"/>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block mb-2">Country</label>
          <input type="text" name="country" placeholder="United States" class="w-full px-4 py-3 text-sm"/>
        </div>
        <div>
          <label class="block mb-2">Business Type</label>
          <select name="business_type" class="w-full px-4 py-3 text-sm">
            <option value="">Select...</option>
            <option>Tire Shop / Installer</option>
            <option>Distributor / Wholesaler</option>
            <option>Fleet Operator</option>
            <option>Auto Parts Retailer</option>
            <option>OEM / Manufacturer</option>
            <option>Other</option>
          </select>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block mb-2">Products of Interest</label>
          <select name="products_interest" class="w-full px-4 py-3 text-sm">
            <option value="">Select...</option>
            <option>AegisRim Forged Wheels</option>
            <option>NEX TPMS Sensors</option>
            <option>Pressure Mind TPMS</option>
            <option>Wheels + TPMS Bundle</option>
            <option>All Products</option>
          </select>
        </div>
        <div>
          <label class="block mb-2">Estimated Annual Volume</label>
          <select name="annual_volume" class="w-full px-4 py-3 text-sm">
            <option value="">Select...</option>
            <option>Under 500 units</option>
            <option>500 – 2,000 units</option>
            <option>2,000 – 10,000 units</option>
            <option>10,000+ units</option>
          </select>
        </div>
      </div>
      <div>
        <label class="block mb-2">Company / Shipping Address</label>
        <textarea name="shipping_address" rows="3" placeholder="Street address, city, state/province, ZIP, country" class="w-full px-4 py-3 text-sm resize-none"></textarea>
      </div>
      <div>
        <label class="block mb-2">Business Document <span style="color:#4a6278;font-size:.65rem;">(Optional — Business License, Tax ID, etc. PDF/JPG max 8MB)</span></label>
        <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-3 text-sm" style="cursor:pointer;"/>
      </div>
      <div>
        <label class="block mb-2">Additional Notes</label>
        <textarea name="message" rows="4" placeholder="Tell us about your business, current suppliers, or any specific requirements..." class="w-full px-4 py-3 text-sm resize-none"></textarea>
      </div>
      <button type="submit" class="w-full py-4 text-sm font-mono font-bold uppercase tracking-widest" style="background:#ffd165;color:#081425;">
        Submit Application →
      </button>
      <p class="text-xs text-center" style="color:#4a6278;">Applications reviewed within 1–2 business days. No spam.</p>
    </form>
    <?php endif; ?>
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

