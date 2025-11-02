#!/bin/bash
set -e

# NVMを読み込む
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

# マイグレーションを実行
php artisan migrate --force || true

# ストレージリンクを作成
php artisan storage:link || true

# キャッシュを作成
php artisan config:cache
php artisan route:cache
php artisan view:cache

# アプリケーションを起動
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}

