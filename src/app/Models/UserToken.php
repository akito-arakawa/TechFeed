<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserToken extends Model
{
    use HasFactory;

    protected $table = 'user_tokens';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'token',
        'expiration_time'
    ];

    protected $hidden = [
        'token'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createToken(User $user) {
        $this->user_id = $user->id;
        $this->expiration_time = now()->addMonth();
        // ランダムなトークンを発行
        $token = Str::random(60);
        $this->token = hash('sha256', $token);
        return $this->saveOrFail();
    }
}
