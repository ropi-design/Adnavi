FROM php:8.2-cli

# 必要なシステムパッケージをインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリを設定
WORKDIR /var/www/html

# アプリケーションファイルをコピー（依存関係ファイルを先にコピー）
COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

# 依存関係をインストール
RUN composer install --no-dev --optimize-autoloader --no-scripts \
    && npm ci \
    && npm cache clean --force

# 残りのアプリケーションファイルをコピー
COPY . /var/www/html

# パーミッションを設定
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# ビルド時にアセットをビルド
RUN npm run build

# ポートを公開
EXPOSE 8000

# 起動スクリプトを作成
RUN echo '#!/bin/sh\n\
set -e\n\
php artisan migrate --force || true\n\
php artisan storage:link || true\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}' > /start.sh \
    && chmod +x /start.sh

# アプリケーションを起動
CMD ["/bin/sh", "/start.sh"]

