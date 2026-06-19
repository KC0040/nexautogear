/**
 * tawk.to 通用整合模組 — NEXAutogear / AegisRim / TXRobo 共用
 *
 * 使用方式：
 *   1. 在 HTML </body> 前貼入 tawk.to embed code（見下方說明）
 *   2. 引入此 JS 檔
 *   3. 呼叫 TawkWidget.escalate(history, context) 觸發轉接
 *
 * tawk.to Embed Code 放置位置：
 *   每個頁面的 </body> 前，替換 TAWK_PROPERTY_ID 和 TAWK_WIDGET_ID
 *   取得方式：tawk.to Dashboard → Administration → Property Settings → Chat Widget
 */

window.TawkWidget = (function () {

  var _ready = false;
  var _queue = [];

  // 等 Tawk_API 完全初始化
  function _onReady(fn) {
    if (_ready) { fn(); return; }
    _queue.push(fn);
    if (window.Tawk_API && !window.Tawk_API._hooked) {
      window.Tawk_API._hooked = true;
      var orig = window.Tawk_API.onLoad || function(){};
      window.Tawk_API.onLoad = function () {
        orig();
        _ready = true;
        _queue.forEach(function(f){ f(); });
        _queue = [];
      };
    } else {
      var check = setInterval(function () {
        if (window.Tawk_API && window.Tawk_API.maximize) {
          clearInterval(check);
          _ready = true;
          _queue.forEach(function(f){ f(); });
          _queue = [];
        }
      }, 400);
    }
  }

  /**
   * escalate(history, context)
   *
   * @param {Array}  history  [{role:'user'|'assistant', content:'...'}]
   * @param {Object} context  { site, topic, vehicle, name, email }
   */
  function escalate(history, context) {
    context = context || {};
    var site    = context.site    || document.title || 'Website';
    var topic   = context.topic   || 'General';
    var vehicle = context.vehicle || '';
    var name    = context.name    || 'Visitor';
    var email   = context.email   || '';

    // 整理最近 6 則對話摘要
    var recent = (history || []).slice(-6);
    var transcript = recent.map(function (m) {
      return '[' + (m.role === 'user' ? 'Customer' : 'AI') + ']: ' +
             m.content.substring(0, 200);
    }).join('\n');

    var summary =
      '=== AI Chat Handoff ===\n' +
      'Site: ' + site + '\n' +
      'Topic: ' + topic + (vehicle ? ' | Vehicle: ' + vehicle : '') + '\n' +
      '---\n' + transcript + '\n' +
      '=== Please continue from here ===';

    // 存到 sessionStorage 讓客服端查看完整紀錄
    try { sessionStorage.setItem('tawk_ai_transcript', summary); } catch (e) {}

    _onReady(function () {
      // 設定 visitor 屬性（在 tawk.to 後台的 Visitor Info 欄位顯示）
      if (window.Tawk_API.setAttributes) {
        window.Tawk_API.setAttributes({
          name:         name,
          email:        email,
          'ai-site':    site,
          'ai-topic':   topic,
          'ai-vehicle': vehicle,
          'handoff':    'true'
        }, function (err) {});
      }

      // 標記 tag 方便 tawk.to 後台篩選
      if (window.Tawk_API.addTags) {
        var tags = ['AI-Handoff', site];
        if (topic) tags.push(topic);
        window.Tawk_API.addTags(tags, function (err) {});
      }

      // 開啟 tawk.to 聊天視窗
      window.Tawk_API.maximize();
    });
  }

  /**
   * injectButton(containerEl, history, context)
   * 在容器底部插入「Connect to Specialist」按鈕
   */
  function injectButton(containerEl, history, context) {
    if (!containerEl) return;
    if (containerEl.querySelector('.tawk-escalate-btn')) return;

    var btn = document.createElement('button');
    btn.className = 'tawk-escalate-btn';
    btn.innerHTML =
      '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:6px">' +
      '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>' +
      'Connect to Live Specialist';

    // 樣式繼承各站設計（可在各站 CSS 覆寫 .tawk-escalate-btn）
    btn.style.cssText =
      'display:block;width:100%;padding:10px 16px;margin-top:8px;' +
      'background:var(--tawk-btn-bg,#2b6cee);color:var(--tawk-btn-color,#fff);' +
      'border:none;border-radius:4px;cursor:pointer;' +
      'font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;' +
      'transition:opacity .2s;';
    btn.onmouseenter = function () { btn.style.opacity = '.85'; };
    btn.onmouseleave = function () { btn.style.opacity = '1'; };
    btn.onclick = function () {
      btn.textContent = 'Connecting…';
      btn.disabled = true;
      escalate(history, context);
    };
    containerEl.appendChild(btn);
    containerEl.scrollTop = containerEl.scrollHeight;
  }

  return { escalate: escalate, injectButton: injectButton };

})();

/* =========================================================
   tawk.to EMBED CODE — 貼在每個 HTML 的 </body> 前
   =========================================================

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

   把 TAWK_PROPERTY_ID 和 TAWK_WIDGET_ID 換成你的真實 ID
   ========================================================= */
