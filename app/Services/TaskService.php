<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    public function getAllTasks(): Collection
    {
        return Task::where('user_id', auth()->user()->id)->get();

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

        return $task;
    }

    public function deleteTask(Task $task): Task
    {
        $task->delete();

        return $task;
    }
}
