# Render 502 Bad Gateway エラー解決ガイド

## 🔴 502 Bad Gateway エラーの原因

502エラーは、アプリケーションが正常に起動していない、またはRenderがアプリケーションに接続できないことを示します。

## 📋 確認すべきポイント

### 1. ログの確認

Render Dashboardで以下を確認：

1. **Webサービスを選択**
2. **Logs** タブをクリック
3. **Runtime Logs** を確認（エラーメッセージを探す）

よくあるエラー：
- `APP_KEY` が設定されていない
- データベース接続エラー
- ポート設定の問題
- マイグレーションエラー

### 2. ビルドログの確認

1. **Events** タブをクリック
2. 最新のデプロイの **Build Logs** を確認
3. エラーがあれば確認

### 3. 環境変数の確認

必須の環境変数が設定されているか確認：

```bash
APP_KEY=base64:... # 必須！
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=pgsql
# その他の環境変数
```

## 🛠️ よくある原因と解決方法

### 問題1: APP_KEYが設定されていない

**症状:**
```
RuntimeException: No application encryption key has been specified.
```

**解決方法:**
1. ローカルで `APP_KEY` を生成：
   ```bash
   php artisan key:generate --show
   ```
2. Render Dashboardで **Environment** タブを開く
3. `APP_KEY` 環境変数を追加し、生成された値を貼り付ける
4. **Save Changes** をクリック
5. サービスを再デプロイ

### 問題2: データベース接続エラー

**症状:**
```
SQLSTATE[08006] [7] could not connect to server
```

**解決方法:**
1. データベースサービスが起動しているか確認
2. データベースサービスの **Internal Database URL** を確認
3. `DB_CONNECTION=pgsql` が設定されているか確認
4. データベースの環境変数が自動設定されているか確認（render.yamlで設定済みなら自動）

### 問題3: ポート設定の問題

**現在のstartCommand:**
```yaml
startCommand: php artisan serve --host=0.0.0.0 --port=$PORT
```

**確認ポイント:**
- `$PORT` が正しく展開されているか（Renderが自動で設定）
- アプリケーションが実際に起動しているか

**改善案:**
より明示的にポートを指定する場合：

```yaml
startCommand: php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
```

ただし、Renderでは `$PORT` 環境変数が自動で設定されるため、現在の設定で問題ないはずです。

### 問題4: マイグレーションエラー

**症状:**
```
Migration failed
```

**解決方法:**
1. **Shell** タブから手動でマイグレーションを実行：
   ```bash
   php artisan migrate --force
   ```
2. エラーがあれば、マイグレーションファイルを確認

### 問題5: ストレージの権限問題

**症状:**
```
The stream or file could not be opened
```

**解決方法:**
1. **Shell** タブから実行：
   ```bash
   php artisan storage:link
   chmod -R 775 storage bootstrap/cache
   ```

### 問題6: ビルドエラー

**症状:**
```
npm run build failed
composer install failed
```

**解決方法:**
1. **Build Logs** を確認
2. `package.json` や `composer.json` に問題がないか確認
3. Node.jsやPHPのバージョンが正しいか確認

## 🔧 デバッグ手順

### ステップ1: ログを確認

```bash
# Render Dashboard → Webサービス → Logs
# Runtime Logs を確認
```

### ステップ2: Shellから手動で起動をテスト

1. **Shell** タブを開く
2. 以下を実行：
   ```bash
   php artisan serve --host=0.0.0.0 --port=$PORT
   ```
3. エラーメッセージを確認

### ステップ3: 環境変数を確認

```bash
# Shellから実行
echo $APP_KEY
echo $DB_CONNECTION
echo $PORT
```

### ステップ4: アプリケーションの状態を確認

```bash
# Shellから実行
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan about
```

## 🚀 推奨される修正

### render.yaml の改善版

```yaml
services:
  - type: web
    name: adnavi
    runtime: php
    plan: starter
    buildCommand: |
      composer install --no-dev --optimize-autoloader &&
      npm ci &&
      npm run build &&
      php artisan migrate --force &&
      php artisan storage:link &&
      php artisan config:cache &&
      php artisan route:cache &&
      php artisan view:cache
    startCommand: php artisan serve --host=0.0.0.0 --port=$PORT
    healthCheckPath: /
    envVars:
      - key: APP_ENV
        value: production
      - key: APP_DEBUG
        value: false
      - key: LOG_CHANNEL
        value: stderr
      # APP_KEY は手動で設定（sync: false）
```

**変更点:**
- `php artisan storage:link` をビルドコマンドに追加
- `APP_KEY` は手動設定（環境変数から設定）

## 📞 追加のトラブルシューティング

### ヘルスチェックの確認

1. **Settings** → **Health Check Path** が `/` に設定されているか確認
2. カスタムヘルスチェックエンドポイントを作成する場合：
   ```php
   // routes/web.php
   Route::get('/health', function () {
       return response()->json(['status' => 'ok']);
   });
   ```

### タイムアウト設定

Renderの無料プランでは起動に時間がかかることがあります。初回起動時は最大60秒ほど待ちます。

### 自動再起動の確認

アプリケーションがクラッシュしている場合、Renderが自動的に再起動を試みます。ログで再起動の理由を確認してください。

## ✅ チェックリスト

デプロイ前の確認：

- [ ] `APP_KEY` が設定されている
- [ ] データベースサービスが起動している
- [ ] すべての環境変数が設定されている
- [ ] ビルドログにエラーがない
- [ ] マイグレーションが成功している
- [ ] ストレージリンクが作成されている

## 🔗 参考リンク

- [Render トラブルシューティング](https://render.com/docs/troubleshooting)
- [Render 環境変数](https://render.com/docs/environment-variables)
- [Laravel デプロイメント](https://laravel.com/docs/deployment)

