<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleBookmarkController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/signup', [AuthController::class, 'signup']);
Route::middleware('auth.token')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('/articles/{article}/bookmark', [ArticleBookmarkController::class, 'store']);
});

Route::middleware('optional.auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);
});
