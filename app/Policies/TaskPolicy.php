<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        $project = $task->project;
        return $user->isAdmin() || 
            $user->isDeveloper() ||
            ($project && ($project->user_id === $user->id || $project->members()->where('users.id', $user->id)->exists())) ||
            $task->user_id === $user->id ||
            $task->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->isDeveloper();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        $project = $task->project;

        // Admin dan Owner Project (Manager) memiliki akses penuh
        if ($user->isAdmin() || ($project && $project->user_id === $user->id)) {
            return true;
        }

        // Developer bisa mengedit tugas di proyek apa saja, tetapi HANYA boleh mengubah kolom 'status'
        if ($user->isDeveloper()) {

            $request = request();
            $fieldsToCheck = ['title', 'description', 'priority', 'due_date', 'estimate_hours', 'assigned_to', 'depends_on_task_id', 'project_id'];
            
            foreach ($fieldsToCheck as $field) {
                if ($request->has($field)) {
                    $requestValue = $request->input($field);
                    $originalValue = $task->$field;

                    if ($field === 'due_date' && $requestValue && $originalValue) {
                        if (date('Y-m-d', strtotime($requestValue)) !== date('Y-m-d', strtotime($originalValue))) {
                            return false;
                        }
                    } elseif ($field === 'assigned_to' || $field === 'depends_on_task_id' || $field === 'project_id' || $field === 'estimate_hours') {
                        if ($requestValue !== null && (int)$requestValue !== (int)$originalValue) {
                            return false;
                        }
                    } else {
                        if ($requestValue !== null && $requestValue != $originalValue) {
                            return false;
                        }
                    }
                }
            }

            return true;
        }

        // Member hanya bisa mengupdate jika:
        // 1. Task tersebut ditugaskan ke dirinya sendiri
        // 2. Hanya kolom 'status' yang diubah (kolom lain seperti judul, estimasi jam, dsb. tidak boleh berubah)
        if ($user->isMember()) {
            if ($task->assigned_to !== $user->id) {
                return false;
            }

            $request = request();
            $fieldsToCheck = ['title', 'description', 'priority', 'due_date', 'estimate_hours', 'assigned_to', 'depends_on_task_id', 'project_id'];
            
            foreach ($fieldsToCheck as $field) {
                if ($request->has($field)) {
                    $requestValue = $request->input($field);
                    $originalValue = $task->$field;

                    if ($field === 'due_date' && $requestValue && $originalValue) {
                        if (date('Y-m-d', strtotime($requestValue)) !== date('Y-m-d', strtotime($originalValue))) {
                            return false;
                        }
                    } elseif ($field === 'assigned_to' || $field === 'depends_on_task_id' || $field === 'project_id' || $field === 'estimate_hours') {
                        if ($requestValue !== null && (int)$requestValue !== (int)$originalValue) {
                            return false;
                        }
                    } else {
                        if ($requestValue !== null && $requestValue != $originalValue) {
                            return false;
                        }
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        $project = $task->project;
        
        // Hanya Admin dan Owner Project (Manager) yang dapat menghapus Task
        return $user->isAdmin() || ($project && $project->user_id === $user->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }
}
