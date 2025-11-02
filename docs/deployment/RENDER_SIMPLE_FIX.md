# Render 502 エラー 簡単な修正方法

## 🔴 502 エラーが出たら

### 最も多い原因：APP_KEY が設定されていない

## ✅ 解決手順（5 分でできます）

### 1. ローカルで APP_KEY を生成

ターミナルで以下を実行：

```bash
cd /Users/satohiro/camp/100_laravel/Adnavi
php artisan key:generate --show
```

出力される値（`base64:...`で始まる長い文字列）をコピー

### 2. Render Dashboard で設定

1. https://dashboard.render.com/ にアクセス
2. 「adnavi」サービスをクリック
3. 左側のメニューから「Environment」をクリック
4. 「+ Add Environment Variable」ボタンをクリック
5. 以下を入力：
    - **Key**: `APP_KEY`
    - **Value**: 先ほどコピーした値（`base64:...`から始まる文字列）
6. 「Save Changes」をクリック

### 3. 再デプロイ

自動的に再デプロイが始まります。数分待ちます。

### 4. 確認

再デプロイが完了したら、URL にアクセスして確認してください。

## 📸 もしログを見たい場合

1. Render Dashboard → 「adnavi」サービス
2. 上のタブから「Logs」をクリック
3. 「Runtime Logs」を見る

エラーメッセージがあれば、それをコピーして確認してください。

## ⚠️ それでもダメな場合

ログのエラーメッセージを教えてください。具体的な対処方法を提案します。
