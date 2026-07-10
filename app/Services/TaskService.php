<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Task;
use App\Models\TaskAutomation;
use App\Models\TaskLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

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

        if ($oldStatus !== 'review' && $task->status === 'review') {
            $this->handleChangeAssigneToManager($task);
        }

        if ($oldStatus !== $task->status && in_array($task->status, ['review', 'done'])) {
            $this->handleSendNotification($task, $oldStatus);
        }



        return $task;

    }

    public function handleSendNotification(Task $task, string $oldStatus)
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        // Tentukan role penerima notifikasi berdasarkan role pembuat perubahan
        $targetRoles = [];
        if ($user->isDeveloper()) {
            $targetRoles = ['manager', 'admin'];
        } elseif ($user->isManager()) {
            $targetRoles = ['admin', 'developer'];
        } elseif ($user->isAdmin()) {
            $targetRoles = ['developer', 'manager'];
        }

        if (empty($targetRoles)) {
            return;
        }

        // Cari semua user yang memiliki role sasaran
        $recipients = User::whereIn('role', $targetRoles)->get();

        $userName = $user->name;
        $taskTitle = $task->title;
        $statusFormatted = ucfirst(str_replace('_', ' ', $task->status));
        $oldStatusFormatted = str_replace('_', ' ', $oldStatus);

        foreach ($recipients as $recipient) {
            $notification = Notification::create([
                'user_id' => $recipient->id,
                'task_id' => $task->id,
                'title' => "Task Update: " . $statusFormatted,
                'message' => "{$userName} mengubah status tugas '{$taskTitle}' dari '{$oldStatusFormatted}' menjadi '{$statusFormatted}'.",
                'is_read' => false,
            ]);

            // Dispatch broadcast event via Laravel Reverb
            event(new \App\Events\NotificationSent($notification));
        }
    }

    public function handleChangeAssigneToManager(Task $task): void
    {
        $manager = User::where('role', 'manager')->first();

        if (!$manager) {
            throw new \Exception("Gagal mengalihkan tugas: User dengan role Manager tidak ditemukan.");
        }

        $task->updateOrFail(['assigned_to' => $manager->id]);

        TaskLog::create([
            'task_id' => $task->id,
            'user_id' => $manager->id,
            'action_taken' => 'review'
        ]);
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
