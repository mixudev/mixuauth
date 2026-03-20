<div class="panel">
  <div class="panel-hdr">
    <div class="panel-title">
      💻 Trusted Devices
      <span class="row-count" id="cnt-devices">0</span>
    </div>
    <div class="toolbar">
      <select class="sel" id="f-dev-status" onchange="resetLoad('devices')">
        <option value="">All</option>
        <option value="trusted">Trusted</option>
        <option value="revoked">Revoked</option>
      </select>
      <input class="inp inp-search" id="f-dev-q" placeholder="Search user / IP…" oninput="debounce('devices')">
      <button class="btn btn-ghost" onclick="resetLoad('devices')">↻ Refresh</button>
    </div>
  </div>

  <div class="tbl-wrap">
    <div class="tbl-scroll" id="sc-devices">
      <table>
        <thead>
          <tr>
            <th>#</th><th>User</th><th>Email</th><th>Label</th><th>Fingerprint</th>
            <th>IP</th><th>Status</th><th>Last Seen</th><th>Trusted Until</th><th>Action</th>
          </tr>
        </thead>
        <tbody id="tb-devices">
          <tr class="state-row"><td colspan="10"><span class="spinner"></span>Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
