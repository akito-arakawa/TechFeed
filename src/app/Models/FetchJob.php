<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FetchJob extends Model
{
    use HasFactory;

    protected $table = 'fetch_jobs';

    protected $fillable = [
        'target',
        'params_json',
        'status',
        'fetched_count',
        'error_message',
    ];

    protected $casts = [
        'params_json' => 'array',
        'fetched_count' => 'integer',
    ];

    public function fetchLogs()
    {
        return $this->hasMany(FetchLog::class);
    }

}
