<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskAutomation;
use App\Models\TaskLog;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    public function getAllTasks(?int $projectId = null): Collection
    {
        $userId = auth()->id();

        // Ambil semua ID proyek di mana pengguna adalah pembuat ATAU anggota
        $accessibleProjectIds = Project::where('user_id', $userId)
            ->orWhereHas('members', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->pluck('id');

        $query = Task::whereIn('project_id', $accessibleProjectIds);

        // Jika React mengirimkan project_id, filter datanya berdasarkan project tersebut
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        return $query->latest()->get();
    }

    public function createTask(array $data): Task
    {
        $data['user_id'] = auth()->id();

        $taskexist = Task::where('project_id', $data['project_id'])->where('status', 'todo')->exists();

        if ($taskexist) {
            $data['status'] = 'blocked';
        } else {
            $data['status'] = 'todo';
        }

        return Task::create($data);
    }

    public function getTaskById(int $id): Task
    {
        $task = Task::findOrFail($id);

        return $task;
    }
    public function updateTask(Task $task, array $data): Task
    {

        $oldStatus = $task->status;



        $task->update($data);

        $task->refresh();



        if ($oldStatus !== 'done' && $task->status === 'done') {

            $this->handleUnlockNextTask($task);

        }



        return $task;

    }



    public function handleUnlockNextTask(Task $task): void
    {

        $nextTasks = Task::where('project_id', $task->project_id)
            ->where('depends_on_task_id', $task->id)
            ->where('status', 'blocked')
            ->get();



        foreach ($nextTasks as $nextTask) {

            $nextTask->update(['status' => 'todo']);

            TaskLog::create([
                'task_id' => $nextTask->id,
                'user_id' => null,
                'action_taken' => 'todo'
            ]);
        }

    }

    public function deleteTask(Task $task): Task
    {
        $task->delete();

        return $task;
    }
}
