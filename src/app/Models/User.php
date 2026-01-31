<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use App\Models\UserToken;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function user_token()
    {
        return $this->hasOne(UserToken::class);
    }

    public function bookmarks()
    {
        return $this->belongsToMany(
            Article::class,
            'user_bookmarks',
            'user_id',
            'article_id',
        );
    }

    public function views()
    {
        return $this->belongsToMany(
            Article::class,
            'article_views',
            'user_id',
            'article_id',
        )->withPivot('last_viewed_at');
    }

    public function notionToken()
    {
        return $this->hasOne(UserNotionToken::class);
    }

    public function createUser($name, $email, $password)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = Hash::make($password);
        return $this->saveOrFail();
    }
}
