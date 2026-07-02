<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAutomation extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'trigger_status',
        'action_type',
        'action_value',
        'is_active',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
