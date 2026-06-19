/**
 * tawk.to 升級整合模組
 * NEXAutogear / AegisRim / TXRobo 三站共用
 *
 * 使用方式：
 *   1. 在 HTML 加入 tawk.to embed code（填入你的 Property ID）
 *   2. 引入此檔案
 *   3. 呼叫 TawkEscalate.handoff(conversationHistory, context)
 */

const TawkEscalate = (() => {

  // ── 等待 Tawk_API 就緒 ──
  function whenReady(fn) {
    if (window.Tawk_API && window.Tawk_API.onLoad) {
      window.Tawk_API.onLoad = fn;
    } else {
      const check = setInterval(() => {
        if (window.Tawk_API) { clearInterval(check); fn(); }
      }, 300);
    }
  }

  /**
   * handoff(messages, context)
   *
   * @param {Array}  messages  - [{role:'user'|'assistant', content:'...'}]
   * @param {Object} context   - { site:'NEX'|'AEGIS'|'TXROBO', topic:'TPMS'|'Sales'|..., vehicle?:'...' }
   */
  function handoff(messages, context = {}) {
    const site    = context.site    || 'NEXAutogear';
    const topic   = context.topic   || 'General';
    const vehicle = context.vehicle || '';

    // 整理對話摘要（最近 6 則）
    const recent = messages.slice(-6);
    const transcript = recent.map(m =>
      `[${m.role === 'user' ? 'Customer' : 'AI'}]: ${m.content}`
    ).join('\n');

    const summary =
      `📋 AI Chat Handoff — ${site}\n` +
      `Topic: ${topic}${vehicle ? ' | Vehicle: ' + vehicle : ''}\n` +
      `──────────────────\n` +
      transcript +
      `\n──────────────────\n` +
      `Please continue from where the AI left off.`;

    whenReady(() => {
      // 設定 visitor 自訂屬性讓 owner 在 tawk.to 後台看到摘要
      if (window.Tawk_API.setAttributes) {
        window.Tawk_API.setAttributes({
          'ai-handoff': 'true',
          'topic':      topic,
          'vehicle':    vehicle,
          'site':       site,
        }, (err) => { if (err) console.warn('tawk setAttributes:', err); });
      }

      // 在對話中插入摘要訊息（客服端可見）
      if (window.Tawk_API.addTags) {
        window.Tawk_API.addTags([site, topic, 'AI-Handoff'], (err) => {});
      }

      // 展開 tawk.to 聊天視窗
      window.Tawk_API.maximize();

      // 把摘要存到 sessionStorage，讓 agent 登入後也能查
      try {
        sessionStorage.setItem('tawk_ai_transcript', summary);
      } catch(e) {}
    });
  }

  /**
   * showHandoffButton(container, messages, context)
   * 在指定容器插入「Connect to Specialist」按鈕
   */
  function showHandoffButton(container, messages, context) {
    if (!container) return;
    const existing = container.querySelector('.tawk-handoff-btn');
    if (existing) return; // 不重複插入

    const btn = document.createElement('button');
    btn.className = 'tawk-handoff-btn';
    btn.innerHTML = `
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:6px;">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
      </svg>
      Connect to Specialist
    `;
    btn.style.cssText = `
      display:block;width:100%;margin-top:10px;padding:10px 16px;
      background:#2b6cee;color:#fff;border:none;border-radius:4px;
      font-family:'Space Grotesk',sans-serif;font-size:0.75rem;font-weight:700;
      letter-spacing:0.08em;text-transform:uppercase;cursor:pointer;
      transition:opacity .2s;
    `;
    btn.onmouseenter = () => btn.style.opacity = '0.85';
    btn.onmouseleave = () => btn.style.opacity = '1';
    btn.onclick = () => {
      handoff(messages, context);
      btn.textContent = 'Connecting...';
      btn.disabled = true;
    };
    container.appendChild(btn);
  }

  return { handoff, showHandoffButton, whenReady };
})();
