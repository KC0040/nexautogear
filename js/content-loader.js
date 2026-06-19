/**
 * NEXAutogear Content Loader
 * Fills data-nex-* attributes from JSON data files.
 * Works in plain HTML. Same JSON files can be imported in Next.js.
 *
 * Usage: <span data-nex="company.name"></span>
 *        <meta data-nex-attr="content" data-nex="seo.defaultDescription">
 */

const NexLoader = (() => {
  let _site = null;
  let _products = null;

  async function loadJSON(path) {
    const r = await fetch(path);
    if (!r.ok) throw new Error(`Failed to load ${path}`);
    return r.json();
  }

  function resolve(obj, path) {
    return path.split('.').reduce((o, k) => (o != null ? o[k] : undefined), obj);
  }

  function fill(data) {
    // data-nex="path.to.value" → innerText
    document.querySelectorAll('[data-nex]').forEach(el => {
      const val = resolve(data, el.dataset.nex);
      if (val != null) el.textContent = val;
    });

    // data-nex-attr="attrName" data-nex="path" → setAttribute
    document.querySelectorAll('[data-nex-attr]').forEach(el => {
      const val = resolve(data, el.dataset.nex);
      if (val != null) el.setAttribute(el.dataset.nexAttr, val);
    });

    // data-nex-href="path" → href
    document.querySelectorAll('[data-nex-href]').forEach(el => {
      const val = resolve(data, el.dataset.nexHref);
      if (val != null) el.href = val;
    });
  }

  async function init(options = {}) {
    const base = options.base || '/data';
    try {
      _site = await loadJSON(`${base}/site.json`);
      _products = await loadJSON(`${base}/products.json`);
      const merged = { ..._site, products: _products };
      fill(merged);
      if (options.onLoad) options.onLoad(merged);
    } catch (e) {
      console.warn('[NexLoader] Could not load content data:', e.message);
    }
  }

  return { init, get site() { return _site; }, get products() { return _products; } };
})();

// Auto-init when DOM is ready
document.addEventListener('DOMContentLoaded', () => NexLoader.init());
