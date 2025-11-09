<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UserToken;
use App\Http\Requests\SignupRequest;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // ランダムなトークンを発行
        $token = Str::random(60);

        // DB保存
        $userToken = new UserToken();
        $userToken->user_id = $user->id;
        $userToken->expiration_time = now()->addMonth();
        $userToken->token = hash('sha256', $token);
        $userToken->saveOrFail();

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function signup(SignupRequest $req)
    {
        try {
            $user = new User();
            $user->createUser($req->name, $req->email, $req->password);
            $token = new UserToken();
            $token->createToken($user);
            return ['token' => $token->token];
        } catch (\Exception $e) {
            return response([], 500);
        }
    }
}
