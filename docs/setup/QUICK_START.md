# Adnavi - クイックスタートガイド

このガイドに従って、すぐに開発を開始できます！

## 🚀 5 分でスタート

### 1. 環境設定

```bash
# .envファイルを作成（既存の場合はスキップ）
cp .env.example .env

# アプリケーションキー生成
php artisan key:generate

# 環境変数の最低限の設定
# .env ファイルを開いて以下を設定：
# APP_LOCALE=ja
# APP_TIMEZONE=Asia/Tokyo
# DB_CONNECTION=sqlite
```

### 2. データベースセットアップ

```bash
# SQLiteデータベースファイル作成
touch database/database.sqlite

# マイグレーション実行
php artisan migrate

# （オプション）テストデータ投入
php artisan db:seed
```

### 3. アプリケーション起動

```bash
# 開発サーバー起動
php artisan serve

# 別ターミナルでVite起動
npm run dev

# ブラウザで開く
# http://localhost:8000
```

## 📁 既に作成されているファイル

### レイアウト

-   ✅ `resources/views/layouts/app.blade.php` - メインレイアウト（Flux Sidebar 付き）

### ページ

-   ✅ `resources/views/pages/dashboard.blade.php` - ダッシュボードページ

### Volt コンポーネント

-   ✅ `resources/views/livewire/dashboard/overview.blade.php` - ダッシュボード概要（実装済み）

### ルート

-   ✅ `routes/web.php` - 全ルート定義済み

### 言語ファイル

-   ✅ `lang/ja/validation.php` - 日本語バリデーション
-   ✅ `lang/ja/auth.php` - 認証メッセージ
-   ✅ `lang/ja/passwords.php` - パスワードリセット
-   ✅ `lang/ja/pagination.php` - ページネーション

### プロバイダー

-   ✅ `app/Providers/AppServiceProvider.php` - 日本語ロケール設定済み

## 🎯 次に作成する Volt コンポーネント

### 優先度：高（すぐに必要）

#### 1. Google 連携

```bash
php artisan make:volt accounts/connect-google
```

実装内容：`docs/LIVEWIRE_VOLT_GUIDE.md` の例を参照

#### 2. レポート一覧

```bash
php artisan make:volt reports/report-list
```

#### 3. レポート生成フォーム

```bash
php artisan make:volt reports/generate-report
```

### 優先度：中（後で必要）

#### 4. 広告アカウント一覧

```bash
php artisan make:volt accounts/ad-account-list
```

#### 5. Analytics プロパティ一覧

```bash
php artisan make:volt accounts/analytics-property-list
```

#### 6. インサイト一覧

```bash
php artisan make:volt insights/insight-list
```

#### 7. インサイト詳細

```bash
php artisan make:volt insights/insight-detail
```

### 優先度：低（機能実装後）

#### 8. 改善施策一覧

```bash
php artisan make:volt recommendations/recommendation-list
```

#### 9. 改善施策詳細

```bash
php artisan make:volt recommendations/recommendation-detail
```

## 📝 Volt コンポーネントの基本テンプレート

新しいコンポーネントを作成したら、以下のテンプレートから始めてください：

### シンプルな表示コンポーネント

```blade
<?php

use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount};

// マウント時の処理
mount(function () {
    // 初期データ読み込み
});

// 状態
state([
    'items' => [],
]);

?>

<div>
    <flux:heading size="lg">タイトル</flux:heading>

    <div class="mt-6">
        {{-- コンテンツ --}}
    </div>
</div>
```

### フォームコンポーネント

```blade
<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;

new class extends Component {
    #[Validate('required|min:3')]
    public string $name = '';

    public function save(): void
    {
        $this->validate();

        // 保存処理

        session()->flash('message', '保存しました');
    }
}; ?>

<div>
    <form wire:submit="save">
        <flux:field>
            <flux:label>名前</flux:label>
            <flux:input wire:model="name" />
            <flux:error name="name" />
        </flux:field>

        <flux:button type="submit">保存</flux:button>
    </form>
</div>
```

### 一覧表示コンポーネント（ページネーション付き）

```blade
<?php

use Livewire\WithPagination;
use Livewire\Volt\Component;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function with(): array
    {
        $query = Model::query();

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        return [
            'items' => $query->paginate(10),
        ];
    }
}; ?>

<div class="space-y-4">
    <flux:input
        wire:model.live.debounce.300ms="search"
        placeholder="検索..."
    />

    @foreach($items as $item)
        <flux:card>
            {{ $item->name }}
        </flux:card>
    @endforeach

    {{ $items->links() }}
</div>
```

## 🎨 Flux UI コンポーネントの使い方

### ボタン

```blade
<flux:button>デフォルト</flux:button>
<flux:button variant="primary">プライマリ</flux:button>
<flux:button variant="danger">危険</flux:button>
<flux:button variant="ghost">ゴースト</flux:button>
<flux:button icon="plus">アイコン付き</flux:button>
<flux:button wire:click="action">クリック</flux:button>
<flux:button href="/path" wire:navigate>リンク</flux:button>
```

### カード

```blade
<flux:card>
    <flux:heading size="lg">タイトル</flux:heading>
    <p>コンテンツ</p>
</flux:card>
```

### フォーム

```blade
<flux:field>
    <flux:label>ラベル</flux:label>
    <flux:input wire:model="value" />
    <flux:description>説明文</flux:description>
    <flux:error name="value" />
</flux:field>

<flux:field>
    <flux:label>選択</flux:label>
    <flux:select wire:model="selected">
        <option value="1">オプション1</option>
        <option value="2">オプション2</option>
    </flux:select>
</flux:field>
```

### バッジ

```blade
<flux:badge>デフォルト</flux:badge>
<flux:badge variant="success">成功</flux:badge>
<flux:badge variant="danger">エラー</flux:badge>
<flux:badge variant="warning">警告</flux:badge>
```

### アラート

```blade
@if (session('message'))
    <flux:alert variant="success">
        {{ session('message') }}
    </flux:alert>
@endif

@if (session('error'))
    <flux:alert variant="danger">
        {{ session('error') }}
    </flux:alert>
@endif
```

## 🔧 開発のヒント

### 1. Livewire のデバッグ

```blade
{{-- 現在の状態を確認 --}}
@dump($this->all())

{{-- 特定の変数を確認 --}}
@dump($items)
```

### 2. ローディング状態

```blade
{{-- ボタンのローディング --}}
<flux:button wire:click="save">
    <span wire:loading.remove wire:target="save">保存</span>
    <span wire:loading wire:target="save">保存中...</span>
</flux:button>

{{-- オーバーレイ --}}
<div wire:loading wire:target="loadData" class="fixed inset-0 bg-black/50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg">
        <flux:icon.arrow-path class="w-8 h-8 animate-spin" />
        <p>読み込み中...</p>
    </div>
</div>
```

### 3. 確認ダイアログ

```blade
<flux:button
    wire:click="delete"
    wire:confirm="本当に削除しますか？"
    variant="danger"
>
    削除
</flux:button>
```

### 4. イベントリスニング

```blade
<?php
use function Livewire\Volt\{on};

// イベントをリッスン
on(['data-updated' => function () {
    $this->loadData();
}]);
?>
```

### 5. wire:navigate で SPA 風のナビゲーション

```blade
{{-- スムーズな画面遷移 --}}
<flux:button href="/reports" wire:navigate>
    レポート一覧
</flux:button>

{{-- 新しいタブで開く場合は通常のリンク --}}
<a href="/reports" target="_blank">
    レポートを新しいタブで開く
</a>
```

## 📚 参考ドキュメント

-   **Livewire Volt 詳細**: `docs/LIVEWIRE_VOLT_GUIDE.md`
-   **実装ロードマップ**: `docs/IMPLEMENTATION_ROADMAP.md`
-   **アーキテクチャ設計**: `docs/ARCHITECTURE.md`
-   **Laravel 12 と日本語化**: `docs/LARAVEL12_AND_LOCALIZATION.md`

## 🐛 トラブルシューティング

### Volt コンポーネントが表示されない

```bash
# ビューキャッシュをクリア
php artisan view:clear

# Livewireのキャッシュをクリア
php artisan livewire:discover
```

### スタイルが適用されない

```bash
# Viteを再起動
npm run dev
```

### データベースエラー

```bash
# マイグレーションをリセット
php artisan migrate:fresh

# または特定のマイグレーションをロールバック
php artisan migrate:rollback
```

## ✅ 次のステップ

1. **データベース設計の実装**

    ```bash
    php artisan make:migration create_google_accounts_table
    php artisan make:migration create_ad_accounts_table
    # など
    ```

2. **モデルの作成**

    ```bash
    php artisan make:model GoogleAccount
    php artisan make:model AdAccount
    # など
    ```

3. **サービスクラスの作成**

    ```bash
    php artisan make:service Google/GoogleAuthService
    php artisan make:service Google/GoogleAdsService
    # など
    ```

4. **Volt コンポーネントの実装**
    - 上記の優先度順に実装

準備完了です！開発を始めましょう 🚀

何か質問があれば、各ドキュメントを参照するか、遠慮なくお尋ねください！
