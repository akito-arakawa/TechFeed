<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_id',
        'source_item_id',
        'url',
        'title',
        'author_name',
        'thumbnail_url',
        'source_like_count',
        'published_at',
        'fetched_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'fetched_at' => 'datetime',
    ];

    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'article_categories',
            'article_id',
            'category_id'
        );
    }

    public function likes()
    {
        return $this->hasMany(UserLike::class);
    }

    public function views()
    {
        return $this->hasMany(ArticleView::class);
    }
}
