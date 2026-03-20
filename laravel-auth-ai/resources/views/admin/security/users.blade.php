<div class="panel">
  <div class="panel-hdr">
    <div class="panel-title">
      👥 Users
      <span class="row-count" id="cnt-users">0</span>
    </div>
    <div class="toolbar">
      <select class="sel" id="f-usr-status" onchange="resetLoad('users')">
        <option value="">All Status</option>
        <option value="blocked">Blocked</option>
        <option value="ok">OK</option>
      </select>
      <input class="inp inp-search" id="f-usr-q" placeholder="Search name / email…" oninput="debounce('users')">
      <button class="btn btn-ghost" onclick="resetLoad('users')">↻ Refresh</button>
    </div>
  </div>

  <div class="tbl-wrap">
    <div class="tbl-scroll" id="sc-users">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Name</th><th>Email</th><th>Active</th><th>Verified</th>
            <th>Last Login</th><th>Last IP</th><th>Logs</th><th>Devices</th>
            <th>Created</th><th>Status</th><th>Action</th>
          </tr>
        </thead>
        <tbody id="tb-users">
          <tr class="state-row"><td colspan="12"><span class="spinner"></span>Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
