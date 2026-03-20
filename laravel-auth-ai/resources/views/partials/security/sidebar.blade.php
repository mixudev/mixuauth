<aside class="sidebar">

  <div class="sidebar-logo">
    <div class="logo-mark">
      <div class="logo-icon">🔐</div>
      <div>
        <div class="logo-text">AUTH MONITOR</div>
        <div class="logo-sub">Security Dashboard</div>
      </div>
    </div>
    <span class="dev-tag">⚠ DEV ONLY</span>
  </div>

  <nav class="sidebar-nav">

    <div class="nav-section-label">Monitoring</div>

    <div class="nav-item active" id="nav-otp" onclick="navigateTo('otp')">
      <span class="nav-icon">🔐</span>
      <span class="nav-label">OTP Verifications</span>
      <span class="nav-badge" id="nb-otp">—</span>
    </div>

    <div class="nav-item" id="nav-logs" onclick="navigateTo('logs')">
      <span class="nav-icon">📋</span>
      <span class="nav-label">Login Logs</span>
      <span class="nav-badge" id="nb-logs">—</span>
    </div>

    <div class="nav-item" id="nav-devices" onclick="navigateTo('devices')">
      <span class="nav-icon">💻</span>
      <span class="nav-label">Trusted Devices</span>
      <span class="nav-badge" id="nb-devices">—</span>
    </div>

    <div class="nav-item" id="nav-users" onclick="navigateTo('users')">
      <span class="nav-icon">👥</span>
      <span class="nav-label">Users</span>
      <span class="nav-badge" id="nb-users">—</span>
    </div>

    <div class="nav-section-label" style="margin-top:4px">IP Management</div>

    <div class="nav-item" id="nav-blacklist" onclick="navigateTo('blacklist')">
      <span class="nav-icon">🚫</span>
      <span class="nav-label">IP Blacklist</span>
      <span class="nav-badge" id="nb-blacklist">—</span>
    </div>

    <div class="nav-item" id="nav-whitelist" onclick="navigateTo('whitelist')">
      <span class="nav-icon">✅</span>
      <span class="nav-label">IP Whitelist</span>
      <span class="nav-badge" id="nb-whitelist">—</span>
    </div>

  </nav>

  <div class="sidebar-footer">
    <span class="live-dot"></span>
    Live — refresh 10s<br>
    <span id="last-update-sidebar" style="color:var(--text3)">—</span>
  </div>

</aside>
