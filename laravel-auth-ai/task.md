# Task: Production Hardening MixuAuth (AUDIT-01)

## Kelompok 1 — CRITICAL: Endpoint WA + Secret Leak
- [/] 1.1 Hapus token hardcoded dari `wa_gateway.php` → gunakan env()
- [ ] 1.2 Model `WaGatewayConfig` → encrypted cast + hidden token
- [ ] 1.3 Controller: hapus `persistModuleConfig()`, fix `getLatestLogs()` (exclude token)
- [ ] 1.4 `.env.example` → ganti password real dengan placeholder
- [ ] 1.5 `routes/api.php` → tambah auth:sanctum + throttle ke WA route

## Kelompok 2 — HIGH: DOM/Stored XSS
- [ ] 2.1 `app-dashboard.blade.php` → ganti innerHTML notif dropdown dengan DOM builder
- [ ] 2.2 `command-palette.js` → escape item.title & item.category di createItemEl()
- [ ] 2.3 Audit & fix sink tambahan (app-popup, device/index, modals)

## Kelompok 3 — MEDIUM: Deploy Readiness
- [ ] 3.1 `Identity/routes/web.php` → tambah import GlobalSearchController
- [ ] 3.2 `GlobalSearchController.php` → fix namespace User model
- [ ] 3.3 `emails/otp-text.blade.php` → fix nama komponen x-email.base-text

## Kelompok 4 — MEDIUM: RBAC WA
- [ ] 4.1 `WaGateway/routes/web.php` → tambah permission ke template routes
- [ ] 4.2 Buat `WaGatewayConfigPolicy.php`
- [ ] 4.3 `StoreWaGatewayConfigRequest` → ganti return true dengan auth check

## Kelompok 5 — MEDIUM: Information Disclosure
- [ ] 5.1 `WaGatewayConfigController::index()` → scope stats per-user + optimalkan hourly query

## Kelompok 6 — MEDIUM: Security Headers
- [ ] 6.1 `SecurityHeadersMiddleware` → tambah CDN ke script-src + HSTS
- [ ] 6.2 `docker/nginx/default.conf` → tambah security headers

## Kelompok 7 — MEDIUM: Migrasi + Health Endpoint
- [ ] 7.1 Buat migrasi additive baru (ganti pendekatan drop-recreate)
- [ ] 7.2 `Security/routes/web.php` → tambah permission ke health endpoint

## Kelompok 8 — LOW: Namespace + Deps
- [ ] 8.1 `PreAuthRateLimitMiddleware` → fix namespace job SendWhatsAppNotification
- [ ] 8.2 `composer.json` → pin dependency version
