<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id',
        'user_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'estimate_hours',
        'assigned_to',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function requirements()
    {
        return $this->belongsToMany(
            Task::class,          // Menghubungkan ke dirinya sendiri (Model Task)
            'task_dependencies',  // Nama tabel pivot
            'task_id',            // Kolom foreign key tugas utama
            'dependency_id'       // Kolom foreign key tugas syarat
        );
    }
}
