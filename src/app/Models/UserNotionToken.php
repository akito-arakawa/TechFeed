<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotionToken extends Model
{
    protected $table = 'user_notion_tokens';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'access_token',
        'refresh_token',
        'notion_workspace_id',
        'notion_workspace_name',
        'notion_bot_id',
        'parent_page_id',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasParentPage(): bool
    {
        return filled($this->parent_page_id);
    }
}
