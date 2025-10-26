# 🚨 すぐに動かすための手順

## ターミナルで以下をコピペして実行：

```bash
cd /Users/satohiro/camp/100_laravel/Adnavi && php artisan key:generate --force && php artisan migrate --force && php artisan serve
```

これで `http://localhost:8000` でアクセスできます！

---

## もしまだ動かない場合：

以下の全てを順番に実行してください：

```bash
# 1. プロジェクトフォルダに移動
cd /Users/satohiro/camp/100_laravel/Adnavi

# 2. アプリケーションキーを生成
php artisan key:generate

# 3. マイグレーション実行
php artisan migrate --force

# 4. キャッシュクリア
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 5. サーバー起動
php artisan serve
```

---

## これで動かない場合：

エラーメッセージをそのまま教えてください！
