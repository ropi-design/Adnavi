<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\WithFileUploads;
use Livewire\Volt\Component;

new class extends Component {
    use \Livewire\WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $avatar = null;
    public ?string $avatarPreview = null;

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        if (Auth::user()->avatar) {
            $this->avatarPreview = Auth::user()->avatar_url;
        }
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'max:10240', 'mimes:jpg,jpeg,png,gif'], // 10MBまで許可
        ]);

        // アバターのアップロード処理
        if ($this->avatar) {
            // 古いアバターを削除
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // 画像をリサイズして保存
            try {
                $avatarPath = $this->resizeAndSaveAvatar($this->avatar);
                $validated['avatar'] = $avatarPath;

                // プレビューを更新
                $this->avatarPreview = asset('storage/' . $avatarPath);

                // ファイルが存在することを確認
                if (!Storage::disk('public')->exists($avatarPath)) {
                    throw new \Exception('Avatar file was not saved correctly');
                }
            } catch (\Exception $e) {
                session()->flash('error', '画像の保存に失敗しました: ' . $e->getMessage());
                unset($validated['avatar']);
            }
        } else {
            unset($validated['avatar']);
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // ユーザー情報をリフレッシュ（アイコン更新のため）
        auth()->user()->refresh();

        session()->flash('message', 'プロフィールを更新しました');
        $this->dispatch('profile-updated', name: $user->name);

        // アップロード済みのファイルをリセット
        $this->avatar = null;

        // ページをリロードしてヘッダーとサイドバーのアイコンも更新
        $this->js('window.location.reload()');
    }

    public function removeAvatar(): void
    {
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->avatar = null;
        $user->save();

        // ユーザー情報をリフレッシュ（アイコン更新のため）
        auth()->user()->refresh();

        $this->avatarPreview = null;

        session()->flash('message', 'アイコンを削除しました');
        $this->dispatch('profile-updated', name: $user->name);

        // ページをリロードしてヘッダーとサイドバーのアイコンも更新
        $this->js('window.location.reload()');
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }

    /**
     * アバター画像をリサイズして保存
     */
    protected function resizeAndSaveAvatar($file): string
    {
        $maxWidth = 800;
        $maxHeight = 800;
        $quality = 85;

        // 画像情報を取得
        $imageInfo = getimagesize($file->getRealPath());
        if (!$imageInfo) {
            // 画像情報が取得できない場合はそのまま保存
            return $file->store('avatars', 'public');
        }

        [$originalWidth, $originalHeight, $imageType] = $imageInfo;

        // リサイズが必要かチェック
        if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
            // リサイズ不要の場合はそのまま保存
            return $file->store('avatars', 'public');
        }

        // アスペクト比を保ちながらリサイズ
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = (int) ($originalWidth * $ratio);
        $newHeight = (int) ($originalHeight * $ratio);

        // 画像タイプに応じて処理
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($file->getRealPath());
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($file->getRealPath());
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($file->getRealPath());
                break;
            default:
                // サポートされていない形式はそのまま保存
                return $file->store('avatars', 'public');
        }

        if (!$sourceImage) {
            // 画像の読み込みに失敗した場合はそのまま保存
            return $file->store('avatars', 'public');
        }

        // 新しい画像を作成
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // PNG/GIFの透明度を保持
        if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // リサイズ
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        // 保存
        $filename = uniqid('avatar_') . '.' . $file->getClientOriginalExtension();

        // ディレクトリが存在しない場合は作成
        Storage::disk('public')->makeDirectory('avatars');

        $path = 'avatars/' . $filename;
        $fullPath = Storage::disk('public')->path($path);

        // 直接ストレージに保存
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $fullPath, $quality);
                break;
            case IMAGETYPE_PNG:
                imagepng($newImage, $fullPath, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($newImage, $fullPath);
                break;
        }

        // メモリを解放
        imagedestroy($sourceImage);
        imagedestroy($newImage);

        // ファイルが正しく保存されたか確認
        if (!file_exists($fullPath)) {
            throw new \Exception('Failed to save avatar image');
        }

        return $path;
    }
};

?>

<div class="h-full flex flex-col animate-fade-in" style="color: #ffffff; background-color: #000;">
    {{-- ヘッダー --}}
    <div class="flex items-center justify-between border-b px-6 py-4" style="border-color: #2a2d2e;">
        <h1 class="text-2xl font-semibold" style="color: #fff;">環境設定</h1>
        <a href="/dashboard" class="p-2 hover:bg-gray-800 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </a>
    </div>

    <div class="flex flex-1 overflow-hidden">
        {{-- 左サイドバーナビゲーション --}}
        <nav class="w-64 border-r p-4 space-y-1 overflow-y-auto"
            style="border-color: #2a2d2e; background-color: #1a1d21;">
            <a href="/settings/profile"
                class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('profile.edit') ? 'bg-white/10' : 'hover:bg-white/5' }}"
                style="color: #fff;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                プロフィール
            </a>
            <a href="/settings/password"
                class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('user-password.edit') ? 'bg-white/10' : 'hover:bg-white/5' }}"
                style="color: #fff;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                パスワード
            </a>
            <a href="/settings/appearance"
                class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('appearance.edit') ? 'bg-white/10' : 'hover:bg-white/5' }}"
                style="color: #fff;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                表示
            </a>
        </nav>

        {{-- 右メインコンテンツ --}}
        <div class="flex-1 overflow-y-auto p-8">

            {{-- コンテンツ --}}
            <div>
                <h2 class="text-2xl font-bold mb-4" style="color: #ffffff;">プロフィール設定</h2>
                <p class="mb-6" style="color: #9ca3af;">名前とメールアドレスを更新できます</p>

                {{-- メッセージ --}}
                @if (session('message'))
                    <div class="p-4 bg-green-100 border-l-4 border-green-500 rounded-lg mb-6">
                        <div class="flex items-center gap-2 text-green-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ session('message') }}
                        </div>
                    </div>
                @endif

                {{-- プロフィールフォーム --}}
                <div class="card p-8">
                    <form wire:submit="updateProfileInformation" class="space-y-6">
                        {{-- ユーザーアイコン --}}
                        <div>
                            <label class="block text-sm font-bold mb-2" style="color: #ffffff;">
                                ユーザーアイコン
                            </label>
                            <div class="flex items-center gap-6" x-data="{ preview: null }"
                                @avatar-preview-updated.window="preview = $event.detail">
                                {{-- 現在のアイコン表示 --}}
                                <div class="flex-shrink-0">
                                    <div x-show="!preview">
                                        @if (auth()->user()->avatar)
                                            <img src="{{ auth()->user()->avatar_url }}?v={{ time() }}"
                                                alt="Avatar" class="w-24 h-24 rounded-lg object-cover border-2"
                                                style="border-color: #e5e7eb;"
                                                onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="w-24 h-24 rounded-lg flex items-center justify-center text-2xl font-bold border-2 hidden"
                                                style="background-color: #9ca3af; color: #ffffff; border-color: #e5e7eb;">
                                                {{ auth()->user()->initials() }}
                                            </div>
                                        @else
                                            <div class="w-24 h-24 rounded-lg flex items-center justify-center text-2xl font-bold border-2"
                                                style="background-color: #9ca3af; color: #ffffff; border-color: #e5e7eb;">
                                                {{ auth()->user()->initials() }}
                                            </div>
                                        @endif
                                    </div>
                                    <img x-show="preview" :src="preview" alt="Avatar Preview"
                                        class="w-24 h-24 rounded-lg object-cover border-2"
                                        style="border-color: #e5e7eb;">
                                </div>

                                {{-- アップロードコントロール --}}
                                <div class="flex-1 space-y-3">
                                    <div>
                                        <label for="avatar-upload" class="inline-block cursor-pointer">
                                            <input type="file" id="avatar-upload" wire:model="avatar"
                                                accept="image/*" class="hidden"
                                                x-on:change="
                                            const file = $event.target.files[0];
                                            if (file) {
                                                const reader = new FileReader();
                                                reader.onload = (e) => {
                                                    window.dispatchEvent(new CustomEvent('avatar-preview-updated', { detail: e.target.result }));
                                                };
                                                reader.readAsDataURL(file);
                                            }
                                        ">
                                            <span
                                                class="inline-flex items-center px-4 py-2 rounded-lg font-semibold text-sm transition-colors border-2"
                                                style="background-color: #1e40af; color: #ffffff; border-color: #1e3a8a;">
                                                @if ($avatar)
                                                    ファイルが選択されました
                                                @else
                                                    選択されていません
                                                @endif
                                            </span>
                                        </label>
                                        @error('avatar')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                        <p class="mt-1 text-xs" style="color: #9ca3af;">
                                            JPG, PNG, GIF形式、最大10MBまで（自動リサイズ: 800x800px）
                                        </p>
                                    </div>

                                    @if ($avatarPreview || auth()->user()->avatar)
                                        <button type="button" wire:click="removeAvatar"
                                            class="text-sm text-red-600 hover:text-red-700 font-semibold">
                                            アイコンを削除
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- 名前 --}}
                        <div>
                            <label for="name" class="block text-sm font-bold mb-2" style="color: #ffffff;">
                                名前
                            </label>
                            <input id="name" type="text" wire:model="name" required autofocus
                                autocomplete="name" class="w-full px-4 py-2.5 rounded-lg transition-colors"
                                style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;" />
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- メールアドレス --}}
                        <div>
                            <label for="email" class="block text-sm font-bold mb-2" style="color: #ffffff;">
                                メールアドレス
                            </label>
                            <input id="email" type="email" wire:model="email" required autocomplete="email"
                                class="w-full px-4 py-2.5 rounded-lg transition-colors"
                                style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;" />
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            {{-- メール未確認通知 --}}
                            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <p class="text-sm text-yellow-800 mb-2">
                                        メールアドレスが未確認です。
                                    </p>
                                    <button type="button" wire:click="resendVerificationNotification"
                                        class="text-sm text-blue-600 hover:text-blue-700 font-semibold underline">
                                        確認メールを再送信
                                    </button>

                                    @if (session('status') === 'verification-link-sent')
                                        <p class="mt-2 text-sm text-green-600 font-medium">
                                            新しい確認リンクをメールアドレスに送信しました。
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- 保存ボタン --}}
                        <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors border-2"
                                style="background-color: #ffffff; color: #000000; border-color: #e5e7eb;"
                                data-test="update-profile-button">
                                保存
                            </button>
                        </div>
                    </form>
                </div>

                {{-- アカウント削除 --}}
                <div class="card p-8 mt-8 border-red-200">
                    <h2 class="text-xl font-bold mb-4" style="color: #ffffff;">アカウント削除</h2>
                    <p class="mb-6" style="color: #ffffff;">
                        アカウントを削除すると、すべてのデータが完全に削除されます。この操作は取り消せません。
                    </p>
                    <button
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors border-2"
                        style="background-color: #dc2626; color: #ffffff; border-color: #b91c1c;"
                        onclick="alert('この機能は実装中です')">
                        アカウントを削除
                    </button>
                </div>
            </div>
        </div>
    </div>
