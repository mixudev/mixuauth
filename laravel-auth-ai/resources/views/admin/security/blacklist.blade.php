<div class="panel">
  <div class="panel-hdr">
    <div class="panel-title">
      🚫 IP Blacklist
      <span class="row-count" id="cnt-blacklist">0</span>
    </div>
    <div class="toolbar">
      <input class="inp inp-search" id="f-bl-q" placeholder="Search IP / reason…" oninput="debounce('blacklist')">
      <button class="btn btn-ghost" onclick="resetLoad('blacklist')">↻ Refresh</button>
    </div>
  </div>

  <div class="add-bar">
    <span class="add-lbl">Add IP →</span>
    <input class="inp inp-sm" id="bl-ip"      placeholder="IP Address"           style="width:155px">
    <input class="inp inp-sm" id="bl-reason"  placeholder="Reason (optional)"    style="width:190px">
    <input class="inp inp-sm" id="bl-minutes" placeholder="Minutes (blank=perm)" style="width:170px" type="number" min="1">
    <button class="btn btn-red btn-sm" onclick="addBlacklist()">+ Blacklist</button>
  </div>

  <div class="tbl-wrap">
    <div class="tbl-scroll" id="sc-blacklist">
      <table>
        <thead>
          <tr>
            <th>#</th><th>IP Address</th><th>Reason</th><th>By</th><th>Count</th>
            <th>Blocked Until</th><th>Blocked At</th><th>Status</th><th>Action</th>
          </tr>
        </thead>
        <tbody id="tb-blacklist">
          <tr class="state-row"><td colspan="9"><span class="spinner"></span>Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
