<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleBookmarkController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ArticleViewController;
use App\Http\Controllers\Me\MeBookmarkController;
use App\Http\Controllers\Me\MeViewController;
use App\Http\Controllers\ArticleSearchController;
use App\Http\Controllers\NotionController;

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
    Route::delete('/articles/{article}/bookmark', [ArticleBookmarkController::class, 'destroy']);
    Route::post('/articles/{article}/view', [ArticleViewController::class, 'store']);
    Route::get('me/bookmarks', [MeBookmarkController::class, 'index']);
    Route::get('me/views', [MeViewController::class, 'index']);
    Route::get('articles/search', [ArticleSearchController::class, 'index']);
    Route::get('/notion/auth', [NotionController::class, 'auth']);
    Route::post('/notion/output', [NotionController::class, 'output']);
});

Route::middleware('optional.auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);
});

Route::get('/notion/callback', [NotionController::class, 'callback']);