<div class="panel">
  <div class="panel-hdr">
    <div class="panel-title">
      ✅ IP Whitelist
      <span class="row-count" id="cnt-whitelist">0</span>
    </div>
    <div class="toolbar">
      <input class="inp inp-search" id="f-wl-q" placeholder="Search IP / label…" oninput="debounce('whitelist')">
      <button class="btn btn-ghost" onclick="resetLoad('whitelist')">↻ Refresh</button>
    </div>
  </div>

  <div class="add-bar">
    <span class="add-lbl">Add IP →</span>
    <input class="inp inp-sm" id="wl-ip"    placeholder="IP Address"           style="width:155px">
    <input class="inp inp-sm" id="wl-label" placeholder="Label (e.g. Office)"  style="width:220px">
    <button class="btn btn-green btn-sm" onclick="addWhitelist()">+ Whitelist</button>
  </div>

  <div class="tbl-wrap">
    <div class="tbl-scroll" id="sc-whitelist">
      <table>
        <thead>
          <tr>
            <th>#</th><th>IP Address</th><th>Label</th><th>Added By</th>
            <th>Created</th><th>Action</th>
          </tr>
        </thead>
        <tbody id="tb-whitelist">
          <tr class="state-row"><td colspan="6"><span class="spinner"></span>Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
