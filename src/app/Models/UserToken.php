<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
