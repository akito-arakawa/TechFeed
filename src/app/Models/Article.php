<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory; 

    protected $fillable = [
        'source_id','source_item_id','url','title','author_name',
        'thumbanil_url','source_like_count','pubished_at','feched_at'
    ];

    protected $cats = [
        'published_at' => 'datetime',
        'fetched_at' => 'datetime',
    ];

    public function source()
    {
        return $this->hasMany(Source::class);
    }

    public function articleCategory()
    {
        return $this->hasMany(ArticleCategory::class);
    }

    public function like()
    {
        return $this->hasMany(UserLike::class);
    }

    public function views()
    {
        return $this->hasMan(ArticleView::class);
    }

}
