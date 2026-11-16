/* Dropdown dengan pencarian — dipakai pada form Siklus Menu */
(function () {
  var esc = function (s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  };

  function build(sel) {
    if (sel.dataset.ssReady) return;
    sel.dataset.ssReady = '1';

    var wrap = document.createElement('div');
    wrap.className = 'ss';
    sel.parentNode.insertBefore(wrap, sel);
    sel.classList.add('ss-native');
    wrap.appendChild(sel);

    var input = document.createElement('input');
    input.type = 'text';
    input.className = 'ss-input';
    input.setAttribute('autocomplete', 'off');
    input.setAttribute('role', 'combobox');
    input.placeholder = sel.dataset.placeholder || 'Cari…';

    var list = document.createElement('div');
    list.className = 'ss-list';
    wrap.appendChild(input);
    wrap.appendChild(list);

    var opts = Array.prototype.map.call(sel.options, function (o) {
      return { v: o.value, t: o.textContent.trim() };
    });
    var shown = [];
    var active = -1;

    function label(v) {
      for (var i = 0; i < opts.length; i++) if (opts[i].v === v) return opts[i].t;
      return '';
    }

    function highlight() {
      var el = list.querySelectorAll('.ss-opt');
      for (var i = 0; i < el.length; i++) el[i].classList.toggle('on', i === active);
      var on = list.querySelector('.ss-opt.on');
      if (on) on.scrollIntoView({ block: 'nearest' });
    }

    function paint(term) {
      var q = String(term || '').toLowerCase();
      var starts = [], contains = [];
      for (var j = 0; j < opts.length; j++) {
        var t = opts[j].t.toLowerCase(), pos = t.indexOf(q);
        if (pos === 0) starts.push(opts[j]);
        else if (pos > 0) contains.push(opts[j]);
      }
      shown = starts.concat(contains).slice(0, 60);
      active = -1;
      for (var i = 0; i < shown.length; i++) if (shown[i].v === sel.value) active = i;
      list.innerHTML = shown.length
        ? shown.map(function (o, i) {
            return '<button type="button" class="ss-opt' + (i === active ? ' on' : '') + '" data-i="' + i + '">' +
              '<i class="fa-solid fa-check"></i>' + esc(o.t) + '</button>';
          }).join('')
        : '<div class="ss-empty">Tidak ada yang cocok</div>';
      list.classList.add('open');
      wrap.classList.add('open');
    }

    function choose(i) {
      var o = shown[i];
      if (!o) return;
      sel.value = o.v;
      input.value = o.t;
      list.classList.remove('open');
      wrap.classList.remove('open');
      sel.dispatchEvent(new Event('change', { bubbles: true }));
    }

    input.value = label(sel.value);

    input.addEventListener('focus', function () {
      paint('');
      input.select();
    });

    input.addEventListener('input', function () { paint(input.value); });

    input.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (!list.classList.contains('open')) paint(input.value);
        active = Math.min(active + 1, shown.length - 1);
        highlight();
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        active = Math.max(active - 1, 0);
        highlight();
      } else if (e.key === 'Enter') {
        if (list.classList.contains('open')) { e.preventDefault(); choose(active); }
      } else if (e.key === 'Escape') {
        list.classList.remove('open');
        wrap.classList.remove('open');
        input.value = label(sel.value);
      }
    });

    input.addEventListener('blur', function () {
      setTimeout(function () {
        list.classList.remove('open');
        wrap.classList.remove('open');
        input.value = label(sel.value);
      }, 120);
    });

    list.addEventListener('mousedown', function (e) {
      var btn = e.target.closest('.ss-opt');
      if (!btn) return;
      e.preventDefault();
      choose(parseInt(btn.dataset.i, 10));
    });
  }

  function init() {
    document.querySelectorAll('select[data-search]').forEach(build);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
