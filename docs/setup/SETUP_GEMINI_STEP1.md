# ステップ 1: Gemini API 設定

## 📝 API キー取得（3 分で完了）

### 1. Google AI Studio にアクセス

https://makersuite.google.com/app/apikey

### 2. ログイン

-   Google アカウントでログイン（無料）
-   Gmail アカウントがあればすぐ使える

### 3. API キー作成

1. 「Get API Key」または「Create API Key」をクリック
2. プロジェクトを選択（なければ「Create API key in new project」）
3. API キーが表示される（例: `AIzaSy...`で始まる文字列）
4. **コピー**してください

### 4. .env に設定

ターミナルで以下を実行：

```bash
echo "" >> .env
echo "# Gemini API設定" >> .env
echo "GEMINI_API_KEY=ここにコピーしたAPIキーを貼り付け" >> .env
echo "GEMINI_MODEL=gemini-1.5-pro-latest" >> .env
```

または、`.env`ファイルを直接編集：

```env
# Gemini API設定
GEMINI_API_KEY=AIzaSy...（あなたのAPIキー）
GEMINI_MODEL=gemini-1.5-pro-latest
```

### 5. 設定をクリア

```bash
./vendor/bin/sail artisan config:clear
```

## ✅ 完了確認

API キーが設定されているか確認：

```bash
cat .env | grep GEMINI
```

以下のように表示されれば OK：

```
GEMINI_API_KEY=AIzaSy...
GEMINI_MODEL=gemini-1.5-pro-latest
```

## 📌 重要な注意事項

-   ✅ **完全無料** - クレジットカード不要
-   ✅ **無料枠** - 月 1,500 リクエスト（十分な量）
-   ⚠️ **API キーは秘密** - GitHub にコミットしない
-   ⚠️ **安全管理** - 他人に共有しない

## 🎯 次のステップ

API キーを設定したら教えてください。次はキューワーカーの起動に進みます！
