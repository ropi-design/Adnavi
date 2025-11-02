FROM php:8.2-cli

# 必要なシステムパッケージをインストール
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure pgsql \
    && docker-php-ext-configure zip \
    && docker-php-ext-install -j$(nproc) pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Node.jsをインストール（公式バイナリから）
RUN curl -fsSL https://nodejs.org/dist/v20.11.0/node-v20.11.0-linux-x64.tar.xz -o node.tar.xz \
    && tar -xJf node.tar.xz -C /usr/local --strip-components=1 \
    && rm node.tar.xz \
    && npm install -g npm@latest

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

# 起動スクリプトをコピー
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# ポートを公開
EXPOSE 8000

# アプリケーションを起動
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

