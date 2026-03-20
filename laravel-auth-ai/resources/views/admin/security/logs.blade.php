<div class="panel">
  <div class="panel-hdr">
    <div class="panel-title">
      📋 Login Logs
      <span class="row-count" id="cnt-logs">0</span>
    </div>
    <div class="toolbar">
      <select class="sel" id="f-logs-status" onchange="resetLoad('logs')">
        <option value="">All Status</option>
        <option value="success">Success</option>
        <option value="otp_required">OTP Required</option>
        <option value="blocked">Blocked</option>
        <option value="failed">Failed</option>
      </select>
      <select class="sel" id="f-logs-decision" onchange="resetLoad('logs')">
        <option value="">All Decision</option>
        <option value="allow">ALLOW</option>
        <option value="otp">OTP</option>
        <option value="block">BLOCK</option>
      </select>
      <input class="inp inp-search" id="f-logs-q" placeholder="Search user / IP…" oninput="debounce('logs')">
      <button class="btn btn-export btn-sm" onclick="exportCSV()">↓ Export CSV</button>
      <button class="btn btn-ghost" onclick="resetLoad('logs')">↻ Refresh</button>
    </div>
  </div>

  <div class="tbl-wrap">
    <div class="tbl-scroll" id="sc-logs">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Time</th><th>User</th><th>Email</th><th>IP</th>
            <th>Status</th><th>Decision</th><th>Risk</th><th>Flags</th><th>Fingerprint</th>
          </tr>
        </thead>
        <tbody id="tb-logs">
          <tr class="state-row"><td colspan="10"><span class="spinner"></span>Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
