<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OptionalAuthToken
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
        $token = $request->bearerToken();

        if ($token) {
            $userToken = UserToken::where('token', $token)
                ->where('expiration_time', '>', now())
                ->first();

            if ($userToken) {
                $user = User::find($userToken->user_id);
                if ($user) {
                    auth()->setUser($user);
                }
            }
        }
        return $next($request);
    }
}
