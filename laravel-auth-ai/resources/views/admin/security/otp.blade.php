<div class="panel">
  <div class="panel-hdr">
    <div class="panel-title">
      🔐 OTP Verifications
      <span class="row-count" id="cnt-otp">0</span>
    </div>
    <div class="toolbar">
      <select class="sel" id="f-otp-status" onchange="resetLoad('otp')">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="verified">Verified</option>
        <option value="expired">Expired</option>
      </select>
      <input class="inp inp-search" id="f-otp-q" placeholder="Search user / email…" oninput="debounce('otp')">
      <button class="btn btn-ghost" onclick="resetLoad('otp')">↻ Refresh</button>
    </div>
  </div>

  <div class="tbl-wrap">
    <div class="tbl-scroll" id="sc-otp">
      <table>
        <thead>
          <tr>
            <th>#</th><th>User</th><th>Email</th><th>Token</th>
            <th>Status</th><th>Attempts</th><th>Expires At</th>
            <th>Verified At</th><th>Created</th>
          </tr>
        </thead>
        <tbody id="tb-otp">
          <tr class="state-row"><td colspan="9"><span class="spinner"></span>Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
