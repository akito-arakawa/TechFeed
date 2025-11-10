<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FetchLog extends Model
{
    use HasFactory;

    protected $table = 'fetch_logs';

    protected $fillable = [
        'job_id',
        'new_count',
        'update_count',
        'skip_count',
        'error_message',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'new_count' => 'integer',
        'update_count' => 'integer',
        'skip_count' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function fetchJob()
    {
        return $this->belongsTo(FetchJob::class, 'job_id');
    }

}
