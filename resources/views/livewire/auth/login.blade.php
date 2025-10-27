<x-layouts.auth>
    <div class="space-y-6">
        {{-- ヘッダー --}}
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900">ログイン</h1>
            <p class="text-gray-600 mt-2">メールアドレスとパスワードを入力してください</p>
        </div>

        {{-- セッションステータス --}}
        @if (session('status'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm text-center">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            {{-- メールアドレス --}}
            <div>
                <label for="email" class="block text-sm font-bold text-gray-900 mb-2">
                    メールアドレス
                </label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    autocomplete="email" placeholder="email@example.com" class="form-input" />
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- パスワード --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block text-sm font-bold text-gray-900">
                        パスワード
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                            パスワードをお忘れですか？
                        </a>
                    @endif
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    placeholder="パスワード" class="form-input" />
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember Me --}}
            <div class="flex items-center">
                <input id="remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" />
                <label for="remember" class="ml-2 text-sm text-gray-700">
                    ログイン状態を保持する
                </label>
            </div>

            {{-- ログインボタン --}}
            <button type="submit" class="btn btn-primary w-full py-3 text-base" data-test="login-button">
                ログイン
            </button>
        </form>

        {{-- 新規登録リンク --}}
        @if (Route::has('register'))
            <div class="text-center text-sm text-gray-600 pt-4 border-t border-gray-200">
                <span>アカウントをお持ちでない方は</span>
                <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700 font-semibold ml-1">
                    新規登録
                </a>
            </div>
        @endif
    </div>
</x-layouts.auth>
