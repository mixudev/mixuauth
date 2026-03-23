#!/bin/bash
# ============================================================
# Script setup lengkap untuk pertama kali menjalankan sistem.
# Jalankan dari direktori root (secure-system/).
#
# Cara pakai:
#   chmod +x setup.sh
#   ./setup.sh
# ============================================================

set -e  # Hentikan jika ada perintah yang gagal

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info()    { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[OK]${NC} $1"; }
log_warn()    { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error()   { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

echo ""
echo "=============================================="
echo "  Secure Auth System — Setup Awal "
echo "  !!! Pastikan koneksi internet lancar !!!"
echo "=============================================="
echo ""

# ----------------------------------------------------------
# Periksa dependencies yang diperlukan
# ----------------------------------------------------------
log_info "Memeriksa dependencies..."

command -v docker      >/dev/null 2>&1 || log_error "Docker tidak ditemukan. Install dari https://docker.com"
command -v docker-compose >/dev/null 2>&1 || command -v "docker compose" >/dev/null 2>&1 || log_error "Docker Compose tidak ditemukan."

log_success "Docker tersedia."

# ----------------------------------------------------------
# Periksa file .env ada
# ----------------------------------------------------------
log_info "Memeriksa file konfigurasi..."

if [ ! -f "laravel-auth-ai/.env" ]; then
    log_warn "File laravel-auth-ai/.env tidak ditemukan. Menyalin dari .env.example..."
    cp laravel-auth-ai/.env.example laravel-auth-ai/.env
fi

if [ ! -f "ai-security/.env" ]; then
    log_warn "File ai-security/.env tidak ditemukan. Menyalin dari .env.example..."
    cp ai-security/.env.example ai-security/.env
fi

log_success "File konfigurasi siap."

# ----------------------------------------------------------
# Buat direktori yang diperlukan
# ----------------------------------------------------------
log_info "Membuat direktori..."
mkdir -p docker/nginx
mkdir -p ai-security/app/models
mkdir -p ai-security/logs

# Laravel Storage subdirectories
mkdir -p laravel-auth-ai/storage/framework/sessions
mkdir -p laravel-auth-ai/storage/framework/views
mkdir -p laravel-auth-ai/storage/framework/cache
mkdir -p laravel-auth-ai/storage/logs
chmod -R 777 laravel-auth-ai/storage

log_success "Direktori siap."

# ----------------------------------------------------------
# Build dan jalankan containers
# ----------------------------------------------------------
log_info "Membangun Docker images (ini mungkin memakan waktu beberapa menit)..."
docker compose build --no-cache

log_info "Menjalankan containers..."
docker compose up -d db redis

log_info "Menunggu database siap..."
sleep 15

# ----------------------------------------------------------
# Setup Laravel
# ----------------------------------------------------------
log_info "Menyiapkan direktori storage di dalam container..."
docker compose run --rm -u root app mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs
docker compose run --rm -u root app chown -R www-data:www-data storage

log_info "Instal dependensi PHP (composer)..."
docker compose run --rm -u root app sh -c "composer install --no-interaction --optimize-autoloader && chown -R www-data:www-data vendor"

log_info "Generate Laravel APP_KEY..."
docker compose run --rm app php artisan key:generate --no-interaction

log_info "Generate dan sinkronisasi AI API Key..."
docker compose run --rm app php artisan ai:generate-key --no-interaction

log_info "Menjalankan database migration..."
docker compose run --rm app php artisan migrate --no-interaction --force

log_info "Meng-cache konfigurasi Laravel..."
docker compose run --rm app php artisan config:cache
docker compose run --rm app php artisan route:cache

log_success "Laravel setup selesai."

# ----------------------------------------------------------
# Jalankan semua services
# ----------------------------------------------------------
log_info "Menjalankan semua services..."
docker compose up -d

log_info "Menunggu semua services siap..."
sleep 10

# ----------------------------------------------------------
# Verifikasi
# ----------------------------------------------------------
log_info "Memverifikasi sistem..."

# Cek Laravel
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/api/auth/login \
    -X POST -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","password":"wrongpassword"}' 2>/dev/null || echo "000")

if [ "$HTTP_CODE" = "422" ] || [ "$HTTP_CODE" = "401" ]; then
    log_success "Laravel API berjalan (HTTP $HTTP_CODE)"
else
    log_warn "Laravel mungkin belum siap (HTTP $HTTP_CODE). Cek: docker compose logs app"
fi

# Cek FastAPI
FASTAPI_STATUS=$(curl -s http://localhost:8000/health 2>/dev/null | grep -o '"status":"ok"' || echo "")
if [ -n "$FASTAPI_STATUS" ]; then
    log_success "FastAPI berjalan."
else
    log_warn "FastAPI belum merespons. Cek: docker compose logs fastapi-risk"
fi

# ----------------------------------------------------------
# Selesai
# ----------------------------------------------------------
echo ""
echo "=============================================="
echo -e "${GREEN}  Setup selesai!${NC}"
echo "=============================================="
echo ""
echo "  Laravel API  : http://localhost:8080/api"
echo "  FastAPI      : http://localhost:8000/health"
echo "  FastAPI Docs : http://localhost:8000/docs (dev only)"
echo ""
echo "  Untuk melihat log:"
echo "    docker compose logs -f app"
echo "    docker compose logs -f fastapi-risk"
echo "    docker compose logs -f worker"
echo ""
echo "  Untuk menghentikan:"
echo "    docker compose down"
echo ""
log_warn "Jangan lupa atur SMTP di laravel-auth-ai/.env untuk OTP!"
echo ""
