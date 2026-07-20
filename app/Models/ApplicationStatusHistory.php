<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStatusHistory extends Model
{
    public $timestamps = false;
    protected $table = 'application_status_history';

    protected $fillable = ['application_id', 'from_status', 'to_status', 'changed_at'];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
