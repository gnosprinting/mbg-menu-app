/* Antrian tulis offline ringan.
   Form bertanda data-offline disimpan di localStorage saat jaringan putus,
   lalu dikirim ulang otomatis ketika online. Tidak meng-cache halaman. */
(function () {
  'use strict';
  var KEY = 'mbg-offline-queue';
  var BAR = null;

  // Baca antrian dari localStorage; data rusak dianggap antrian kosong.
  function baca() {
    try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { return []; }
  }
  // Simpan antrian dan perbarui indikator di layar.
  function simpan(q) { localStorage.setItem(KEY, JSON.stringify(q)); render(); }

  // Tampilkan bilah status: jumlah entri, status online, dan tombol Kirim.
  function render() {
    var q = baca();
    if (!q.length && navigator.onLine) { if (BAR) BAR.remove(); BAR = null; return; }
    if (!BAR) {
      BAR = document.createElement('div');
      BAR.id = 'offline-bar';
      BAR.style.cssText = 'position:fixed;left:16px;bottom:16px;z-index:9999;display:flex;gap:10px;align-items:center;' +
        'background:#0B1020;color:#fff;padding:9px 12px;border-radius:12px;font:12px/1.4 Segoe UI,Arial,sans-serif;' +
        'box-shadow:0 8px 24px rgba(11,16,32,.28)';
      document.body.appendChild(BAR);
    }
    var n = q.length;
    var status = navigator.onLine ? 'menunggu dikirim' : 'offline';
    BAR.innerHTML = '<i class="fa-solid ' + (navigator.onLine ? 'fa-cloud-arrow-up' : 'fa-plug-circle-xmark') + '"></i>' +
      '<span>' + n + ' entri ' + status + '</span>';
    if (navigator.onLine && n) {
      var b = document.createElement('button');
      b.textContent = 'Kirim';
      b.style.cssText = 'background:#7B4DFF;color:#fff;border:0;border-radius:8px;padding:4px 10px;cursor:pointer;font:inherit';
      b.onclick = flush;
      BAR.appendChild(b);
    }
  }

  // Buat ID unik agar server dapat menolak kiriman ganda (idempotensi).
  function uuid() {
    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
      return v.toString(16);
    });
  }

  // Ubah isi form menjadi objek, tambahkan client_uuid, lalu simpan ke antrian.
  function enqueue(form) {
    var q = baca();
    var data = {};
    new FormData(form).forEach(function (v, k) { if (k !== '_token') data[k] = v; });
    data.client_uuid = uuid();
    q.push({ url: form.getAttribute('action') || location.pathname, data: data, ts: Date.now() });
    simpan(q);
  }

  var flushing = false;

  // Kirim antrian satu per satu; hentikan saat offline atau server menolak.
  function flush() {
    if (flushing) return;
    var q = baca();
    if (!q.length || !navigator.onLine) { render(); return; }
    flushing = true;
    var item = q[0];
    var body = new URLSearchParams(item.data);
    fetch(item.url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
        'X-CSRF-TOKEN': (document.querySelector('meta[name=csrf-token]') || {}).content || '',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json, text/html'
      },
      body: body.toString()
    }).then(function (r) {
      if (r.status === 419) { throw new Error('Sesi kedaluwarsa — muat ulang halaman lalu kirim lagi.'); }
      if (r.status >= 400 && r.status !== 422) { throw new Error('HTTP ' + r.status); }
      q.shift(); simpan(q);
      flushing = false;
      if (q.length) { setTimeout(flush, 350); }
    }).catch(function (e) {
      flushing = false;
      if (BAR) {
        var s = document.createElement('span');
        s.textContent = 'Gagal: ' + e.message;
        s.style.cssText = 'color:#FCA5A5';
        BAR.appendChild(s);
      }
    });
  }

  document.addEventListener('submit', function (ev) {
    var form = ev.target;
    if (!form.matches('form[data-offline]')) return;
    if (navigator.onLine) return; // biarkan submit normal
    ev.preventDefault();
    enqueue(form);
    form.reset();
    var note = form.querySelector('[data-offline-note]');
    if (note) { note.textContent = 'Tersimpan di perangkat. Akan dikirim saat online.'; }
  }, true);

  window.addEventListener('online', function () { render(); flush(); });
  window.addEventListener('offline', render);
  document.addEventListener('DOMContentLoaded', function () { render(); if (navigator.onLine) flush(); });

  window.MbgOffline = { queue: baca, flush: flush, enqueue: enqueue, render: render };
})();
