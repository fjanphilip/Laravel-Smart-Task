<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    public function getAllTasks(?int $projectId = null): Collection
    {
        $query = Task::where('user_id', auth()->id());

        // Jika React mengirimkan project_id, filter datanya berdasarkan project tersebut
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        return $query->latest()->get();
    }

    public function createTask(array $data): Task
    {
        $data['user_id'] = auth()->id();

        return Task::create($data);
    }

    public function getTaskById(int $id): Task
    {
        $task = Task::findOrFail($id);

        return $task;
    }
    public function updateTask(Task $task, array $data): Task
    {

        $task->update($data);

        // $task->refresh();

        return $task;
    }

    public function deleteTask(Task $task): Task
    {
        $task->delete();

        return $task;
    }
}
