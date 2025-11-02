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

# Node.jsをインストール（nvm経由）
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash \
    && export NVM_DIR="$HOME/.nvm" \
    && [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh" \
    && nvm install 20 \
    && nvm alias default 20 \
    && nvm use default

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリを設定
WORKDIR /var/www/html

# アプリケーションファイルをコピー（依存関係ファイルを先にコピー）
COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

# 依存関係をインストール
RUN composer install --no-dev --optimize-autoloader --no-scripts \
    && export NVM_DIR="$HOME/.nvm" \
    && [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh" \
    && npm ci \
    && npm cache clean --force

# 残りのアプリケーションファイルをコピー
COPY . /var/www/html

# パーミッションを設定
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# ビルド時にアセットをビルド
RUN export NVM_DIR="$HOME/.nvm" \
    && [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh" \
    && npm run build

# 起動スクリプトをコピー
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# ポートを公開
EXPOSE 8000

# アプリケーションを起動
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

