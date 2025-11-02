<?php

use App\Http\Controllers\Api\GeminiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// APIエンドポイント（認証済みユーザーのみ）
Route::middleware(['auth', 'throttle:gemini'])->group(function () {
    // Gemini APIエンドポイント（マルチモーダル対応）
    Route::post('/gemini', [GeminiController::class, 'generate'])->name('api.gemini');
});
