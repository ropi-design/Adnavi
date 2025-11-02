# Render PHP 環境設定エラー解決方法

## 問題：php: command not found

ログに「Using Node.js version」と表示されている場合、サービスが**Node.js 環境**として認識されています。

## 解決方法

### 方法 1: Render Dashboard で環境を手動で変更（最速）

1. Render Dashboard → **adnavi** サービスを開く
2. 左側メニューから **Settings** をクリック
3. **Environment** のセクションを見つける
4. **Environment** ドロップダウンから **PHP** を選択
5. **Save Changes** をクリック
6. 自動的に再デプロイが始まります

### 方法 2: サービスを削除して再作成

1. Render Dashboard → **adnavi** サービス
2. **Settings** → 最下部の **Delete Service** をクリック
3. 削除を確認
4. **New +** → **Blueprint** を選択
5. リポジトリを再選択
6. **Apply** をクリック

### 方法 3: render.yaml の確認

render.yaml は正しく設定されていますが、もし手動でサービスを作成する場合：

1. **New +** → **Web Service**
2. GitHub リポジトリを接続
3. **Environment**: **PHP** を選択（重要！）
4. その他の設定を入力

## 確認ポイント

再デプロイ後、ログで以下を確認：

✅ **成功例：**

```
==> Using PHP version 8.2
==> Running 'php artisan serve...'
```

❌ **失敗例（現在）：**

```
==> Using Node.js version 22.16.0
==> Running 'php artisan serve...'
bash: php: command not found
```

## 重要

**Environment を PHP に設定しない限り、PHP コマンドは利用できません。**

Render Dashboard の**Settings**で必ず確認してください！
