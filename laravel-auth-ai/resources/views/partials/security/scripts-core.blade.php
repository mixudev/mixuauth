<script>
'use strict';

// ─────────────────────────────────────────────────────────────────────────────
// CONSTANTS
// BASE sudah include /api — semua endpoint dipanggil relatif ke sini
// ─────────────────────────────────────────────────────────────────────────────
const BASE = '/dev/monitoring/api';
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const PAGE_META = {
  otp:       { title: 'OTP Verifications', sub: 'Active & historical OTP records',   cols: 9  },
  logs:      { title: 'Login Logs',         sub: 'All authentication attempts',       cols: 10 },
  devices:   { title: 'Trusted Devices',    sub: 'Registered & revoked devices',      cols: 10 },
  users:     { title: 'Users',              sub: 'User accounts & block management',  cols: 12 },
  blacklist: { title: 'IP Blacklist',       sub: 'Blocked IP addresses',             cols: 9  },
  whitelist: { title: 'IP Whitelist',       sub: 'Trusted / bypassed IP addresses',  cols: 6  },
};

const TAB_ENDPOINTS = {
  otp:       '/otps',
  logs:      '/logs',
  devices:   '/devices',
  users:     '/users',
  blacklist: '/ip-blacklist',
  whitelist: '/ip-whitelist',
};

const TABS = Object.keys(PAGE_META);

// ─────────────────────────────────────────────────────────────────────────────
// PAGINATION STATE — satu entry per tab, persisten selama sesi
// ─────────────────────────────────────────────────────────────────────────────
const state = {};
function initState(tab) {
  state[tab] = { cursor: 0, hasMore: true, loading: false, count: 0 };
}
TABS.forEach(initState);

let activeTab = 'otp';

// ─────────────────────────────────────────────────────────────────────────────
// NAVIGATION — show/hide pre-rendered pages (bukan innerHTML swap)
// Ini kenapa tombol action bisa bekerja: elemen sudah ada di DOM dari awal
// ─────────────────────────────────────────────────────────────────────────────
function navigateTo(tab) {
  // Kalau klik tab yang sama → refresh data
  if (tab === activeTab) {
    resetLoad(tab);
    return;
  }

  // Sembunyikan semua page
  TABS.forEach(t => {
    const el = document.getElementById('page-' + t);
    if (el) el.style.display = 'none';
  });

  // Tampilkan page yang dipilih
  const target = document.getElementById('page-' + tab);
  if (target) target.style.display = '';

  // Update sidebar active state
  TABS.forEach(t => {
    const nav = document.getElementById('nav-' + t);
    if (nav) nav.classList.toggle('active', t === tab);
  });

  // Update header breadcrumb
  const meta    = PAGE_META[tab] ?? {};
  const titleEl = document.getElementById('hdr-page-title');
  const subEl   = document.getElementById('hdr-page-sub');
  if (titleEl) titleEl.textContent = meta.title ?? tab;
  if (subEl)   subEl.textContent   = meta.sub   ?? '';

  activeTab = tab;

  // Load data hanya kalau belum pernah load
  if (state[tab].count === 0 && state[tab].hasMore) {
    resetLoad(tab);
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// SEARCH DEBOUNCE
// ─────────────────────────────────────────────────────────────────────────────
const debounceTimers = {};
function debounce(tab) {
  clearTimeout(debounceTimers[tab]);
  debounceTimers[tab] = setTimeout(() => resetLoad(tab), 320);
}

function resetLoad(tab) {
  initState(tab);
  const tb = document.getElementById('tb-' + tab);
  if (tb) {
    tb.innerHTML = stateRow(PAGE_META[tab]?.cols ?? 9, '<span class="spinner"></span>Loading…');
  }
  loadPage(tab);
}

// ─────────────────────────────────────────────────────────────────────────────
// INFINITE SCROLL
// ─────────────────────────────────────────────────────────────────────────────
const observers = {};

function setupObserver(tab) {
  if (observers[tab]) observers[tab].disconnect();
  const sentinel = document.getElementById('sentinel-' + tab);
  if (!sentinel) return;
  observers[tab] = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting && state[tab]?.hasMore && !state[tab]?.loading) {
      loadPage(tab);
    }
  }, { rootMargin: '200px' });
  observers[tab].observe(sentinel);
}

// ─────────────────────────────────────────────────────────────────────────────
// CORE DATA LOADER
// ─────────────────────────────────────────────────────────────────────────────
async function loadPage(tab) {
  const s = state[tab];
  if (!s || !s.hasMore || s.loading) return;
  s.loading = true;

  const params = buildParams(tab, s.cursor);
  const url    = BASE + TAB_ENDPOINTS[tab] + (params ? '?' + params : '');

  try {
    const resp = await fetch(url, {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
    });
    const json = await resp.json();
    if (!resp.ok) throw new Error(json.message || 'HTTP ' + resp.status);

    const rows = json.data ?? json;
    s.hasMore  = json.has_more  ?? false;
    s.cursor   = json.next_cursor ?? 0;
    s.count   += rows.length;
    s.loading  = false;

    renderRows(tab, rows, s.count - rows.length);
    updateCount(tab, s.count, s.hasMore);
    updateNavBadge(tab, s.count, s.hasMore);
    if (s.hasMore) setupObserver(tab);

  } catch (e) {
    s.loading = false;
    const tb  = document.getElementById('tb-' + tab);
    if (tb && tb.querySelectorAll('tr:not(.state-row)').length === 0) {
      tb.innerHTML = stateRow(PAGE_META[tab]?.cols ?? 9, '⚠ ' + esc(e.message));
    }
    toast(e.message, 'err');
  }
}

function buildParams(tab, cursor) {
  const p = new URLSearchParams();
  if (cursor) p.set('cursor', cursor);

  const filterMap = {
    otp:       { status: 'f-otp-status',  search: 'f-otp-q' },
    logs:      { status: 'f-logs-status', decision: 'f-logs-decision', search: 'f-logs-q' },
    devices:   { status: 'f-dev-status',  search: 'f-dev-q' },
    users:     { status: 'f-usr-status',  search: 'f-usr-q' },
    blacklist: { search: 'f-bl-q' },
    whitelist: { search: 'f-wl-q' },
  };

  Object.entries(filterMap[tab] ?? {}).forEach(([key, id]) => {
    const el = document.getElementById(id);
    if (el?.value) p.set(key, el.value);
  });

  return p.toString();
}

// ─────────────────────────────────────────────────────────────────────────────
// ROW RENDERING
// ─────────────────────────────────────────────────────────────────────────────
function renderRows(tab, rows, offset) {
  const tb   = document.getElementById('tb-' + tab);
  if (!tb) return;
  const cols = PAGE_META[tab]?.cols ?? 9;

  if (offset === 0) {
    tb.innerHTML = '';
  } else {
    document.getElementById('sentinel-row-' + tab)?.remove();
  }

  if (rows.length === 0 && offset === 0) {
    tb.innerHTML = stateRow(cols, 'No data found');
    return;
  }

  tb.insertAdjacentHTML('beforeend',
    rows.map((r, i) => renderRow(tab, r, offset + i + 1)).join('')
  );

  if (state[tab].hasMore) {
    tb.insertAdjacentHTML('beforeend', `
      <tr id="sentinel-row-${tab}">
        <td colspan="${cols}" style="padding:0;border:none">
          <div id="sentinel-${tab}" class="sentinel"></div>
        </td>
      </tr>`);
  }
}

function renderRow(tab, r, n) {
  switch (tab) {
    case 'otp':       return rowOtp(r, n);
    case 'logs':      return rowLog(r, n);
    case 'devices':   return rowDevice(r, n);
    case 'users':     return rowUser(r, n);
    case 'blacklist': return rowBlacklist(r, n);
    case 'whitelist': return rowWhitelist(r, n);
    default:          return '';
  }
}

function rowOtp(o, n) {
  const bc = o.status === 'verified' ? 'b-green' : o.status === 'expired' ? 'b-muted' : 'b-amber';
  return `<tr>
    <td class="dim">${n}</td>
    <td class="user-cell"><strong>${esc(o.user)}</strong></td>
    <td class="dim">${esc(o.email)}</td>
    <td><span class="otp-tok">${esc(o.otp_code)}</span></td>
    <td><span class="badge ${bc}">${o.status}</span></td>
    <td style="color:${o.attempts >= 3 ? 'var(--red)' : 'inherit'}">${o.attempts}/3</td>
    <td class="dim">${o.expires_at  || '—'}</td>
    <td class="dim">${o.verified_at || '—'}</td>
    <td class="dim">${o.created_at}</td>
  </tr>`;
}

function rowLog(l, n) {
  const sc    = l.risk_score ?? 0;
  const rc    = sc < 30 ? 'var(--green)' : sc < 60 ? 'var(--amber)' : 'var(--red)';
  const sb    = { success: 'b-green', otp_required: 'b-amber', blocked: 'b-red', failed: 'b-red' }[l.status] || 'b-muted';
  const db    = { ALLOW: 'b-green', OTP: 'b-amber', BLOCK: 'b-red' }[l.decision] || 'b-muted';
  const flags = Array.isArray(l.reason_flags) ? l.reason_flags : tryParse(l.reason_flags ?? '[]');
  return `<tr class="${l.decision === 'BLOCK' ? 'row-blocked' : ''}">
    <td class="dim">${n}</td>
    <td class="mono-sm">${l.occurred_at || '—'}</td>
    <td class="user-cell"><strong>${esc(l.user)}</strong></td>
    <td class="dim">${esc(l.email)}</td>
    <td class="mono-sm">${esc(l.ip_address || '—')}</td>
    <td><span class="badge ${sb}">${l.status}</span></td>
    <td><span class="badge ${db}">${l.decision || '—'}</span></td>
    <td>
      <div class="risk">
        <span class="risk-num" style="color:${rc}">${sc}</span>
        <div class="risk-bar"><div class="risk-fill" style="width:${Math.min(sc, 100)}%;background:${rc}"></div></div>
      </div>
    </td>
    <td><div class="flags">${flags.map(f => `<span class="flag">${esc(f)}</span>`).join('') || '—'}</div></td>
    <td><span class="fp-cell" title="${esc(l.device_fp)}">${esc(l.device_fp || '—')}</span></td>
  </tr>`;
}

function rowDevice(d, n) {
  const rev = d.is_revoked;
  return `<tr>
    <td class="dim">${n}</td>
    <td class="user-cell"><strong>${esc(d.user)}</strong></td>
    <td class="dim">${esc(d.email)}</td>
    <td>${esc(d.device_label)}</td>
    <td><span class="fp-cell" title="${esc(d.fingerprint)}">${esc(d.fingerprint)}</span></td>
    <td class="mono-sm">${esc(d.ip_address || '—')}</td>
    <td><span class="badge ${rev ? 'b-red' : 'b-green'}">${rev ? 'REVOKED' : 'TRUSTED'}</span></td>
    <td class="dim">${d.last_seen     || '—'}</td>
    <td class="dim">${d.trusted_until || '—'}</td>
    <td>
      <button class="btn btn-sm ${rev ? 'btn-green' : 'btn-red'}"
              onclick="toggleDevice(${d.id}, this)">
        ${rev ? '↺ Restore' : '⊘ Revoke'}
      </button>
    </td>
  </tr>`;
}

function rowUser(u, n) {
  const blocked = u.is_blocked;
  return `<tr class="${blocked ? 'row-blocked' : ''}">
    <td class="dim">${u.id}</td>
    <td class="user-cell"><strong>${esc(u.name)}</strong></td>
    <td class="dim">${esc(u.email)}</td>
    <td><span class="badge ${u.is_active ? 'b-green' : 'b-muted'}">${u.is_active ? 'YES' : 'NO'}</span></td>
    <td><span class="badge ${u.verified  ? 'b-cyan'  : 'b-muted'}">${u.verified  ? 'YES' : 'NO'}</span></td>
    <td class="dim">${u.last_login_at || '—'}</td>
    <td class="mono-sm">${esc(u.last_login_ip || '—')}</td>
    <td>${u.login_count}</td>
    <td>${u.device_count}</td>
    <td class="dim">${u.created_at}</td>
    <td><span class="badge ${blocked ? 'b-red' : 'b-green'}">${blocked ? 'BLOCKED' : 'OK'}</span></td>
    <td>
      ${blocked
        ? `<button class="btn btn-cyan btn-sm" onclick="unblockUser(${u.id}, '${esc(u.name)}', this)">↑ Unblock</button>`
        : `<button class="btn btn-red  btn-sm" onclick="blockUser(${u.id},   '${esc(u.name)}', this)">⊘ Block</button>`
      }
    </td>
  </tr>`;
}

function rowBlacklist(r, n) {
  const act = r.is_active;
  const by  = r.blocked_by === 'auto' ? 'b-violet' : 'b-cyan';
  const pu  = r.blocked_until === 'Permanen';
  return `<tr class="${act ? 'row-blocked' : ''}">
    <td class="dim">${n}</td>
    <td class="mono-sm"><strong>${esc(r.ip_address)}</strong></td>
    <td class="dim trunc" style="max-width:200px">${esc(r.reason)}</td>
    <td><span class="badge ${by}">${r.blocked_by}</span></td>
    <td>${r.block_count}</td>
    <td>${pu
      ? `<span class="badge b-perm">PERMANENT</span>`
      : `<span class="dim">${r.blocked_until}</span>`
    }</td>
    <td class="dim">${r.blocked_at}</td>
    <td><span class="badge ${act ? 'b-red' : 'b-muted'}">${act ? 'ACTIVE' : 'EXPIRED'}</span></td>
    <td>
      <button class="btn btn-amber btn-sm"
              onclick="removeBlacklist('${esc(r.ip_address)}', this)">✕ Remove</button>
    </td>
  </tr>`;
}

function rowWhitelist(r, n) {
  return `<tr class="row-wl">
    <td class="dim">${n}</td>
    <td class="mono-sm"><strong>${esc(r.ip_address)}</strong></td>
    <td>${esc(r.label)}</td>
    <td class="dim">${esc(r.added_by)}</td>
    <td class="dim">${r.created_at}</td>
    <td>
      <button class="btn btn-amber btn-sm"
              onclick="removeWhitelist('${esc(r.ip_address)}', this)">✕ Remove</button>
    </td>
  </tr>`;
}

// ─────────────────────────────────────────────────────────────────────────────
// COUNTER BADGES
// ─────────────────────────────────────────────────────────────────────────────
function updateCount(tab, n, hasMore) {
  const el = document.getElementById('cnt-' + tab);
  if (el) el.textContent = hasMore ? n + '+' : n;
}

function updateNavBadge(tab, n, hasMore) {
  const el = document.getElementById('nb-' + tab);
  if (el) el.textContent = hasMore ? n + '+' : n;
}

// ─────────────────────────────────────────────────────────────────────────────
// ACTION HANDLERS
// ─────────────────────────────────────────────────────────────────────────────

async function toggleDevice(id, btn) {
  const isRev = btn.classList.contains('btn-red');
  const ok    = await confirm2(`${isRev ? 'Revoke' : 'Restore'} device ini?`);
  if (!ok) return;
  btn.disabled = true;
  try {
    const d = await apiFetch('POST', `/devices/${id}/revoke`);
    toast(d.message, 'ok');
    setTimeout(() => resetLoad('devices'), 500);
  } catch (e) {
    toast(e.message, 'err');
    btn.disabled = false;
  }
}

async function unblockUser(userId, name, btn) {
  const ok = await confirm2(
    `Unblock user <strong>${esc(name)}</strong>?<br><br>` +
    `Tindakan ini akan: hapus block cache, hapus 10 log block terbaru, ` +
    `restore semua device yang direvoke, dan aktifkan kembali akun.`
  );
  if (!ok) return;
  btn.disabled    = true;
  btn.textContent = '…';
  try {
    const d = await apiFetch('POST', `/users/${userId}/unblock`);
    toast(d.message, 'ok');
    setTimeout(() => resetLoad('users'), 500);
    loadStats();
  } catch (e) {
    toast(e.message, 'err');
    btn.disabled    = false;
    btn.textContent = '↑ Unblock';
  }
}

async function blockUser(userId, name, btn) {
  const minutes = await prompt2(
    `Block <strong>${esc(name)}</strong>`,
    'Durasi blokir (menit). Kosongkan untuk permanent:'
  );
  if (minutes === null) return;
  btn.disabled    = true;
  btn.textContent = '…';
  try {
    const body = { reason: 'Manual block via dev monitor' };
    if (minutes.trim()) body.minutes = parseInt(minutes);
    const d = await apiFetch('POST', `/users/${userId}/block`, body);
    toast(d.message, 'ok');
    setTimeout(() => resetLoad('users'), 500);
    loadStats();
  } catch (e) {
    toast(e.message, 'err');
    btn.disabled    = false;
    btn.textContent = '⊘ Block';
  }
}

async function addBlacklist() {
  const ip      = v('bl-ip');
  const reason  = v('bl-reason');
  const minutes = v('bl-minutes');
  if (!ip) { toast('IP address wajib diisi', 'err'); return; }
  try {
    const body = { ip_address: ip };
    if (reason)  body.reason  = reason;
    if (minutes) body.minutes = parseInt(minutes);
    const d = await apiFetch('POST', '/ip-blacklist', body);
    toast(d.message, 'ok');
    ['bl-ip', 'bl-reason', 'bl-minutes'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.value = '';
    });
    resetLoad('blacklist');
    loadStats();
  } catch (e) { toast(e.message, 'err'); }
}

async function removeBlacklist(ip, btn) {
  const ok = await confirm2(`Hapus <strong>${esc(ip)}</strong> dari blacklist?`);
  if (!ok) return;
  btn.disabled = true;
  try {
    const d = await apiFetch('DELETE', `/ip-blacklist/${encodeURIComponent(ip)}`);
    toast(d.message, 'ok');
    resetLoad('blacklist');
    loadStats();
  } catch (e) {
    toast(e.message, 'err');
    btn.disabled = false;
  }
}

async function addWhitelist() {
  const ip    = v('wl-ip');
  const label = v('wl-label');
  if (!ip) { toast('IP address wajib diisi', 'err'); return; }
  try {
    const d = await apiFetch('POST', '/ip-whitelist', { ip_address: ip, label });
    toast(d.message, 'ok');
    ['wl-ip', 'wl-label'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.value = '';
    });
    resetLoad('whitelist');
    loadStats();
  } catch (e) { toast(e.message, 'err'); }
}

async function removeWhitelist(ip, btn) {
  const ok = await confirm2(`Hapus <strong>${esc(ip)}</strong> dari whitelist?`);
  if (!ok) return;
  btn.disabled = true;
  try {
    const d = await apiFetch('DELETE', `/ip-whitelist/${encodeURIComponent(ip)}`);
    toast(d.message, 'ok');
    resetLoad('whitelist');
    loadStats();
  } catch (e) {
    toast(e.message, 'err');
    btn.disabled = false;
  }
}

function exportCSV() {
  const decision = document.getElementById('f-logs-decision')?.value || '';
  const url = BASE + '/export/logs' + (decision ? '?decision=' + encodeURIComponent(decision) : '');
  window.open(url, '_blank');
}

// ─────────────────────────────────────────────────────────────────────────────
// STATS — fetch dari /api/stats, update semua tile
// ─────────────────────────────────────────────────────────────────────────────
async function loadStats() {
  try {
    const resp = await fetch(BASE + '/stats', {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
    });
    if (!resp.ok) return;
    const d = await resp.json();

    const map = {
      's-users':      d.users,
      's-active':     d.active_users,
      's-logs':       d.total_logs,
      's-blocked':    d.blocked_logs,
      's-otps':       d.active_otps,
      's-devices':    d.trusted_devices,
      's-blacklist':  d.ip_blacklisted,
      's-whitelist':  d.ip_whitelisted,
      's-userblocks': d.users_blocked,
    };

    Object.entries(map).forEach(([id, val]) => {
      const el = document.getElementById(id);
      if (!el) return;
      const strVal = (val !== undefined && val !== null) ? String(val) : '0';
      if (el.textContent !== strVal) {
        el.textContent = strVal;
        el.classList.add('updated');
        setTimeout(() => el.classList.remove('updated'), 1200);
      }
    });

    const ts = new Date().toLocaleTimeString('id-ID');
    const lu = document.getElementById('last-update');
    const ls = document.getElementById('last-update-sidebar');
    if (lu) lu.textContent = ts;
    if (ls) ls.textContent = 'Updated ' + ts;

  } catch (_) { /* stats gagal → silent */ }
}

// ─────────────────────────────────────────────────────────────────────────────
// API HELPER
// ─────────────────────────────────────────────────────────────────────────────
async function apiFetch(method, path, body = null) {
  const opts = {
    method,
    headers: {
      'Accept':       'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': CSRF,
    },
  };
  if (body) opts.body = JSON.stringify(body);
  const r = await fetch(BASE + path, opts);
  const d = await r.json();
  if (!r.ok) throw new Error(d.message || 'HTTP ' + r.status);
  return d;
}

// ─────────────────────────────────────────────────────────────────────────────
// MODAL HELPERS
// ─────────────────────────────────────────────────────────────────────────────
let modalResolve = null;

function confirm2(html) {
  return new Promise(resolve => {
    modalResolve = resolve;
    document.getElementById('m-title').textContent = 'Konfirmasi';
    document.getElementById('m-body').innerHTML    = html;
    document.getElementById('m-inp').style.display = 'none';
    document.getElementById('m-ok').textContent    = 'Konfirmasi';
    document.getElementById('m-ok').onclick = () => { closeModal(); resolve(true); };
    document.getElementById('overlay').classList.add('open');
  });
}

function prompt2(title, label) {
  return new Promise(resolve => {
    document.getElementById('m-title').innerHTML  = title;
    document.getElementById('m-body').textContent = label;
    const inp = document.getElementById('m-inp');
    inp.style.display = '';
    inp.value = '';
    document.getElementById('m-ok').textContent = 'OK';
    document.getElementById('m-ok').onclick = () => {
      const val = inp.value;
      closeModal();
      resolve(val);
    };
    document.getElementById('overlay').classList.add('open');
    setTimeout(() => inp.focus(), 100);
  });
}

function closeModal() {
  document.getElementById('overlay').classList.remove('open');
  if (modalResolve) { modalResolve(null); modalResolve = null; }
}

document.getElementById('overlay').addEventListener('click', e => {
  if (e.target === e.currentTarget) closeModal();
});

// ─────────────────────────────────────────────────────────────────────────────
// UTILITIES
// ─────────────────────────────────────────────────────────────────────────────
function v(id) {
  return document.getElementById(id)?.value?.trim() ?? '';
}

function esc(str) {
  if (str == null) return '—';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function tryParse(val) {
  try { return JSON.parse(val); } catch { return []; }
}

function stateRow(cols, msg) {
  return `<tr class="state-row"><td colspan="${cols}">${msg}</td></tr>`;
}

let toastTimer;
function toast(msg, type = 'ok') {
  clearTimeout(toastTimer);
  const el = document.getElementById('toast');
  el.innerHTML = (type === 'ok' ? '✓ ' : '✕ ') + esc(msg);
  el.className = `toast toast-${type} show`;
  toastTimer   = setTimeout(() => el.classList.remove('show'), 3500);
}
</script>