<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug'
    ];

    public function articleCategory()
    {
        return $this->hasMany(ArticleCategory::class);
    }

    public function scopeSlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

}
