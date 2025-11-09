<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserToken;
use Symfony\Component\HttpFoundation\Response;

class AuthToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken(); // AuthorizationヘッダーのBearerトークンを取得

        if (!$token) {
            return response()->json(['message' => 'Token missing'], 401);
        }

        // user_tokensテーブルからトークンを検索（有効期限チェック付き）
        $userToken = UserToken::where('token', $token)
            ->where('expiration_time', '>', now())
            ->first();

        if (!$userToken) {
            return response()->json(['message' => 'Invalid or expired token'], 401);
        }

        // トークンに紐づくユーザーを取得
        $user = User::find($userToken->user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        auth()->setUser($user);

        // リクエストにユーザー情報をセット
        $request->merge(['auth_user' => $user]);

        return $next($request);
    }
}
