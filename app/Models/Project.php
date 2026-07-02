<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'user_id',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function automations()
    {
        return $this->hasMany(TaskAutomation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
