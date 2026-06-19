/**
 * NEX JSON 驅動多語言引擎
 * 用法：頁面 </body> 前加 <script src="/js/i18n.js" defer></script>
 * 新增語言 = 在 /lang/ 放一個新 JSON + 在 LANGS 加一行，不用改 HTML
 */
(function () {
  var LANGS = [
    { code: "en", label: "EN" },
    { code: "es", label: "ES" },
    { code: "de", label: "DE" },
    { code: "fr", label: "FR" },
    { code: "ja", label: "JP" },
    { code: "ar", label: "AR", rtl: true },
  ];
  var DEFAULT = "en";

  function currentLang() {
    var q = new URLSearchParams(location.search).get("lang");
    if (q && LANGS.some(function (l) { return l.code === q; })) {
      localStorage.setItem("nex_lang", q);
      return q;
    }
    return localStorage.getItem("nex_lang") || DEFAULT;
  }

  function apply(lang) {
    document.documentElement.lang = lang;
    var isRtl = LANGS.some(function (l) { return l.code === lang && l.rtl; });
    document.documentElement.dir = isRtl ? "rtl" : "ltr";
    if (lang === DEFAULT) return; // 英文 = HTML 原文，不用 fetch
    fetch("/lang/" + lang + ".json")
      .then(function (r) { return r.json(); })
      .then(function (dict) {
        document.querySelectorAll("[data-i18n]").forEach(function (el) {
          var key = el.getAttribute("data-i18n");
          if (dict[key]) el.innerHTML = dict[key];
        });
      })
      .catch(function () { /* JSON 缺失時保持英文 */ });
  }

  // 語言切換器 — 自動插入頁面右下（聊天按鈕上方）
  function buildSwitcher(lang) {
    var css = "#nex-lang{position:fixed;bottom:24px;left:24px;z-index:9998;display:flex;gap:2px;border:1px solid #2c2c30;background:#16161a;border-radius:8px;overflow:hidden}" +
      "#nex-lang button{background:none;border:none;color:#8a8a90;font:700 11px/1 Inter,sans-serif;letter-spacing:.08em;padding:9px 12px;cursor:pointer}" +
      "#nex-lang button.on{background:#ffd165;color:#1a1a1a}";
    var style = document.createElement("style");
    style.textContent = css;
    document.head.appendChild(style);

    var bar = document.createElement("div");
    bar.id = "nex-lang";
    LANGS.forEach(function (l) {
      var b = document.createElement("button");
      b.textContent = l.label;
      if (l.code === lang) b.className = "on";
      b.addEventListener("click", function () {
        localStorage.setItem("nex_lang", l.code);
        location.search = "?lang=" + l.code;
      });
      bar.appendChild(b);
    });
    document.body.appendChild(bar);
  }

  var lang = currentLang();
  apply(lang);
  buildSwitcher(lang);
})();
