<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskAutomation;
use App\Models\Project;
use App\Models\TaskLog;
use Illuminate\Support\Facades\Log;

class TaskObserver
{
    public function updated(Task $task): void
    {
        // Jalankan HANYA jika kolom status yang benar-benar sukses berubah
        if (!$task->wasChanged('status')) {
            return;
        }

        // Ambil semua otomasi aktif yang cocok dengan status baru task ini
        $automations = TaskAutomation::where('project_id', $task->project_id)
            ->where('trigger_status', $task->status)
            ->where('is_active', true)
            ->get();

        // Log perubahan status utama dari user via Postman/Web
        TaskLog::create([
            'task_id' => $task->id,
            'user_id' => auth()->id() ?? $task->user_id,
            'action_taken' => $task->status,
        ]);

        foreach ($automations as $automation) {

            // ==========================================================
            // FITUR 1: JIKA STATUS 'done' -> Buka Task Selanjutnya
            // ==========================================================
            if ($automation->action_type === 'unlock_task' && $task->status === 'done') {
                $taskSequence = json_decode($automation->action_value, true);

                if (is_array($taskSequence)) {
                    $taskSequence = array_map('intval', $taskSequence);
                    $currentKey = array_search((int) $task->id, $taskSequence);

                    if ($currentKey !== false && isset($taskSequence[$currentKey + 1])) {
                        $nextTaskId = $taskSequence[$currentKey + 1];
                        $nextTask = Task::find($nextTaskId);

                        if ($nextTask && $nextTask->status === 'blocked') {
                            $nextTask->status = 'todo';
                            $nextTask->saveQuietly();

                            // Log otomatisasi sistem membuka task berikutnya
                            TaskLog::create([
                                'task_id' => $nextTask->id,
                                'user_id' => null, // null berarti digerakkan oleh sistem/otomasi
                                'action_taken' => $nextTask->status
                            ]);

                            Log::info("Otomatisasi Sukses (Unlock Task): Task ID {$nextTask->id} terbuka karena Task ID {$task->id} selesai.");
                        }
                    }
                }
            }

            // ==========================================================
            // FITUR 2: JIKA STATUS 'review' -> Oper ke Manager
            // ==========================================================
            if ($automation->action_type === 'change_assignee' && $task->status === 'review') {

                if ($automation->action_value === 'manager') {
                    $project = Project::find($task->project_id);

                    if ($project && $project->user_id) {
                        // Ubah penanggung jawab ke user_id milik manager secara dinamis
                        $task->assigned_to = $project->user_id;
                        $task->saveQuietly();

                        // Log otomatisasi sistem mengubah assignee tugas
                        TaskLog::create([
                            'task_id' => $task->id,
                            'user_id' => auth()->id() ?? $task->user_id,
                            'action_taken' => $automation->action_type
                        ]);

                        Log::info("Otomatisasi Sukses (Change Assignee): Task ID {$task->id} dioper ke Manager ID {$project->user_id}.");
                    }
                }
            }
        }
    }
}