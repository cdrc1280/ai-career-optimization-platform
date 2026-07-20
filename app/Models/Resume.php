<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Resume extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'original_filename', 'file_path', 'mime_type', 'file_size',
        'parsed_data', 'parse_status', 'parse_error',
    ];

    protected function casts(): array
    {
        return ['parsed_data' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ResumeVersion::class);
    }

    public function masterVersion(): HasOne
    {
        return $this->hasOne(ResumeVersion::class)->where('is_master', true);
    }
}
