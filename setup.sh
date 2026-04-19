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
generate_random_string() {
    cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w "${1:-32}" | head -n 1
}


echo ""
echo "=============================================="
echo "  Secure Auth System — Setup Awal "
echo "  !!! Pastikan koneksi internet lancar !!!"
echo "=============================================="
echo ""

# ----------------------------------------------------------
# Periksa dependencies yang diperlukan
# ----------------------------------------------------------
log_info "Memeriksa koneksi internet..."
if ! curl -s --head --request GET http://www.google.com | grep "200 OK" > /dev/null; then
    log_error "Koneksi internet tidak terdeteksi! Pastikan Anda terhubung ke internet untuk mendownload library dan Docker images."
fi
log_success "Koneksi internet aktif."

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

if [ ! -f ".env" ]; then
    log_warn "File root .env tidak ditemukan. Menyalin dari .env.example..."
    cp .env.example .env
fi

log_success "File konfigurasi siap."

# ----------------------------------------------------------
# Konfigurasi Keamanan Otomatis
# ----------------------------------------------------------
log_info "Mengamankan kredensial sistem..."

# Pastikan file .env ada sebelum di-sed
[ -f ".env" ] || touch .env
[ -f "laravel-auth-ai/.env" ] || touch laravel-auth-ai/.env

# 1. REDIS_PASSWORD
CURRENT_REDIS_PWD=$(grep "^REDIS_PASSWORD=" .env | cut -d'=' -f2-)
if [ -z "$CURRENT_REDIS_PWD" ] || [ "$CURRENT_REDIS_PWD" = "" ]; then
    log_info "Generating new REDIS_PASSWORD..."
    NEW_REDIS_PWD=$(generate_random_string 32)
    
    # Update root .env
    if grep -q "^REDIS_PASSWORD=" .env; then
        sed -i "s|^REDIS_PASSWORD=.*|REDIS_PASSWORD=$NEW_REDIS_PWD|" .env
    else
        echo "REDIS_PASSWORD=$NEW_REDIS_PWD" >> .env
    fi
    
    # Update Laravel .env
    if grep -q "^REDIS_PASSWORD=" laravel-auth-ai/.env; then
        sed -i "s|^REDIS_PASSWORD=.*|REDIS_PASSWORD=$NEW_REDIS_PWD|" laravel-auth-ai/.env
    else
        echo "REDIS_PASSWORD=$NEW_REDIS_PWD" >> laravel-auth-ai/.env
    fi
fi

# 2. MYSQL_ROOT_PASSWORD
CURRENT_MYSQL_ROOT=$(grep "^MYSQL_ROOT_PASSWORD=" .env | cut -d'=' -f2-)
if [ -z "$CURRENT_MYSQL_ROOT" ] || [ "$CURRENT_MYSQL_ROOT" = "root_secure_9283_password_!" ] || [ "$CURRENT_MYSQL_ROOT" = "ZQ!8pV@r6FJxkNwD7m2C" ]; then
    log_info "Generating new MYSQL_ROOT_PASSWORD..."
    NEW_MYSQL_ROOT=$(generate_random_string 32)
    sed -i "s|^MYSQL_ROOT_PASSWORD=.*|MYSQL_ROOT_PASSWORD=$NEW_MYSQL_ROOT|" .env
    
    # Laravel .env might have it with a typo or correct name
    sed -i "s|^MYSQL_ROOT_PASSWORD=.*|MYSQL_ROOT_PASSWORD=$NEW_MYSQL_ROOT|" laravel-auth-ai/.env
    sed -i "s|^MSQL_ROOT_PASSWORD=.*|MYSQL_ROOT_PASSWORD=$NEW_MYSQL_ROOT|" laravel-auth-ai/.env
fi

# 3. DB_PASSWORD
CURRENT_DB_PWD=$(grep "^DB_PASSWORD=" laravel-auth-ai/.env | cut -d'=' -f2-)
if [ -z "$CURRENT_DB_PWD" ] || [ "$CURRENT_DB_PWD" = "secret123" ] || [ "$CURRENT_DB_PWD" = "W9sLeP7T@x4RkM!2cHf" ] || [ "$CURRENT_DB_PWD" = "app_secure_7261_password_fsaA" ]; then
    log_info "Generating new DB_PASSWORD..."
    NEW_DB_PWD=$(generate_random_string 32)
    sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$NEW_DB_PWD|" .env
    sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$NEW_DB_PWD|" laravel-auth-ai/.env
fi

log_success "Kredensial keamanan telah diperbarui."


# ----------------------------------------------------------
# Buat direktori yang diperlukan
# ----------------------------------------------------------
log_info "Membuat direktori..."
mkdir -p docker/nginx
mkdir -p docker/laravel
mkdir -p docker/docs
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
# Menggunakan --ignore-platform-reqs agar tetap bisa jalan meskipun ada mismatch versi PHP minor antara lock file dan environment
docker compose run --rm -u root app sh -c "composer install --no-interaction --optimize-autoloader --ignore-platform-reqs && chown -R www-data:www-data vendor"

log_info "Membersihkan file-file legacy..."
docker compose run --rm -u root app rm -f app/Services/User/UserServiceold.php

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
sleep 15


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
echo "  Laravel App  : http://localhost:8080"
echo "  Laravel API  : http://localhost:8080/api"
echo "  FastAPI      : http://localhost:8000/health"
echo "  phpMyAdmin   : http://localhost:8081"
echo "  Dokumentasi  : http://localhost:8090"
echo ""
echo "  Untuk melihat log:"
echo "    docker compose logs -f app"
echo "    docker compose logs -f fastapi-risk"
echo "    docker compose logs -f worker"
echo "    docker compose logs -f docs"
echo ""
echo "  Untuk menghentikan:"
echo "    docker compose down"
echo ""
log_warn "Jangan lupa atur SMTP di laravel-auth-ai/.env untuk OTP!"
echo ""
