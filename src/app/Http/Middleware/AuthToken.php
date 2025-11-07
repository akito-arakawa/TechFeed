<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class AuthToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // AuthorizationヘッダーのBearer tokenを取得
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message'=> 'Token missing'], 401);
        }

        // DBのハッシュと比較
        $user = User::where('api_token', hash('sha256', $token))->first();

        if (! $user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        // 認証成功 → user をログイン扱いにする
        auth()->login($user);

        return $next($request);
    }
}
