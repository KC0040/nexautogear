<?php
require_once __DIR__ . '/../includes/auth.php';
require_member();

$memberId = $_SESSION['member_id'];
$stmt = db()->prepare('SELECT * FROM b2b_members WHERE id = ?');
$stmt->execute([$memberId]);
$member = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Support | NEX AUTO GEAR</title>
  <meta name="robots" content="noindex"/>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body{background:#081425;color:#e8edf2;font-family:'Inter',sans-serif;}
    .sidebar-link{display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:.8rem;font-family:'JetBrains Mono',monospace;text-transform:uppercase;letter-spacing:.06em;color:#8da0b3;transition:all .2s;}
    .sidebar-link:hover,.sidebar-link.active{color:#ffd165;background:#122440;}

    /* Mode toggle */
    .mode-btn{flex:1;padding:10px;font-family:'JetBrains Mono',monospace;font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;border:1px solid #1e3455;background:transparent;color:#8da0b3;cursor:pointer;transition:all .2s;}
    .mode-btn.active{background:#ffd165;color:#081425;border-color:#ffd165;font-weight:700;}

    /* Chat */
    #chat-messages{height:340px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;padding:16px;scroll-behavior:smooth;}
    .msg-user{align-self:flex-end;background:#ffd165;color:#081425;padding:8px 14px;max-width:75%;font-size:.85rem;line-height:1.5;}
    .msg-agent{align-self:flex-start;background:#1a2e48;color:#e8edf2;padding:8px 14px;max-width:82%;font-size:.85rem;line-height:1.5;}
    .msg-agent .agent-label{font-size:.62rem;font-family:'JetBrains Mono',monospace;color:#8da0b3;margin-bottom:4px;text-transform:uppercase;letter-spacing:.06em;}
    .msg-image{align-self:flex-end;max-width:200px;border:1px solid #1e3455;}
    .msg-image img{width:100%;display:block;}

    select,textarea{background:#0c1c30;border:1px solid #1e3455;color:#e8edf2;padding:8px 12px;font-size:.82rem;font-family:'Inter',sans-serif;width:100%;outline:none;}
    select{font-family:'JetBrains Mono',monospace;font-size:.78rem;cursor:pointer;}
    select:focus,textarea:focus{border-color:#ffd16550;}
    textarea{resize:none;}

    .typing-dot{width:6px;height:6px;background:#8da0b3;border-radius:50%;display:inline-block;animation:blink 1.2s infinite;}
    .typing-dot:nth-child(2){animation-delay:.2s;}
    .typing-dot:nth-child(3){animation-delay:.4s;}
    @keyframes blink{0%,80%,100%{opacity:0;}40%{opacity:1;}}

    /* Upload button */
    #upload-btn{background:none;border:1px solid #1e3455;color:#8da0b3;padding:0 10px;cursor:pointer;transition:border-color .2s;flex-shrink:0;}
    #upload-btn:hover{border-color:#ffd16550;color:#ffd165;}
    #file-input{display:none;}

    /* Image preview strip */
    #preview-strip{display:flex;gap:6px;flex-wrap:wrap;padding:0 12px 8px;}
    .preview-thumb{position:relative;width:56px;height:56px;border:1px solid #1e3455;overflow:hidden;flex-shrink:0;}
    .preview-thumb img{width:100%;height:100%;object-fit:cover;}
    .preview-thumb .rm{position:absolute;top:1px;right:2px;font-size:.6rem;background:#ff000088;color:#fff;border:none;cursor:pointer;padding:0 3px;line-height:1.4;}

    /* Sales panel */
    #panel-sales{display:none;}
    #panel-support{display:none;}
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
    <a href="/members/dashboard.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">dashboard</span> Dashboard</a>
    <a href="/members/orders.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">package_2</span> Orders</a>
    <a href="/members/pricelists.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">price_check</span> Price List</a>
    <a href="/members/catalog.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">menu_book</span> Catalog</a>
    <a href="/members/new-order.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">send</span> New Inquiry</a>
    <a href="/members/payment-info.php" class="sidebar-link"><span class="material-symbols-outlined" style="font-size:1rem;">account_balance</span> Payment Info</a>
    <div class="my-4" style="border-top:1px solid #1e3455;"></div>
    <a href="/members/support.php" class="sidebar-link active"><span class="material-symbols-outlined" style="font-size:1rem;">support_agent</span> Support</a>
  </aside>

  <!-- MAIN -->
  <main class="flex-1 p-6 md:p-10">
    <h1 style="font-family:'Space Grotesk',sans-serif;font-size:1.4rem;font-weight:700;margin-bottom:4px;">Support Center</h1>
    <p style="color:#8da0b3;font-size:.8rem;margin-bottom:24px;">Hi <?= htmlspecialchars($member['contact_name']) ?>, what do you need help with?</p>

    <!-- MODE TOGGLE -->
    <div style="display:flex;gap:0;max-width:400px;margin-bottom:28px;border:1px solid #1e3455;">
      <button class="mode-btn" id="btn-sales" onclick="setMode('sales')">
        <span class="material-symbols-outlined" style="font-size:.9rem;vertical-align:middle;margin-right:4px;">storefront</span>Sales / Pricing
      </button>
      <button class="mode-btn" id="btn-support" onclick="setMode('support')">
        <span class="material-symbols-outlined" style="font-size:.9rem;vertical-align:middle;margin-right:4px;">headset_mic</span>Technical Support
      </button>
    </div>

    <!-- SALES PANEL -->
    <div id="panel-sales">
      <div style="max-width:520px;background:#0c1c30;border:1px solid #1e3455;padding:28px;">
        <p style="font-family:'JetBrains Mono',monospace;font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;color:#ffd165;margin-bottom:12px;">CN & TW Sales Team</p>
        <p style="font-size:.85rem;color:#8da0b3;line-height:1.6;margin-bottom:20px;">For pricing, bulk orders, custom specs, and private label — reach our sales team directly. We'll respond within one business day.</p>
        <div class="space-y-3">
          <a href="mailto:Sales@nexautogear.com?subject=B2B Inquiry — <?= urlencode($member['company']) ?>" style="display:flex;align-items:center;justify-content:center;gap:8px;background:#ffd165;color:#081425;font-family:'JetBrains Mono',monospace;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;padding:12px;text-decoration:none;">
            <span class="material-symbols-outlined" style="font-size:1rem;">mail</span>
            Sales@nexautogear.com
          </a>
          <a href="/members/new-order.php" style="display:flex;align-items:center;justify-content:center;gap:8px;border:1px solid #1e3455;color:#8da0b3;font-family:'JetBrains Mono',monospace;font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;padding:11px;text-decoration:none;">
            <span class="material-symbols-outlined" style="font-size:1rem;">description</span>
            Submit Structured Inquiry Form
          </a>
        </div>
        <p style="font-size:.72rem;color:#4e6a85;margin-top:16px;">Mon–Sat 9AM–10PM GMT+8 · Chinese &amp; English</p>
      </div>
    </div>

    <!-- SUPPORT PANEL -->
    <div id="panel-support">
      <div style="display:grid;grid-template-columns:220px 1fr;gap:20px;max-width:900px;">

        <!-- Left: Vehicle selector -->
        <div style="display:flex;flex-direction:column;gap:16px;">
          <div style="background:#0c1c30;border:1px solid #1e3455;padding:18px;">
            <p style="font-family:'JetBrains Mono',monospace;font-size:.68rem;text-transform:uppercase;letter-spacing:.08em;color:#ffd165;margin-bottom:12px;">Vehicle (Optional)</p>
            <div style="display:flex;flex-direction:column;gap:8px;">
              <select id="sel-make" onchange="updateModels()"><option value="">— Make —</option></select>
              <select id="sel-model" style="display:none;"><option value="">— Model —</option></select>
              <select id="sel-year">
                <option value="">— Year —</option>
                <?php for($y=2026;$y>=2005;$y--) echo "<option>$y</option>"; ?>
              </select>
              <select id="sel-type">
                <option value="">— Type —</option>
                <option>Sedan</option><option>SUV / Crossover</option>
                <option>Pickup Truck</option><option>Sports Car</option>
                <option>Van / Minivan</option><option>EV</option><option>Other</option>
              </select>
              <select id="sel-topic">
                <option value="">— Topic —</option>
                <option>TPMS Sensor Programming</option>
                <option>TPMS Warning Light</option>
                <option>Wheel Fitment / Specs</option>
                <option>Order Status</option>
                <option>Return / Warranty</option>
                <option>Shipping Inquiry</option>
                <option>Other</option>
              </select>
              <button onclick="injectVehicleContext()" style="background:#1e3455;color:#ffd165;border:1px solid #ffd16540;font-family:'JetBrains Mono',monospace;font-size:.68rem;text-transform:uppercase;letter-spacing:.08em;padding:8px;cursor:pointer;width:100%;">
                Apply →
              </button>
            </div>
          </div>

          <div style="background:#0c1c30;border:1px solid #1e3455;padding:18px;">
            <p style="font-family:'JetBrains Mono',monospace;font-size:.68rem;text-transform:uppercase;letter-spacing:.08em;color:#8da0b3;margin-bottom:10px;">Email Instead</p>
            <a href="mailto:Support@nexautogear.com?subject=Support — <?= urlencode($member['company']) ?>" style="font-size:.78rem;color:#8da0b3;display:flex;align-items:center;gap:6px;text-decoration:none;">
              <span class="material-symbols-outlined" style="font-size:.95rem;color:#ffd165;">mail</span>
              Support@nexautogear.com
            </a>
            <p style="font-size:.7rem;color:#4e6a85;margin-top:10px;line-height:1.5;">Can't resolve here? Leave your preferred contact (email / phone / Telegram) and we'll follow up personally.</p>
          </div>
        </div>

        <!-- Right: Chat -->
        <div style="background:#0c1c30;border:1px solid #1e3455;display:flex;flex-direction:column;min-height:500px;">
          <!-- Header -->
          <div style="padding:12px 16px;border-bottom:1px solid #1e3455;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="width:7px;height:7px;background:#22c55e;border-radius:50%;"></div>
              <span style="font-family:'JetBrains Mono',monospace;font-size:.72rem;color:#e8edf2;text-transform:uppercase;letter-spacing:.06em;">NEX Support</span>
            </div>
            <span style="font-family:'JetBrains Mono',monospace;font-size:.62rem;color:#8da0b3;">Online Now</span>
          </div>

          <!-- Messages -->
          <div id="chat-messages" style="flex:1;">
            <div class="msg-agent">
              <div class="agent-label">NEX Support</div>
              Hello <?= htmlspecialchars($member['contact_name']) ?> 👋 How can I help today? You can also upload a photo of the issue using the 📎 button below.
            </div>
          </div>

          <!-- Image preview strip -->
          <div id="preview-strip"></div>

          <!-- Input bar -->
          <div style="padding:10px;border-top:1px solid #1e3455;display:flex;gap:6px;align-items:flex-end;">
            <input type="file" id="file-input" accept="image/*" multiple onchange="handleFiles(this.files)"/>
            <button id="upload-btn" onclick="document.getElementById('file-input').click()" title="Attach photo">
              <span class="material-symbols-outlined" style="font-size:1.1rem;line-height:2.4;">attach_file</span>
            </button>
            <textarea id="chat-input" rows="2" placeholder="Type your question… (Enter to send, Shift+Enter for new line)" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMsg();}"></textarea>
            <button onclick="sendMsg()" style="background:#ffd165;color:#081425;border:none;padding:0 14px;cursor:pointer;flex-shrink:0;height:52px;" title="Send">
              <span class="material-symbols-outlined">send</span>
            </button>
          </div>
        </div>

      </div>
    </div>

  </main>
</div>

<script>
// ── Mode toggle ──
function setMode(mode) {
  document.getElementById('panel-sales').style.display = mode === 'sales' ? 'block' : 'none';
  document.getElementById('panel-support').style.display = mode === 'support' ? 'block' : 'none';
  document.getElementById('btn-sales').classList.toggle('active', mode === 'sales');
  document.getElementById('btn-support').classList.toggle('active', mode === 'support');
}
// Default to support
setMode('support');

// ── Vehicle makes/models from vehicles.json ──
var VEHICLES = {};
fetch('/data/vehicles.json')
  .then(function(r){ return r.json(); })
  .then(function(data) {
    VEHICLES = data;
    var sel = document.getElementById('sel-make');
    Object.keys(data).sort().forEach(function(make) {
      var o = document.createElement('option');
      o.value = make; o.textContent = make;
      sel.appendChild(o);
    });
  });

function updateModels() {
  var make = document.getElementById('sel-make').value;
  var mSel = document.getElementById('sel-model');
  mSel.innerHTML = '<option value="">— Model —</option>';
  if (make && VEHICLES[make]) {
    VEHICLES[make].forEach(function(model) {
      var o = document.createElement('option');
      o.value = model; o.textContent = model;
      mSel.appendChild(o);
    });
    mSel.style.display = 'block';
  } else {
    mSel.style.display = 'none';
  }
}

// ── Vehicle context ──
var vehicleCtx = null;
function injectVehicleContext() {
  var make  = document.getElementById('sel-make').value;
  var model = document.getElementById('sel-model').value;
  var year  = document.getElementById('sel-year').value;
  var type  = document.getElementById('sel-type').value;
  var topic = document.getElementById('sel-topic').value;
  if (!make && !year && !topic) return;
  vehicleCtx = [year, make, model, type].filter(Boolean).join(' ') + (topic ? ' · ' + topic : '');
  appendMsg('agent', 'Got it — I\'ll keep your vehicle info in mind: ' + vehicleCtx + '. What\'s the issue?');
}

// ── Pending images ──
var pendingImages = []; // [{dataUrl, file}]

function handleFiles(files) {
  Array.from(files).forEach(function(file) {
    if (!file.type.startsWith('image/')) return;
    var reader = new FileReader();
    reader.onload = function(e) {
      var dataUrl = e.target.result;
      pendingImages.push({ dataUrl: dataUrl, file: file });
      addThumb(dataUrl, pendingImages.length - 1);
    };
    reader.readAsDataURL(file);
  });
  document.getElementById('file-input').value = '';
}

function addThumb(dataUrl, idx) {
  var strip = document.getElementById('preview-strip');
  var wrap = document.createElement('div');
  wrap.className = 'preview-thumb';
  wrap.id = 'thumb-' + idx;
  wrap.innerHTML = '<img src="' + dataUrl + '"/><button class="rm" onclick="removeThumb(' + idx + ')">✕</button>';
  strip.appendChild(wrap);
}

function removeThumb(idx) {
  pendingImages[idx] = null;
  var el = document.getElementById('thumb-' + idx);
  if (el) el.remove();
}

// ── Chat ──
var history = [];
var ENDPOINT = 'https://new2-chatbotservice.pkxdtf.easypanel.host/chat';

function sendMsg() {
  var input = document.getElementById('chat-input');
  var text = input.value.trim();
  var imgs = pendingImages.filter(Boolean);

  if (!text && imgs.length === 0) return;
  input.value = '';

  // Show user message
  if (text) appendMsg('user', text);

  // Show uploaded images in chat
  imgs.forEach(function(img) {
    var div = document.createElement('div');
    div.className = 'msg-image';
    div.innerHTML = '<img src="' + img.dataUrl + '" alt="Uploaded photo"/>';
    document.getElementById('chat-messages').appendChild(div);
  });
  document.getElementById('preview-strip').innerHTML = '';
  pendingImages = [];

  // Build message for API
  var fullMsg = '';
  if (vehicleCtx) fullMsg += '[Vehicle: ' + vehicleCtx + '] ';
  if (text) fullMsg += text;
  if (imgs.length > 0) fullMsg += (text ? ' ' : '') + '[Customer attached ' + imgs.length + ' photo(s) for reference]';

  history.push({ role: 'user', content: fullMsg });
  var tid = showTyping();

  fetch(ENDPOINT, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ message: fullMsg, site: 'nexautogear', history: history })
  })
  .then(function(r){ return r.json(); })
  .then(function(data) {
    removeTyping(tid);
    var reply = data.reply || data.message || 'Sorry, I couldn\'t process that. Please try again.';
    appendMsg('agent', reply);
    history.push({ role: 'assistant', content: reply });
    // Trigger tawk.to handoff if AI can't resolve
    var low = reply.toLowerCase();
    var cantResolve = low.includes('unable') || low.includes('not sure') ||
                      low.includes('escalate') || low.includes('follow up') ||
                      low.includes('specialist') || low.includes('connect you');
    if (cantResolve) {
      setTimeout(function(){ showHandoffBtn(); }, 600);
    }
  })
  .catch(function() {
    removeTyping(tid);
    appendMsg('agent', 'Connection issue. Please email Support@nexautogear.com or try again shortly.');
  });
}

function appendMsg(role, text) {
  var box = document.getElementById('chat-messages');
  var div = document.createElement('div');
  div.className = role === 'user' ? 'msg-user' : 'msg-agent';
  if (role === 'agent') {
    var lbl = document.createElement('div'); lbl.className = 'agent-label'; lbl.textContent = 'NEX Support';
    div.appendChild(lbl);
  }
  var p = document.createElement('p'); p.style.whiteSpace = 'pre-wrap'; p.textContent = text;
  div.appendChild(p);
  box.appendChild(div);
  box.scrollTop = box.scrollHeight;
}

function showTyping() {
  var box = document.getElementById('chat-messages');
  var div = document.createElement('div');
  div.className = 'msg-agent';
  var id = 'tp-' + Date.now();
  div.id = id;
  div.innerHTML = '<div class="agent-label">NEX Support</div><div style="display:flex;gap:4px;padding:4px 0;"><div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div></div>';
  box.appendChild(div);
  box.scrollTop = box.scrollHeight;
  return id;
}
function removeTyping(id) { var el=document.getElementById(id); if(el) el.remove(); }

// ── tawk.to 升級按鈕 ──
function showHandoffBtn() {
  var box = document.getElementById('chat-messages');
  if (document.getElementById('tawk-handoff-btn')) return; // 不重複
  var wrap = document.createElement('div');
  wrap.style.cssText = 'padding:4px 0;';
  var btn = document.createElement('button');
  btn.id = 'tawk-handoff-btn';
  btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:15px;vertical-align:middle;margin-right:6px;">support_agent</span>Connect to Live Specialist';
  btn.style.cssText = 'display:block;width:100%;padding:10px 16px;margin-top:4px;background:#ffd165;color:#081425;border:none;border-radius:4px;font-family:"JetBrains Mono",monospace;font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;cursor:pointer;';
  btn.onclick = function() {
    btn.textContent = 'Connecting…';
    btn.disabled = true;
    tawkHandoff();
  };
  wrap.appendChild(btn);
  box.appendChild(wrap);
  box.scrollTop = box.scrollHeight;
}

function tawkHandoff() {
  // 整理 AI 對話摘要
  var recent = history.slice(-6);
  var transcript = recent.map(function(m){
    return '[' + (m.role === 'user' ? 'Customer' : 'AI') + ']: ' + m.content;
  }).join('\n');

  var vehicle = document.getElementById('veh-ctx') ? document.getElementById('veh-ctx').textContent : '';
  var summary = 'NEXAutogear Support Handoff\nVehicle: ' + (vehicleCtx || 'Not specified') + '\n---\n' + transcript;

  // 存摘要供 tawk.to 後台查閱
  try { sessionStorage.setItem('tawk_ai_transcript', summary); } catch(e){}

  // 設 tawk visitor 屬性
  if (window.Tawk_API) {
    if (window.Tawk_API.setAttributes) {
      window.Tawk_API.setAttributes({
        'name':     '<?php echo htmlspecialchars($member['company_name'] ?? 'Member'); ?>',
        'email':    '<?php echo htmlspecialchars($member['email'] ?? ''); ?>',
        'ai-topic': currentMode === 'tech' ? 'TPMS Technical' : 'Sales',
        'vehicle':  vehicleCtx || '',
        'site':     'NEXAutogear'
      }, function(err){});
    }
    window.Tawk_API.maximize();
  } else {
    // tawk.to 未載入時的後備：開 email
    window.location.href = 'mailto:Support@nexautogear.com?subject=TPMS Support - ' + encodeURIComponent(vehicleCtx || 'Vehicle TBD') + '&body=' + encodeURIComponent(summary);
  }
}
</script>

<!-- ── tawk.to embed — 填入你的 Property ID ── -->
<!--
  到 tawk.to Dashboard → Administration → Property Settings
  複製 "Direct Chat Link" 裡的 embed code，貼在這裡（替換下方的 TAWK_PROPERTY_ID 和 TAWK_WIDGET_ID）

<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
  var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
  s1.async=true;
  s1.src='https://embed.tawk.to/TAWK_PROPERTY_ID/TAWK_WIDGET_ID';
  s1.charset='UTF-8';
  s1.setAttribute('crossorigin','*');
  s0.parentNode.insertBefore(s1,s0);
})();
</script>
-->
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

