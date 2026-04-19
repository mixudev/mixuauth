# ============================================================
# Stage 1: Builder — Install deps & build VitePress static site
# ============================================================
FROM node:20-alpine AS builder

WORKDIR /app

# Copy package files dulu untuk memanfaatkan layer cache Docker
COPY docs/package.json docs/package-lock.json* ./

# Install dependencies
RUN npm ci --prefer-offline 2>/dev/null || npm install

# Copy seluruh source docs
COPY docs/ .

# Build static site
RUN npm run build

# ============================================================
# Stage 2: Serve — Nginx Alpine ringan untuk static files
# ============================================================
FROM nginx:1.27-alpine AS serve

# Hapus konfigurasi default Nginx
RUN rm -rf /usr/share/nginx/html/* /etc/nginx/conf.d/default.conf

# Copy hasil build ke Nginx
COPY --from=builder /app/.vitepress/dist /usr/share/nginx/html

# Konfigurasi Nginx untuk SPA (Single Page App) routing VitePress
RUN printf 'server {\n\
    listen 80;\n\
    root /usr/share/nginx/html;\n\
    index index.html;\n\
    charset utf-8;\n\
\n\
    # Serve precompressed files\n\
    gzip_static on;\n\
\n\
    location / {\n\
        try_files $uri $uri/ $uri.html /index.html;\n\
    }\n\
\n\
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {\n\
        expires 1y;\n\
        add_header Cache-Control "public, immutable";\n\
        access_log off;\n\
    }\n\
\n\
    # Security headers\n\
    add_header X-Frame-Options "SAMEORIGIN";\n\
    add_header X-Content-Type-Options "nosniff";\n\
    add_header X-XSS-Protection "1; mode=block";\n\
}\n' > /etc/nginx/conf.d/docs.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
