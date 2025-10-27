<x-layouts.auth>
    <div class="space-y-6">
        {{-- ヘッダー --}}
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900">新規登録</h1>
            <p class="text-gray-600 mt-2">アカウントを作成してください</p>
        </div>

        {{-- セッションステータス --}}
        @if (session('status'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm text-center">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
            @csrf

            {{-- 名前 --}}
            <div>
                <label for="name" class="block text-sm font-bold text-gray-900 mb-2">
                    名前
                </label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                    autocomplete="name" placeholder="山田 太郎" class="form-input" />
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- メールアドレス --}}
            <div>
                <label for="email" class="block text-sm font-bold text-gray-900 mb-2">
                    メールアドレス
                </label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                    autocomplete="email" placeholder="email@example.com" class="form-input" />
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- パスワード --}}
            <div>
                <label for="password" class="block text-sm font-bold text-gray-900 mb-2">
                    パスワード
                </label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                    placeholder="8文字以上" class="form-input" />
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- パスワード確認 --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-bold text-gray-900 mb-2">
                    パスワード（確認）
                </label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                    autocomplete="new-password" placeholder="もう一度入力してください" class="form-input" />
            </div>

            {{-- 登録ボタン --}}
            <button type="submit" class="btn btn-primary w-full py-3 text-base" data-test="register-user-button">
                アカウント作成
            </button>
        </form>

        {{-- ログインリンク --}}
        <div class="text-center text-sm text-gray-600 pt-4 border-t border-gray-200">
            <span>すでにアカウントをお持ちの方は</span>
            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-semibold ml-1">
                ログイン
            </a>
        </div>
    </div>
</x-layouts.auth>
