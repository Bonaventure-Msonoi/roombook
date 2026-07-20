#!/bin/bash
# Run this script on a fresh Ubuntu 22.04/24.04 Oracle Cloud VM (as ubuntu user with sudo).
set -euo pipefail

APP_DIR="${APP_DIR:-$HOME/roombook}"
REPO_URL="${REPO_URL:-}"

echo "==> Installing Docker..."
sudo apt-get update
sudo apt-get install -y ca-certificates curl git
if ! command -v docker >/dev/null 2>&1; then
    curl -fsSL https://get.docker.com | sudo sh
fi
sudo usermod -aG docker "$USER" || true

echo "==> Preparing app directory: $APP_DIR"
mkdir -p "$APP_DIR"

if [ -n "$REPO_URL" ]; then
    if [ ! -d "$APP_DIR/.git" ]; then
        git clone "$REPO_URL" "$APP_DIR"
    else
        git -C "$APP_DIR" pull
    fi
else
    echo "No REPO_URL set. Copy your project into: $APP_DIR"
fi

cd "$APP_DIR"

if [ ! -f .env ]; then
    cp .env.oracle.example .env
    php -r "echo 'APP_KEY=base64:'.base64_encode(random_bytes(32)).PHP_EOL;" >> .env.tmp
    # Replace empty APP_KEY line
    grep -v '^APP_KEY=' .env > .env.new
    cat .env.tmp >> .env.new
    mv .env.new .env
    rm -f .env.tmp
    echo ""
    echo "Created .env from .env.oracle.example"
    echo "IMPORTANT: edit .env and set:"
    echo "  - APP_URL (your VM public IP or domain)"
    echo "  - DB_PASSWORD"
    echo "  - DB_ROOT_PASSWORD"
    echo ""
    read -r -p "Press Enter after editing .env (nano .env)..." _
fi

echo "==> Building and starting containers..."
sudo docker compose -f docker-compose.prod.yml up -d --build

echo "==> Seeding database (first run)..."
sudo docker compose -f docker-compose.prod.yml exec -T app php artisan db:seed --force || true

PUBLIC_IP=$(curl -s ifconfig.me || hostname -I | awk '{print $1}')
echo ""
echo "============================================"
echo " RoomBook should be live at:"
echo "   http://${PUBLIC_IP}"
echo ""
echo " Default logins (change passwords in prod):"
echo "   admin@roombook.local / password"
echo "   user@roombook.local  / password"
echo "============================================"
