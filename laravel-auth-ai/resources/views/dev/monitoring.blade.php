<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>DEV Monitor — AI Auth</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Syne:wght@400;600;800&display=swap');
  :root{--bg:#090b10;--surface:#0e1117;--surface2:#141822;--border:#1e2535;--accent:#00e5ff;--accent2:#ff4081;--accent3:#69ff47;--warn:#ffab00;--text:#e2e8f0;--muted:#4a5568;--allow:#69ff47;--otp:#ffab00;--block:#ff4081;--font-mono:'JetBrains Mono',monospace;--font-display:'Syne',sans-serif;}
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--text);font-family:var(--font-mono);min-height:100vh;overflow-x:hidden;}
  body::before{content:'';position:fixed;inset:0;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,229,255,0.015) 2px,rgba(0,229,255,0.015) 4px);pointer-events:none;z-index:999;}
  .header{background:var(--surface);border-bottom:1px solid var(--border);padding:16px 32px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
  .header-left{display:flex;align-items:center;gap:16px;}
  .logo{font-family:var(--font-display);font-size:18px;font-weight:800;color:var(--accent);letter-spacing:-0.5px;text-transform:uppercase;}
  .dev-badge{background:var(--accent2);color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:3px;letter-spacing:1px;animation:blink 2s step-end infinite;}
  @keyframes blink{0%,100%{opacity:1}50%{opacity:.4}}
  .header-right{display:flex;align-items:center;gap:16px;font-size:12px;color:var(--muted);}
  .live-dot{width:8px;height:8px;border-radius:50%;background:var(--accent3);animation:pulse 1.5s ease-in-out infinite;display:inline-block;margin-right:6px;}
  @keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.8)}}
  .container{padding:24px 32px;max-width:1600px;margin:0 auto;}
  .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:28px;}
  .stat-card{background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:16px;position:relative;overflow:hidden;transition:border-color .2s;}
  .stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:var(--accent);}
  .stat-card:nth-child(2)::before{background:var(--accent3);}
  .stat-card:nth-child(3)::before{background:var(--warn);}
  .stat-card:nth-child(4)::before{background:var(--accent2);}
  .stat-card:nth-child(5)::before{background:#a78bfa;}
  .stat-card:nth-child(6)::before{background:#34d399;}
  .stat-card:nth-child(7)::before{background:#f97316;}
  .stat-card:nth-child(8)::before{background:#06b6d4;}
  .stat-card:nth-child(9)::before{background:#ec4899;}
  .stat-card:hover{border-color:var(--accent);}
  .stat-label{font-size:10px;color:var(--muted);letter-spacing:1px;text-transform:uppercase;margin-bottom:6px;}
  .stat-value{font-family:var(--font-display);font-size:28px;font-weight:800;color:var(--text);line-height:1;}
  .tabs{display:flex;gap:4px;margin-bottom:24px;border-bottom:1px solid var(--border);flex-wrap:wrap;}
  .tab{padding:10px 16px;font-family:var(--font-mono);font-size:11px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;cursor:pointer;border:none;background:transparent;color:var(--muted);border-bottom:2px solid transparent;transition:all .2s;position:relative;bottom:-1px;}
  .tab:hover{color:var(--text);}
  .tab.active{color:var(--accent);border-bottom-color:var(--accent);}
  .table-wrap{background:var(--surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;}
  .table-header{padding:16px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:8px;}
  .table-title{font-family:var(--font-display);font-size:13px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--accent);}
  .toolbar{display:flex;gap:8px;align-items:center;flex-wrap:wrap;}
  .filter-select{background:var(--surface2);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);font-size:11px;padding:6px 10px;border-radius:4px;outline:none;cursor:pointer;}
  .filter-select:focus{border-color:var(--accent);}
  .refresh-btn{background:var(--surface2);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);font-size:11px;padding:6px 14px;border-radius:4px;cursor:pointer;transition:all .2s;}
  .refresh-btn:hover{border-color:var(--accent);color:var(--accent);}
  .search-input{background:var(--surface2);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);font-size:12px;padding:6px 14px;border-radius:4px;outline:none;width:200px;transition:border-color .2s;}
  .search-input:focus{border-color:var(--accent);}
  .search-input::placeholder{color:var(--muted);}
  .text-input{background:var(--surface2);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);font-size:12px;padding:6px 12px;border-radius:4px;outline:none;width:160px;}
  .text-input:focus{border-color:var(--accent);}
  .text-input::placeholder{color:var(--muted);}
  .overflow-x{overflow-x:auto;}
  table{width:100%;border-collapse:collapse;font-size:12px;}
  thead tr{background:var(--surface2);border-bottom:1px solid var(--border);}
  th{padding:10px 16px;text-align:left;font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--muted);font-weight:600;white-space:nowrap;}
  td{padding:10px 16px;border-bottom:1px solid var(--border);color:var(--text);vertical-align:middle;}
  tr:last-child td{border-bottom:none;}
  tbody tr{transition:background .15s;}
  tbody tr:hover{background:var(--surface2);}
  tbody tr.row-blocked{background:rgba(255,64,129,0.04);}
  tbody tr.row-blocked:hover{background:rgba(255,64,129,0.08);}
  tbody tr.row-whitelisted{background:rgba(105,255,71,0.03);}
  .badge{display:inline-block;padding:2px 8px;border-radius:3px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;}
  .badge-allow,.badge-success,.badge-yes,.badge-trusted,.badge-verified,.badge-active{background:rgba(105,255,71,.15);color:var(--allow);border:1px solid rgba(105,255,71,.3);}
  .badge-otp,.badge-otp_required{background:rgba(255,171,0,.15);color:var(--otp);border:1px solid rgba(255,171,0,.3);}
  .badge-block,.badge-blocked,.badge-failed,.badge-revoked{background:rgba(255,64,129,.15);color:var(--block);border:1px solid rgba(255,64,129,.3);}
  .badge-no,.badge-expired{background:rgba(74,85,104,.3);color:var(--muted);border:1px solid var(--border);}
  .badge-fallback,.badge-auto{background:rgba(167,139,250,.15);color:#a78bfa;border:1px solid rgba(167,139,250,.3);}
  .badge-manual{background:rgba(0,229,255,.1);color:var(--accent);border:1px solid rgba(0,229,255,.2);}
  .badge-permanent{background:rgba(255,64,129,.2);color:var(--block);border:1px solid rgba(255,64,129,.4);}
  .badge-whitelist{background:rgba(105,255,71,.2);color:var(--allow);border:1px solid rgba(105,255,71,.4);}
  .btn{font-family:var(--font-mono);font-size:10px;font-weight:700;padding:4px 10px;border-radius:4px;cursor:pointer;transition:all .2s;letter-spacing:.5px;white-space:nowrap;border:1px solid;}
  .btn:disabled{opacity:.4;cursor:default;}
  .btn-unblock{background:rgba(0,229,255,.08);border-color:rgba(0,229,255,.3);color:var(--accent);}
  .btn-unblock:hover:not(:disabled){background:rgba(0,229,255,.2);}
  .btn-block{background:rgba(255,64,129,.08);border-color:rgba(255,64,129,.3);color:var(--block);}
  .btn-block:hover:not(:disabled){background:rgba(255,64,129,.2);}
  .btn-revoke{background:rgba(255,64,129,.08);border-color:rgba(255,64,129,.3);color:var(--block);}
  .btn-revoke:hover:not(:disabled){background:rgba(255,64,129,.2);}
  .btn-restore{background:rgba(105,255,71,.08);border-color:rgba(105,255,71,.3);color:var(--allow);}
  .btn-restore:hover:not(:disabled){background:rgba(105,255,71,.2);}
  .btn-remove{background:rgba(255,171,0,.08);border-color:rgba(255,171,0,.3);color:var(--warn);}
  .btn-remove:hover:not(:disabled){background:rgba(255,171,0,.2);}
  .btn-add{background:rgba(0,229,255,.1);border-color:rgba(0,229,255,.4);color:var(--accent);padding:6px 16px;font-size:11px;}
  .btn-add:hover{background:rgba(0,229,255,.25);}
  .add-form{display:flex;gap:8px;align-items:center;padding:12px 20px;border-bottom:1px solid var(--border);flex-wrap:wrap;background:rgba(0,229,255,0.03);}
  .add-form-label{font-size:11px;color:var(--muted);letter-spacing:.5px;}
  .mono{font-family:var(--font-mono);}
  .muted{color:var(--muted);}
  .small{font-size:11px;}
  .flags{display:flex;flex-wrap:wrap;gap:4px;max-width:200px;}
  .flag-chip{font-size:9px;padding:2px 6px;background:rgba(255,171,0,.1);border:1px solid rgba(255,171,0,.2);color:var(--otp);border-radius:3px;white-space:nowrap;}
  .risk-wrap{display:flex;align-items:center;gap:8px;}
  .risk-bar{width:60px;height:4px;background:var(--border);border-radius:2px;overflow:hidden;}
  .risk-fill{height:100%;border-radius:2px;}
  .loading{text-align:center;padding:60px;color:var(--muted);font-size:13px;}
  .spinner{display:inline-block;width:20px;height:20px;border:2px solid var(--border);border-top-color:var(--accent);border-radius:50%;animation:spin .8s linear infinite;margin-right:10px;vertical-align:middle;}
  @keyframes spin{to{transform:rotate(360deg)}}
  .empty{text-align:center;padding:60px;color:var(--muted);font-size:13px;}
  .tab-content{display:none;}
  .tab-content.active{display:block;}
  ::-webkit-scrollbar{width:6px;height:6px;}::-webkit-scrollbar-track{background:var(--surface);}::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px;}
  .ua{max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--muted);}
  .fp{max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:var(--font-mono);color:var(--muted);font-size:11px;}
  .otp-code{font-family:var(--font-mono);font-size:11px;font-weight:600;color:var(--warn);letter-spacing:2px;background:rgba(255,171,0,.08);padding:2px 8px;border-radius:4px;border:1px solid rgba(255,171,0,.2);}
  .toast{position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:6px;font-size:12px;font-family:var(--font-mono);font-weight:600;z-index:9999;opacity:0;transform:translateY(8px);transition:all .3s;pointer-events:none;max-width:400px;}
  .toast.show{opacity:1;transform:translateY(0);}
  .toast-success{background:#0d2b1a;border:1px solid rgba(105,255,71,.4);color:var(--allow);}
  .toast-error{background:#2b0d14;border:1px solid rgba(255,64,129,.4);color:var(--block);}
</style>
</head>
<body>
<div class="header">
  <div class="header-left">
    <div class="logo">AI Auth Monitor</div>
    <span class="dev-badge">DEV ONLY</span>
  </div>
  <div class="header-right">
    <span><span class="live-dot"></span>LIVE</span>
    <span id="last-update">—</span>
  </div>
</div>

<div class="container">
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Total Users</div><div class="stat-value" id="s-users">—</div></div>
    <div class="stat-card"><div class="stat-label">Active Users</div><div class="stat-value" id="s-active">—</div></div>
    <div class="stat-card"><div class="stat-label">Login Logs</div><div class="stat-value" id="s-logs">—</div></div>
    <div class="stat-card"><div class="stat-label">Blocked Logs</div><div class="stat-value" id="s-blocked">—</div></div>
    <div class="stat-card"><div class="stat-label">Active OTPs</div><div class="stat-value" id="s-otps">—</div></div>
    <div class="stat-card"><div class="stat-label">Trusted Devices</div><div class="stat-value" id="s-devices">—</div></div>
    <div class="stat-card"><div class="stat-label">IP Blacklisted</div><div class="stat-value" id="s-blacklist">—</div></div>
    <div class="stat-card"><div class="stat-label">IP Whitelisted</div><div class="stat-value" id="s-whitelist">—</div></div>
    <div class="stat-card"><div class="stat-label">Users Blocked</div><div class="stat-value" id="s-userblocks">—</div></div>
  </div>

  <div class="tabs">
    <button class="tab active" onclick="switchTab('otp')">🔐 OTP</button>
    <button class="tab" onclick="switchTab('logs')">📋 Login Logs</button>
    <button class="tab" onclick="switchTab('devices')">💻 Devices</button>
    <button class="tab" onclick="switchTab('users')">👥 Users</button>
    <button class="tab" onclick="switchTab('blacklist')">🚫 IP Blacklist</button>
    <button class="tab" onclick="switchTab('whitelist')">✅ IP Whitelist</button>
  </div>

  <!-- OTP Tab -->
  <div class="tab-content active" id="tab-otp">
    <div class="table-wrap">
      <div class="table-header">
        <span class="table-title">OTP Verifications</span>
        <div class="toolbar">
          <select class="filter-select" onchange="filterByCol('otp-table',this.value,4)">
            <option value="">Semua Status</option><option value="active">Active</option><option value="verified">Verified</option><option value="expired">Expired</option>
          </select>
          <input class="search-input" placeholder="Cari user / email..." oninput="filterTable('otp-table',this.value)">
          <button class="refresh-btn" onclick="loadOtps()">↻ Refresh</button>
        </div>
      </div>
      <div class="overflow-x">
        <table id="otp-table">
          <thead><tr><th>#</th><th>User</th><th>Email</th><th>Token</th><th>Status</th><th>Attempts</th><th>Expires At</th><th>Verified At</th><th>Created</th></tr></thead>
          <tbody id="otp-body"><tr><td colspan="9" class="loading"><span class="spinner"></span>Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Logs Tab -->
  <div class="tab-content" id="tab-logs">
    <div class="table-wrap">
      <div class="table-header">
        <span class="table-title">Login Logs</span>
        <div class="toolbar">
          <select class="filter-select" onchange="filterByCol('logs-table',this.value,5)">
            <option value="">Semua Status</option><option value="success">Success</option><option value="otp_required">OTP Required</option><option value="blocked">Blocked</option><option value="failed">Failed</option>
          </select>
          <select class="filter-select" onchange="filterByCol('logs-table',this.value,6)">
            <option value="">Semua Decision</option><option value="allow">ALLOW</option><option value="otp">OTP</option><option value="block">BLOCK</option>
          </select>
          <input class="search-input" placeholder="Cari user / IP..." oninput="filterTable('logs-table',this.value)">
          <button class="refresh-btn" onclick="loadLogs()">↻ Refresh</button>
        </div>
      </div>
      <div class="overflow-x">
        <table id="logs-table">
          <thead><tr><th>#</th><th>Waktu</th><th>User</th><th>Email</th><th>IP</th><th>Status</th><th>Decision</th><th>Risk</th><th>Flags</th><th>FP</th></tr></thead>
          <tbody id="logs-body"><tr><td colspan="10" class="loading"><span class="spinner"></span>Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Devices Tab -->
  <div class="tab-content" id="tab-devices">
    <div class="table-wrap">
      <div class="table-header">
        <span class="table-title">Trusted Devices</span>
        <div class="toolbar">
          <select class="filter-select" onchange="filterByCol('devices-table',this.value,6)">
            <option value="">Semua</option><option value="trusted">Trusted</option><option value="revoked">Revoked</option>
          </select>
          <input class="search-input" placeholder="Cari user / IP..." oninput="filterTable('devices-table',this.value)">
          <button class="refresh-btn" onclick="loadDevices()">↻ Refresh</button>
        </div>
      </div>
      <div class="overflow-x">
        <table id="devices-table">
          <thead><tr><th>#</th><th>User</th><th>Email</th><th>Label</th><th>Fingerprint</th><th>IP</th><th>Status</th><th>Last Seen</th><th>Trusted Until</th><th>Aksi</th></tr></thead>
          <tbody id="devices-body"><tr><td colspan="10" class="loading"><span class="spinner"></span>Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Users Tab -->
  <div class="tab-content" id="tab-users">
    <div class="table-wrap">
      <div class="table-header">
        <span class="table-title">Users</span>
        <div class="toolbar">
          <select class="filter-select" onchange="filterByCol('users-table',this.value,10)">
            <option value="">Semua Status</option><option value="blocked">Blocked</option><option value="ok">OK</option>
          </select>
          <input class="search-input" placeholder="Cari nama / email..." oninput="filterTable('users-table',this.value)">
          <button class="refresh-btn" onclick="loadUsers()">↻ Refresh</button>
        </div>
      </div>
      <div class="overflow-x">
        <table id="users-table">
          <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Active</th><th>Verified</th><th>Last Login</th><th>Last IP</th><th>Logs</th><th>Devices</th><th>Created</th><th>Risk Status</th><th>Aksi</th></tr></thead>
          <tbody id="users-body"><tr><td colspan="12" class="loading"><span class="spinner"></span>Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- IP Blacklist Tab -->
  <div class="tab-content" id="tab-blacklist">
    <div class="table-wrap">
      <div class="table-header">
        <span class="table-title">IP Blacklist</span>
        <div class="toolbar">
          <input class="search-input" placeholder="Cari IP / alasan..." oninput="filterTable('blacklist-table',this.value)">
          <button class="refresh-btn" onclick="loadBlacklist()">↻ Refresh</button>
        </div>
      </div>
      <div class="add-form">
        <span class="add-form-label">+ Tambah:</span>
        <input class="text-input" id="bl-ip" placeholder="IP Address" style="width:160px">
        <input class="text-input" id="bl-reason" placeholder="Alasan (opsional)" style="width:200px">
        <input class="text-input" id="bl-minutes" placeholder="Menit (kosong=permanen)" style="width:180px" type="number" min="1">
        <button class="btn btn-add" onclick="addBlacklist()">+ Blacklist IP</button>
      </div>
      <div class="overflow-x">
        <table id="blacklist-table">
          <thead><tr><th>#</th><th>IP Address</th><th>Alasan</th><th>Oleh</th><th>Kali Block</th><th>Blocked Until</th><th>Blocked At</th><th>Status</th><th>Aksi</th></tr></thead>
          <tbody id="blacklist-body"><tr><td colspan="9" class="loading"><span class="spinner"></span>Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- IP Whitelist Tab -->
  <div class="tab-content" id="tab-whitelist">
    <div class="table-wrap">
      <div class="table-header">
        <span class="table-title">IP Whitelist</span>
        <div class="toolbar">
          <input class="search-input" placeholder="Cari IP / label..." oninput="filterTable('whitelist-table',this.value)">
          <button class="refresh-btn" onclick="loadWhitelist()">↻ Refresh</button>
        </div>
      </div>
      <div class="add-form">
        <span class="add-form-label">+ Tambah:</span>
        <input class="text-input" id="wl-ip" placeholder="IP Address" style="width:160px">
        <input class="text-input" id="wl-label" placeholder="Label (cth: Office Jakarta)" style="width:220px">
        <button class="btn btn-add" onclick="addWhitelist()">+ Whitelist IP</button>
      </div>
      <div class="overflow-x">
        <table id="whitelist-table">
          <thead><tr><th>#</th><th>IP Address</th><th>Label</th><th>Ditambahkan Oleh</th><th>Created</th><th>Aksi</th></tr></thead>
          <tbody id="whitelist-body"><tr><td colspan="6" class="loading"><span class="spinner"></span>Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
const BASE = '/dev/monitoring/api';
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function switchTab(tab) {
  document.querySelectorAll('.tab').forEach((el,i) => {
    el.classList.toggle('active', ['otp','logs','devices','users','blacklist','whitelist'][i] === tab);
  });
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
  document.getElementById('tab-'+tab).classList.add('active');
  const loaders = {otp:loadOtps,logs:loadLogs,devices:loadDevices,users:loadUsers,blacklist:loadBlacklist,whitelist:loadWhitelist};
  loaders[tab]?.();
}

async function loadStats() {
  try {
    const d = await api('GET', '/stats');
    document.getElementById('s-users').textContent      = d.users;
    document.getElementById('s-active').textContent     = d.active_users;
    document.getElementById('s-logs').textContent       = d.total_logs;
    document.getElementById('s-blocked').textContent    = d.blocked_logs;
    document.getElementById('s-otps').textContent       = d.active_otps;
    document.getElementById('s-devices').textContent    = d.trusted_devices;
    document.getElementById('s-blacklist').textContent  = d.ip_blacklisted;
    document.getElementById('s-whitelist').textContent  = d.ip_whitelisted;
    document.getElementById('s-userblocks').textContent = d.users_blocked;
    document.getElementById('last-update').textContent  = 'Updated ' + new Date().toLocaleTimeString('id-ID');
  } catch(e) {}
}

async function loadOtps() {
  const tbody = document.getElementById('otp-body');
  tbody.innerHTML = loading(9);
  try {
    const data = await api('GET','/otps');
    if (!data.length) { tbody.innerHTML = empty(9); return; }
    tbody.innerHTML = data.map((o,i) => `<tr>
      <td class="muted small">${i+1}</td><td><strong>${esc(o.user)}</strong></td><td class="muted small">${esc(o.email)}</td>
      <td><span class="otp-code">${esc(o.otp_code?o.otp_code.substring(0,20)+'...':'—')}</span></td>
      <td><span class="badge badge-${o.status}">${o.status}</span></td>
      <td style="color:${o.attempts>=3?'var(--block)':'var(--text)'}">${o.attempts}/3</td>
      <td class="muted small">${o.expires_at??'—'}</td><td class="muted small">${o.verified_at??'—'}</td><td class="muted small">${o.created_at}</td>
    </tr>`).join('');
  } catch(e) { tbody.innerHTML = err(9,e); }
}

async function loadLogs() {
  const tbody = document.getElementById('logs-body');
  tbody.innerHTML = loading(10);
  try {
    const data = await api('GET','/logs');
    if (!data.length) { tbody.innerHTML = empty(10); return; }
    tbody.innerHTML = data.map((l,i) => {
      const score = l.risk_score??0;
      const color = score<30?'#69ff47':score<60?'#ffab00':'#ff4081';
      const flags = Array.isArray(l.reason_flags)?l.reason_flags:(l.reason_flags?JSON.parse(l.reason_flags):[]);
      return `<tr>
        <td class="muted small">${i+1}</td><td class="muted small">${l.occurred_at??'—'}</td>
        <td><strong>${esc(l.user)}</strong></td><td class="muted small">${esc(l.email)}</td>
        <td class="mono small">${esc(l.ip_address??'—')}</td>
        <td><span class="badge badge-${l.status}">${l.status}</span></td>
        <td><span class="badge badge-${(l.decision??'').toLowerCase()}">${l.decision??'—'}</span></td>
        <td><div class="risk-wrap"><span style="color:${color};font-weight:600;min-width:24px">${score}</span><div class="risk-bar"><div class="risk-fill" style="width:${Math.min(score,100)}%;background:${color}"></div></div></div></td>
        <td><div class="flags">${flags.map(f=>`<span class="flag-chip">${esc(f)}</span>`).join('')||'—'}</div></td>
        <td><span class="fp" title="${esc(l.device_fp)}">${esc(l.device_fp??'—')}</span></td>
      </tr>`;
    }).join('');
  } catch(e) { tbody.innerHTML = err(10,e); }
}

async function loadDevices() {
  const tbody = document.getElementById('devices-body');
  tbody.innerHTML = loading(10);
  try {
    const data = await api('GET','/devices');
    if (!data.length) { tbody.innerHTML = empty(10); return; }
    tbody.innerHTML = data.map((d,i) => `<tr>
      <td class="muted small">${i+1}</td><td><strong>${esc(d.user)}</strong></td><td class="muted small">${esc(d.email)}</td>
      <td class="small">${esc(d.device_label)}</td>
      <td><span class="fp" title="${esc(d.fingerprint)}">${esc(d.fingerprint)}</span></td>
      <td class="mono small">${esc(d.ip_address??'—')}</td>
      <td><span class="badge badge-${d.is_revoked?'revoked':'trusted'}">${d.is_revoked?'REVOKED':'TRUSTED'}</span></td>
      <td class="muted small">${d.last_seen??'—'}</td><td class="muted small">${d.trusted_until??'—'}</td>
      <td><button class="btn btn-${d.is_revoked?'restore':'revoke'}" onclick="toggleDevice(${d.id},this)">${d.is_revoked?'↺ Restore':'⊘ Revoke'}</button></td>
    </tr>`).join('');
  } catch(e) { tbody.innerHTML = err(10,e); }
}

async function loadUsers() {
  const tbody = document.getElementById('users-body');
  tbody.innerHTML = loading(12);
  try {
    const data = await api('GET','/users');
    if (!data.length) { tbody.innerHTML = empty(12); return; }
    tbody.innerHTML = data.map(u => `<tr class="${u.is_blocked?'row-blocked':''}">
      <td class="muted small">${u.id}</td><td><strong>${esc(u.name)}</strong></td><td class="muted small">${esc(u.email)}</td>
      <td><span class="badge badge-${u.is_active?'yes':'no'}">${u.is_active?'YES':'NO'}</span></td>
      <td><span class="badge badge-${u.verified?'verified':'no'}">${u.verified?'YES':'NO'}</span></td>
      <td class="muted small">${u.last_login_at??'—'}</td>
      <td class="mono small">${esc(u.last_login_ip??'—')}</td>
      <td>${u.login_count}</td><td>${u.device_count}</td>
      <td class="muted small">${u.created_at}</td>
      <td>${u.is_blocked?`<span class="badge badge-blocked">BLOCKED</span>`:`<span class="badge badge-allow" style="font-size:9px">OK</span>`}</td>
      <td style="display:flex;gap:4px;flex-wrap:wrap">
        ${u.is_blocked
          ? `<button class="btn btn-unblock" onclick="unblockUser(${u.id},'${esc(u.name)}',this)">↑ Unblock</button>`
          : `<button class="btn btn-block" onclick="blockUser(${u.id},'${esc(u.name)}',this)">⊘ Block</button>`
        }
      </td>
    </tr>`).join('');
  } catch(e) { tbody.innerHTML = err(12,e); }
}

async function loadBlacklist() {
  const tbody = document.getElementById('blacklist-body');
  tbody.innerHTML = loading(9);
  try {
    const data = await api('GET','/ip-blacklist');
    if (!data.length) { tbody.innerHTML = empty(9,'Tidak ada IP di blacklist'); return; }
    tbody.innerHTML = data.map((r,i) => `<tr class="${r.is_active?'row-blocked':''}">
      <td class="muted small">${i+1}</td>
      <td class="mono"><strong>${esc(r.ip_address)}</strong></td>
      <td class="muted small">${esc(r.reason)}</td>
      <td><span class="badge badge-${r.blocked_by==='auto'?'auto':'manual'}">${r.blocked_by}</span></td>
      <td>${r.block_count}</td>
      <td class="muted small">${r.blocked_until==='Permanen'?`<span class="badge badge-permanent">PERMANEN</span>`:r.blocked_until}</td>
      <td class="muted small">${r.blocked_at}</td>
      <td><span class="badge badge-${r.is_active?'blocked':'expired'}">${r.is_active?'ACTIVE':'EXPIRED'}</span></td>
      <td><button class="btn btn-remove" onclick="removeBlacklist('${esc(r.ip_address)}',this)">✕ Hapus</button></td>
    </tr>`).join('');
  } catch(e) { tbody.innerHTML = err(9,e); }
}

async function loadWhitelist() {
  const tbody = document.getElementById('whitelist-body');
  tbody.innerHTML = loading(6);
  try {
    const data = await api('GET','/ip-whitelist');
    if (!data.length) { tbody.innerHTML = empty(6,'Tidak ada IP di whitelist'); return; }
    tbody.innerHTML = data.map((r,i) => `<tr class="row-whitelisted">
      <td class="muted small">${i+1}</td>
      <td class="mono"><strong>${esc(r.ip_address)}</strong></td>
      <td>${esc(r.label)}</td>
      <td class="muted small">${esc(r.added_by)}</td>
      <td class="muted small">${r.created_at}</td>
      <td><button class="btn btn-remove" onclick="removeWhitelist('${esc(r.ip_address)}',this)">✕ Hapus</button></td>
    </tr>`).join('');
  } catch(e) { tbody.innerHTML = err(6,e); }
}

// ── Actions ───────────────────────────────────────────────────────────────

async function unblockUser(userId, name, btn) {
  if (!confirm(`Unblock user "${name}"?\n\nAksi ini akan:\n• Clear cache block counter\n• Hapus 10 block log terbaru\n• Restore semua trusted devices\n• Aktifkan kembali akun`)) return;
  btn.disabled = true; btn.textContent = '...';
  try {
    const d = await api('POST', `/users/${userId}/unblock`);
    showToast(d.message, 'success');
    setTimeout(loadUsers, 800);
    loadStats();
  } catch(e) { showToast(e.message,'error'); btn.disabled=false; btn.textContent='↑ Unblock'; }
}

async function blockUser(userId, name, btn) {
  const minutes = prompt(`Block user "${name}" berapa menit?\n(Kosongkan = permanen)`);
  if (minutes === null) return;
  btn.disabled = true; btn.textContent = '...';
  try {
    const body = {reason:'Manual block via monitoring'};
    if (minutes) body.minutes = parseInt(minutes);
    const d = await api('POST', `/users/${userId}/block`, body);
    showToast(d.message, 'success');
    setTimeout(loadUsers, 800);
    loadStats();
  } catch(e) { showToast(e.message,'error'); btn.disabled=false; btn.textContent='⊘ Block'; }
}

async function toggleDevice(deviceId, btn) {
  const isRevoke = btn.classList.contains('btn-revoke');
  if (!confirm(`${isRevoke?'Revoke':'Restore'} device ini?`)) return;
  btn.disabled = true;
  try {
    const d = await api('POST', `/devices/${deviceId}/revoke`);
    showToast(d.message, 'success');
    setTimeout(loadDevices, 800);
  } catch(e) { showToast(e.message,'error'); btn.disabled=false; }
}

async function addBlacklist() {
  const ip = document.getElementById('bl-ip').value.trim();
  const reason = document.getElementById('bl-reason').value.trim();
  const minutes = document.getElementById('bl-minutes').value.trim();
  if (!ip) { showToast('IP address wajib diisi.','error'); return; }
  try {
    const body = {ip_address:ip};
    if (reason) body.reason = reason;
    if (minutes) body.minutes = parseInt(minutes);
    const d = await api('POST','/ip-blacklist',body);
    showToast(d.message,'success');
    document.getElementById('bl-ip').value='';
    document.getElementById('bl-reason').value='';
    document.getElementById('bl-minutes').value='';
    loadBlacklist(); loadStats();
  } catch(e) { showToast(e.message,'error'); }
}

async function removeBlacklist(ip, btn) {
  if (!confirm(`Hapus IP ${ip} dari blacklist?`)) return;
  btn.disabled = true;
  try {
    const d = await apiFetch('DELETE', `/ip-blacklist/${encodeURIComponent(ip)}`);
    showToast(d.message,'success');
    loadBlacklist(); loadStats();
  } catch(e) { showToast(e.message,'error'); btn.disabled=false; }
}

async function addWhitelist() {
  const ip = document.getElementById('wl-ip').value.trim();
  const label = document.getElementById('wl-label').value.trim();
  if (!ip) { showToast('IP address wajib diisi.','error'); return; }
  try {
    const d = await api('POST','/ip-whitelist',{ip_address:ip, label});
    showToast(d.message,'success');
    document.getElementById('wl-ip').value='';
    document.getElementById('wl-label').value='';
    loadWhitelist(); loadStats();
  } catch(e) { showToast(e.message,'error'); }
}

async function removeWhitelist(ip, btn) {
  if (!confirm(`Hapus IP ${ip} dari whitelist?`)) return;
  btn.disabled = true;
  try {
    const d = await apiFetch('DELETE', `/ip-whitelist/${encodeURIComponent(ip)}`);
    showToast(d.message,'success');
    loadWhitelist(); loadStats();
  } catch(e) { showToast(e.message,'error'); btn.disabled=false; }
}

// ── API Helpers ────────────────────────────────────────────────────────────

async function api(method, path, body=null) {
  return apiFetch(method, path, body);
}

async function apiFetch(method, path, body=null) {
  const opts = {
    method,
    headers: {'X-CSRF-TOKEN':CSRF,'Accept':'application/json','Content-Type':'application/json'},
  };
  if (body) opts.body = JSON.stringify(body);
  const r = await fetch(BASE+path, opts);
  const d = await r.json();
  if (!r.ok) throw new Error(d.message || `HTTP ${r.status}`);
  return d;
}

function filterTable(tableId, query) {
  const q = query.toLowerCase();
  document.querySelectorAll(`#${tableId} tbody tr`).forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

function filterByCol(tableId, value, colIndex) {
  document.querySelectorAll(`#${tableId} tbody tr`).forEach(row => {
    if (!value) { row.style.display=''; return; }
    const cell = row.cells[colIndex];
    row.style.display = cell && cell.textContent.toLowerCase().includes(value.toLowerCase()) ? '' : 'none';
  });
}

function showToast(msg, type='success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = `toast toast-${type} show`;
  setTimeout(() => t.classList.remove('show'), 3500);
}

function loading(cols) { return `<tr><td colspan="${cols}" class="loading"><span class="spinner"></span>Loading...</td></tr>`; }
function empty(cols, msg='Tidak ada data') { return `<tr><td colspan="${cols}" class="empty">${msg}</td></tr>`; }
function err(cols, e) { return `<tr><td colspan="${cols}" class="empty">Error: ${esc(e.message)}</td></tr>`; }
function esc(str) {
  if (str==null) return '—';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadStats();
loadOtps();
setInterval(loadStats, 10000);
</script>
</body>
</html>