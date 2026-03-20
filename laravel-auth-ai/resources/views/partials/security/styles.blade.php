<style>
/* ── Reset & Variables ─────────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg:        #07090f;
  --s1:        #0b0e18;
  --s2:        #0f1320;
  --s3:        #141928;
  --border:    #1c2235;
  --border2:   #242c42;
  --text:      #c8d0e0;
  --text2:     #7a88a6;
  --text3:     #3d4a65;
  --cyan:      #38bdf8;
  --cyan-dim:  rgba(56,189,248,.12);
  --green:     #4ade80;
  --green-dim: rgba(74,222,128,.10);
  --amber:     #fbbf24;
  --amber-dim: rgba(251,191,36,.10);
  --red:       #f87171;
  --red-dim:   rgba(248,113,113,.10);
  --violet:    #a78bfa;
  --mono:      'IBM Plex Mono', monospace;
  --sans:      'DM Sans', sans-serif;
  --r:         6px;
  --sidebar-w: 210px;
}

body {
  background: var(--bg);
  color: var(--text);
  font-family: var(--sans);
  min-height: 100vh;
  font-size: 13px;
  line-height: 1.5;
  overflow-x: hidden;
  display: flex;
}

/* Subtle grid texture */
body::before {
  content: '';
  position: fixed; inset: 0;
  background-image:
    linear-gradient(rgba(56,189,248,.025) 1px, transparent 1px),
    linear-gradient(90deg, rgba(56,189,248,.025) 1px, transparent 1px);
  background-size: 40px 40px;
  pointer-events: none;
  z-index: 0;
}

/* ── Sidebar ───────────────────────────────────────────────────────────── */
.sidebar {
  position: fixed; top: 0; left: 0; bottom: 0;
  width: var(--sidebar-w);
  background: var(--s1);
  border-right: 1px solid var(--border);
  display: flex; flex-direction: column;
  z-index: 300;
  overflow: hidden;
}

.sidebar-logo {
  padding: 18px 20px 14px;
  border-bottom: 1px solid var(--border);
  display: flex; flex-direction: column; gap: 6px;
}

.logo-mark {
  display: flex; align-items: center; gap: 9px;
}
.logo-icon {
  width: 30px; height: 30px; border-radius: 7px;
  background: linear-gradient(135deg, var(--cyan-dim), rgba(56,189,248,.04));
  border: 1px solid rgba(56,189,248,.25);
  display: flex; align-items: center; justify-content: center;
  font-size: 15px; flex-shrink: 0;
}
.logo-text {
  font-family: var(--mono); font-size: 11px; font-weight: 600;
  color: var(--cyan); letter-spacing: .05em; line-height: 1.2;
}
.logo-sub {
  font-family: var(--mono); font-size: 8px; color: var(--text3);
  letter-spacing: .1em; text-transform: uppercase;
}

.dev-tag {
  align-self: flex-start;
  font-family: var(--mono); font-size: 8px; font-weight: 600;
  letter-spacing: .1em; padding: 2px 7px; border-radius: 3px;
  background: rgba(248,113,113,.15); color: var(--red);
  border: 1px solid rgba(248,113,113,.25);
  animation: blink 2.5s ease-in-out infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.5} }

.sidebar-nav {
  flex: 1; overflow-y: auto; padding: 12px 10px;
  scrollbar-width: none;
}
.sidebar-nav::-webkit-scrollbar { display: none; }

.nav-section-label {
  font-family: var(--mono); font-size: 8px; font-weight: 500;
  letter-spacing: .12em; text-transform: uppercase;
  color: var(--text3); padding: 10px 10px 6px;
}

.nav-item {
  display: flex; align-items: center; gap: 10px;
  padding: 8px 10px; border-radius: var(--r);
  cursor: pointer; border: 1px solid transparent;
  transition: all .15s; margin-bottom: 2px;
  user-select: none;
  font-family: var(--sans); font-size: 12px; font-weight: 500;
  color: var(--text2);
}
.nav-item:hover { background: var(--s2); color: var(--text); border-color: var(--border); }
.nav-item.active {
  background: var(--s3); color: var(--cyan);
  border-color: var(--border2);
}
.nav-icon {
  font-size: 13px; width: 18px; text-align: center; flex-shrink: 0;
}
.nav-label { flex: 1; }
.nav-badge {
  font-family: var(--mono); font-size: 9px;
  background: var(--s2); color: var(--text3);
  border: 1px solid var(--border); border-radius: 10px;
  padding: 1px 6px; min-width: 20px; text-align: center;
}
.nav-item.active .nav-badge { background: var(--cyan-dim); color: var(--cyan); border-color: rgba(56,189,248,.3); }

.sidebar-footer {
  padding: 14px 16px;
  border-top: 1px solid var(--border);
  font-family: var(--mono); font-size: 9px; color: var(--text3);
  line-height: 1.6;
}
.live-dot {
  display: inline-block; width: 6px; height: 6px; border-radius: 50%;
  background: var(--green); margin-right: 5px; vertical-align: middle;
  box-shadow: 0 0 0 0 rgba(74,222,128,.4);
  animation: heartbeat 2s ease-in-out infinite;
}
@keyframes heartbeat {
  0%   { box-shadow: 0 0 0 0   rgba(74,222,128,.5); }
  40%  { box-shadow: 0 0 0 5px rgba(74,222,128,.0); }
  100% { box-shadow: 0 0 0 0   rgba(74,222,128,.0); }
}

/* ── App Shell ─────────────────────────────────────────────────────────── */
.app-shell {
  margin-left: var(--sidebar-w);
  flex: 1; display: flex; flex-direction: column;
  min-height: 100vh; position: relative; z-index: 1;
}

/* ── Header ───────────────────────────────────────────────────────────── */
.hdr {
  position: sticky; top: 0; z-index: 200;
  background: rgba(7,9,15,.92); backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 28px; height: 50px;
}
.hdr-left { display: flex; align-items: center; gap: 10px; }
.hdr-page-title {
  font-family: var(--mono); font-size: 12px; font-weight: 600;
  color: var(--text); letter-spacing: .04em;
}
.hdr-page-sub { font-family: var(--mono); font-size: 10px; color: var(--text3); }
.hdr-sep { color: var(--border2); margin: 0 4px; }
.hdr-right { display: flex; align-items: center; gap: 16px; }
#last-update { font-family: var(--mono); font-size: 10px; color: var(--text3); }

/* ── Stats Bar ─────────────────────────────────────────────────────────── */
.stats-bar {
  display: grid; grid-template-columns: repeat(9, 1fr);
  gap: 1px; background: var(--border);
  border-bottom: 1px solid var(--border);
}
@media (max-width: 1400px) { .stats-bar { grid-template-columns: repeat(5, 1fr); } }

.stat {
  background: var(--s1); padding: 12px 16px;
  position: relative; overflow: hidden;
  transition: background .15s; cursor: default;
}
.stat:hover { background: var(--s2); }
.stat::after {
  content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 1px;
  background: linear-gradient(90deg, transparent, var(--accent, var(--cyan)), transparent);
  opacity: .4;
}
.stat:nth-child(1)  { --accent: var(--cyan); }
.stat:nth-child(2)  { --accent: var(--green); }
.stat:nth-child(3)  { --accent: var(--amber); }
.stat:nth-child(4)  { --accent: var(--red); }
.stat:nth-child(5)  { --accent: var(--violet); }
.stat:nth-child(6)  { --accent: #34d399; }
.stat:nth-child(7)  { --accent: var(--red); }
.stat:nth-child(8)  { --accent: var(--green); }
.stat:nth-child(9)  { --accent: var(--amber); }

.stat-lbl {
  font-family: var(--mono); font-size: 8px; font-weight: 500;
  letter-spacing: .1em; text-transform: uppercase;
  color: var(--text3); margin-bottom: 6px; white-space: nowrap;
}
.stat-val {
  font-family: var(--mono); font-size: 22px; font-weight: 600;
  color: var(--text); line-height: 1; transition: color .3s;
}
.stat-val.updated { color: var(--accent, var(--cyan)); }

/* ── Page Content ─────────────────────────────────────────────────────── */
.page-content { padding: 22px 26px; flex: 1; }

/* ── Panel ────────────────────────────────────────────────────────────── */
.panel {
  background: var(--s1); border: 1px solid var(--border);
  border-radius: var(--r); overflow: hidden;
}
.panel-hdr {
  display: flex; align-items: center; justify-content: space-between;
  padding: 13px 18px; border-bottom: 1px solid var(--border);
  flex-wrap: wrap; gap: 10px;
}
.panel-title {
  font-family: var(--mono); font-size: 11px; font-weight: 600;
  letter-spacing: .08em; text-transform: uppercase;
  color: var(--cyan); display: flex; align-items: center; gap: 8px;
}
.row-count {
  font-family: var(--mono); font-size: 10px;
  color: var(--text3); background: var(--s2);
  padding: 2px 8px; border-radius: 20px; border: 1px solid var(--border);
}

/* ── Toolbar ──────────────────────────────────────────────────────────── */
.toolbar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.inp {
  background: var(--s2); border: 1px solid var(--border);
  color: var(--text); font-family: var(--mono); font-size: 11px;
  padding: 6px 11px; border-radius: var(--r); outline: none;
  transition: border-color .15s, width .2s;
}
.inp:focus { border-color: rgba(56,189,248,.4); }
.inp::placeholder { color: var(--text3); }
.inp-search { width: 190px; }
.inp-search:focus { width: 230px; }

.sel {
  background: var(--s2); border: 1px solid var(--border);
  color: var(--text2); font-family: var(--mono); font-size: 11px;
  padding: 6px 11px; border-radius: var(--r); outline: none;
  cursor: pointer; transition: border-color .15s;
}
.sel:focus { border-color: rgba(56,189,248,.4); }

.btn {
  font-family: var(--mono); font-size: 10px; font-weight: 600;
  letter-spacing: .05em; padding: 6px 12px; border-radius: var(--r);
  border: 1px solid; cursor: pointer; transition: all .15s;
  display: inline-flex; align-items: center; gap: 5px; white-space: nowrap;
}
.btn:disabled { opacity: .4; cursor: default; }
.btn-ghost  { background: transparent; border-color: var(--border); color: var(--text2); }
.btn-ghost:hover:not(:disabled) { border-color: var(--border2); color: var(--text); background: var(--s2); }
.btn-cyan   { background: var(--cyan-dim); border-color: rgba(56,189,248,.3); color: var(--cyan); }
.btn-cyan:hover:not(:disabled)  { background: rgba(56,189,248,.2); }
.btn-green  { background: var(--green-dim); border-color: rgba(74,222,128,.3); color: var(--green); }
.btn-green:hover:not(:disabled) { background: rgba(74,222,128,.2); }
.btn-red    { background: var(--red-dim);   border-color: rgba(248,113,113,.3); color: var(--red); }
.btn-red:hover:not(:disabled)   { background: rgba(248,113,113,.2); }
.btn-amber  { background: var(--amber-dim); border-color: rgba(251,191,36,.3); color: var(--amber); }
.btn-amber:hover:not(:disabled) { background: rgba(251,191,36,.2); }
.btn-sm     { padding: 3px 9px; font-size: 9px; }
.btn-export { background: transparent; border-color: var(--border); color: var(--text3); font-size: 9px; padding: 5px 10px; }
.btn-export:hover { border-color: var(--amber); color: var(--amber); }

/* ── Add form ─────────────────────────────────────────────────────────── */
.add-bar {
  display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
  padding: 10px 18px; border-bottom: 1px solid var(--border);
  background: rgba(56,189,248,.02);
}
.add-lbl { font-family: var(--mono); font-size: 9px; color: var(--text3); letter-spacing: .1em; text-transform: uppercase; }
.inp-sm  { padding: 5px 10px; font-size: 11px; }

/* ── Table ────────────────────────────────────────────────────────────── */
.tbl-wrap { overflow-x: auto; }
.tbl-scroll {
  max-height: 560px; overflow-y: auto;
  scrollbar-width: thin; scrollbar-color: var(--border2) transparent;
}
.tbl-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
.tbl-scroll::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px; }

table { width: 100%; border-collapse: collapse; font-size: 12px; }

thead { position: sticky; top: 0; z-index: 10; }
thead tr { background: var(--s2); }
th {
  padding: 9px 16px; text-align: left;
  font-family: var(--mono); font-size: 9px; font-weight: 500;
  letter-spacing: .08em; text-transform: uppercase; color: var(--text3);
  white-space: nowrap; border-bottom: 1px solid var(--border);
  user-select: none;
}
td {
  padding: 9px 16px; border-bottom: 1px solid var(--border);
  vertical-align: middle; white-space: nowrap;
}
tr:last-child td { border-bottom: none; }
tbody tr { transition: background .1s; }
tbody tr:hover { background: var(--s2); }
tbody tr.row-blocked { background: rgba(248,113,113,.03); }
tbody tr.row-blocked:hover { background: rgba(248,113,113,.06); }
tbody tr.row-wl { background: rgba(74,222,128,.02); }

/* ── Badges ───────────────────────────────────────────────────────────── */
.badge {
  display: inline-flex; align-items: center;
  padding: 2px 7px; border-radius: 3px;
  font-family: var(--mono); font-size: 9px; font-weight: 600;
  letter-spacing: .08em; text-transform: uppercase; border: 1px solid;
  white-space: nowrap;
}
.b-green   { background: var(--green-dim); color: var(--green); border-color: rgba(74,222,128,.25); }
.b-red     { background: var(--red-dim);   color: var(--red);   border-color: rgba(248,113,113,.25); }
.b-amber   { background: var(--amber-dim); color: var(--amber); border-color: rgba(251,191,36,.25); }
.b-cyan    { background: var(--cyan-dim);  color: var(--cyan);  border-color: rgba(56,189,248,.25); }
.b-violet  { background: rgba(167,139,250,.12); color: var(--violet); border-color: rgba(167,139,250,.25); }
.b-muted   { background: rgba(255,255,255,.04); color: var(--text3); border-color: var(--border); }
.b-perm    { background: rgba(248,113,113,.2); color: var(--red); border-color: rgba(248,113,113,.4); font-size: 8px; }

/* ── Cell helpers ──────────────────────────────────────────────────────── */
.mono-sm { font-family: var(--mono); font-size: 11px; color: var(--text2); }
.dim     { color: var(--text3); font-size: 11px; }
.trunc   { max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.fp-cell { font-family: var(--mono); font-size: 10px; color: var(--text3); max-width: 130px; overflow: hidden; text-overflow: ellipsis; }
.user-cell strong { color: var(--text); font-weight: 500; }

.risk { display: flex; align-items: center; gap: 7px; }
.risk-bar { width: 48px; height: 3px; background: var(--s3); border-radius: 2px; overflow: hidden; flex-shrink: 0; }
.risk-fill { height: 100%; border-radius: 2px; transition: width .3s; }
.risk-num  { font-family: var(--mono); font-size: 11px; font-weight: 600; min-width: 22px; }

.flags { display: flex; flex-wrap: wrap; gap: 3px; max-width: 180px; }
.flag  { font-family: var(--mono); font-size: 9px; padding: 1px 5px; border-radius: 2px; background: rgba(251,191,36,.08); color: var(--amber); border: 1px solid rgba(251,191,36,.15); }
.otp-tok { font-family: var(--mono); font-size: 10px; letter-spacing: .1em; color: var(--amber); }

/* ── Infinite scroll ──────────────────────────────────────────────────── */
.sentinel { height: 1px; }
.state-row td { text-align: center; padding: 48px 16px; color: var(--text3); font-family: var(--mono); font-size: 11px; }
.spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid var(--border2); border-top-color: var(--cyan); border-radius: 50%; animation: spin .7s linear infinite; vertical-align: middle; margin-right: 8px; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Toast ────────────────────────────────────────────────────────────── */
.toast {
  position: fixed; bottom: 20px; right: 20px; z-index: 9999;
  padding: 11px 18px; border-radius: var(--r);
  font-family: var(--mono); font-size: 11px; font-weight: 500;
  opacity: 0; transform: translateY(6px);
  transition: all .25s cubic-bezier(.34,1.56,.64,1);
  pointer-events: none; max-width: 380px;
  display: flex; align-items: center; gap: 8px;
}
.toast.show { opacity: 1; transform: translateY(0); }
.toast-ok  { background: #0a1f12; border: 1px solid rgba(74,222,128,.35);  color: var(--green); }
.toast-err { background: #1f0a0a; border: 1px solid rgba(248,113,113,.35); color: var(--red);   }

/* ── Modal ────────────────────────────────────────────────────────────── */
.overlay {
  position: fixed; inset: 0; background: rgba(7,9,15,.8);
  backdrop-filter: blur(4px); z-index: 500;
  display: flex; align-items: center; justify-content: center;
  opacity: 0; pointer-events: none; transition: opacity .2s;
}
.overlay.open { opacity: 1; pointer-events: all; }
.modal {
  background: var(--s2); border: 1px solid var(--border2);
  border-radius: 10px; padding: 28px 32px; max-width: 420px; width: 90%;
  transform: scale(.95); transition: transform .2s;
}
.overlay.open .modal { transform: scale(1); }
.modal-title { font-family: var(--mono); font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 10px; }
.modal-body  { color: var(--text2); font-size: 12px; margin-bottom: 20px; line-height: 1.6; }
.modal-actions { display: flex; gap: 8px; justify-content: flex-end; }
.modal-inp { width: 100%; margin-bottom: 12px; padding: 7px 12px; }

/* ── Scrollbar ────────────────────────────────────────────────────────── */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px; }
</style>
