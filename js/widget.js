/**
 * NEX AI 聊天 Widget + tawk.to 升級整合
 * 使用方式（貼在任何頁面 </body> 前）：
 *
 *   <script>window.NEX_CHAT = {
 *     endpoint:    "https://new2-chatbotservice.pkxdtf.easypanel.host/chat",
 *     site:        "nexautogear",   // or "aegisrim" / "txrobo"
 *     botName:     "NEX",
 *     accentColor: "#ffd165"
 *   };</script>
 *   <script src="/js/widget.js" defer></script>
 *   <!-- tawk.to embed code 接在後面 -->
 */
(function () {
  var cfg      = window.NEX_CHAT || {};
  var ENDPOINT = cfg.endpoint    || '';
  var ACCENT   = cfg.accentColor || '#ffd165';
  var BOT      = cfg.botName     || 'NEX';
  var SITE     = cfg.site        || 'nexautogear';
  var history  = [];
  var escalated = false;

  // ── 樣式 ──
  var css =
    '#nexw-btn{position:fixed;bottom:24px;right:24px;z-index:9000;width:56px;height:56px;border:none;cursor:pointer;background:' + ACCENT + ';color:#1a1a1a;display:flex;align-items:center;justify-content:center;border-radius:50%;box-shadow:0 4px 24px rgba(0,0,0,.3);transition:transform .2s}' +
    '#nexw-btn:hover{transform:scale(1.08)}' +
    '#nexw-panel{position:fixed;bottom:96px;right:24px;z-index:9000;width:360px;max-width:calc(100vw - 32px);height:500px;background:#16161a;border:1px solid #2c2c30;border-radius:12px;display:none;flex-direction:column;overflow:hidden;font-family:Inter,system-ui,sans-serif;box-shadow:0 12px 48px rgba(0,0,0,.5)}' +
    '#nexw-panel.open{display:flex}' +
    '#nexw-head{background:#1e1e23;border-bottom:1px solid #2c2c30;padding:14px 18px;display:flex;align-items:center;justify-content:space-between}' +
    '#nexw-head .hinfo{display:flex;align-items:center;gap:10px}' +
    '#nexw-head .dot{width:8px;height:8px;border-radius:50%;background:#2ed573;flex-shrink:0}' +
    '#nexw-head b{color:#f5f0e6;font-size:13px;letter-spacing:.08em;text-transform:uppercase;display:block}' +
    '#nexw-head span{color:#8a8a90;font-size:11px}' +
    '#nexw-close{background:none;border:none;color:#8a8a90;cursor:pointer;font-size:18px;line-height:1;padding:0 4px}' +
    '#nexw-msgs{flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:10px}' +
    '.nexw-m{max-width:85%;padding:9px 13px;font-size:13px;line-height:1.55;border-radius:10px;white-space:pre-wrap}' +
    '.nexw-m.user{align-self:flex-end;background:' + ACCENT + ';color:#1a1a1a}' +
    '.nexw-m.bot{align-self:flex-start;background:#222227;color:#d7d2c7;border:1px solid #2c2c30}' +
    '#nexw-escalate-btn{display:block;width:calc(100% - 28px);margin:0 14px 10px;padding:9px 16px;background:#2b6cee;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:.68rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;transition:opacity .2s}' +
    '#nexw-escalate-btn:hover{opacity:.85}' +
    '#nexw-escalate-btn:disabled{opacity:.5;cursor:default}' +
    '#nexw-in{border-top:1px solid #2c2c30;padding:10px;display:flex;gap:8px}' +
    '#nexw-in input{flex:1;background:#1e1e23;border:1px solid #2c2c30;color:#f5f0e6;font-size:13px;padding:9px 12px;border-radius:8px;outline:none}' +
    '#nexw-in input:focus{border-color:' + ACCENT + '}' +
    '#nexw-in button{background:' + ACCENT + ';border:none;color:#1a1a1a;font-size:11px;font-weight:700;letter-spacing:.08em;padding:0 16px;cursor:pointer;border-radius:8px}' +
    '@media(max-width:480px){#nexw-panel{width:calc(100vw - 24px);right:12px;height:70vh;bottom:88px}}';

  var style = document.createElement('style');
  style.textContent = css;
  document.head.appendChild(style);

  // ── DOM ──
  var btn = document.createElement('button');
  btn.id = 'nexw-btn';
  btn.setAttribute('aria-label', 'Chat with ' + BOT);
  btn.innerHTML = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>';

  var panel = document.createElement('div');
  panel.id = 'nexw-panel';
  panel.innerHTML =
    '<div id="nexw-head">' +
      '<div class="hinfo"><span class="dot"></span><div><b>' + BOT + ' Assistant</b><span>AI · Live specialist available</span></div></div>' +
      '<button id="nexw-close" aria-label="Close">✕</button>' +
    '</div>' +
    '<div id="nexw-msgs"></div>' +
    '<div id="nexw-in"><input type="text" placeholder="Ask about pricing, TPMS, MOQ…" /><button>SEND</button></div>';

  document.body.appendChild(btn);
  document.body.appendChild(panel);

  var msgs  = panel.querySelector('#nexw-msgs');
  var input = panel.querySelector('input');
  var send  = panel.querySelector('#nexw-in button');

  panel.querySelector('#nexw-close').addEventListener('click', function () {
    panel.classList.remove('open');
  });

  // ── 訊息渲染 ──
  function add(role, text) {
    var d = document.createElement('div');
    d.className = 'nexw-m ' + (role === 'user' ? 'user' : 'bot');
    d.textContent = text;
    msgs.appendChild(d);
    msgs.scrollTop = msgs.scrollHeight;
    return d;
  }

  // ── tawk.to 升級按鈕 ──
  function showEscalateBtn() {
    if (document.getElementById('nexw-escalate-btn')) return;
    var ebtn = document.createElement('button');
    ebtn.id = 'nexw-escalate-btn';
    ebtn.innerHTML =
      '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:5px">' +
      '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>' +
      'Connect to Live Specialist';
    panel.insertBefore(ebtn, panel.querySelector('#nexw-in'));

    ebtn.addEventListener('click', function () {
      if (escalated) return;
      escalated = true;
      ebtn.textContent = 'Connecting…';
      ebtn.disabled = true;
      tawkHandoff();
    });
  }

  // ── tawk.to 轉接 ──
  function tawkHandoff() {
    var recent = history.slice(-6);
    var transcript = recent.map(function (m) {
      return '[' + (m.role === 'user' ? 'Customer' : 'AI') + ']: ' + (m.text || m.content || '').substring(0, 200);
    }).join('\n');

    var summary =
      '=== AI Handoff: ' + SITE + ' ===\n' +
      transcript + '\n' +
      '=== Please continue ===';

    try { sessionStorage.setItem('tawk_ai_transcript', summary); } catch (e) {}

    function doTawk() {
      if (window.Tawk_API) {
        if (window.Tawk_API.setAttributes) {
          window.Tawk_API.setAttributes({
            'ai-site':    SITE,
            'ai-topic':   'Sales/Support',
            'handoff':    'true'
          }, function () {});
        }
        window.Tawk_API.maximize();
      } else {
        // 後備：email
        window.open('mailto:Sales@nexautogear.com?subject=Chat%20Inquiry%20%E2%80%94%20' + encodeURIComponent(SITE) + '&body=' + encodeURIComponent(summary));
      }
    }

    if (window.Tawk_API && window.Tawk_API.maximize) {
      doTawk();
    } else {
      var t = setInterval(function () {
        if (window.Tawk_API && window.Tawk_API.maximize) {
          clearInterval(t); doTawk();
        }
      }, 400);
      setTimeout(function () { clearInterval(t); doTawk(); }, 5000);
    }
  }

  // ── 開場白 ──
  add('bot', 'Welcome to ' + (SITE === 'aegisrim' ? 'AegisRim' : 'NEX AUTO GEAR') + '. How can I help you today?');

  // ── 開關面板 ──
  btn.addEventListener('click', function () {
    panel.classList.toggle('open');
    if (panel.classList.contains('open')) input.focus();
  });

  // ── 送出訊息 ──
  var busy = false;
  function submit() {
    var text = input.value.trim();
    if (!text || busy) return;
    input.value = '';
    add('user', text);
    history.push({ role: 'user', text: text });
    busy = true;
    var thinking = add('bot', '···');

    if (!ENDPOINT) {
      thinking.textContent = 'Please email Sales@nexautogear.com — we respond within 1 business day.';
      busy = false;
      return;
    }

    fetch(ENDPOINT, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: text, site: SITE, history: history.slice(-12) })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        var reply = data.reply || data.error || 'Sorry, please try again.';
        thinking.textContent = reply;
        history.push({ role: 'assistant', text: reply });

        // 偵測 AI 無法解決 → 顯示升級按鈕
        var low = reply.toLowerCase();
        if (
          low.includes('specialist') || low.includes('connect you') ||
          low.includes('unable') || low.includes('follow up') ||
          low.includes('escalate') || low.includes('email us') ||
          history.length > 8  // 超過 8 輪仍未解決，主動顯示
        ) {
          showEscalateBtn();
        }
      })
      .catch(function () {
        thinking.textContent = 'Connection issue — email Sales@nexautogear.com.';
        showEscalateBtn();
      })
      .finally(function () { busy = false; });
  }

  send.addEventListener('click', submit);
  input.addEventListener('keydown', function (e) { if (e.key === 'Enter') submit(); });

})();
