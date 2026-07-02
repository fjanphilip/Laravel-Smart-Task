<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskAutomation;
use Illuminate\Support\Facades\Log;

class TaskObserver
{
    public function updated(Task $task): void
    {
        // Jalankan HANYA jika kolom status yang berubah
        if (!$task->wasChanged('status')) {
            return;
        }

        // Cari aturan otomatisasi yang aktif untuk project ini
        $automations = TaskAutomation::where('project_id', $task->project_id)
            ->where('trigger_status', $task->status) // Menunggu status 'done'
            ->where('is_active', true)
            ->get();

        foreach ($automations as $automation) {
            if ($automation->action_type === 'unlock_sequential') {
                $taskSequence = json_decode($automation->action_value, true);

                if (is_array($taskSequence)) {
                    $currentKey = array_search($task->id, $taskSequence);

                    if ($currentKey !== false && isset($taskSequence[$currentKey + 1])) {
                        $nextTaskId = $taskSequence[$currentKey + 1];
                        $nextTask = Task::find($nextTaskId);

                        if ($nextTask && $nextTask->status === 'blocked') {

                            // --- UBAH BAGIAN INI ---
                            $nextTask->status = 'todo';
                            $nextTask->saveQuietly(); // Benar & Didukung Laravel resmi
                            // -----------------------

                            Log::info("Otomatisasi Sukses: Task ID {$nextTask->id} ('{$nextTask->title}') terbuka karena Task ID {$task->id} selesai.");
                        }
                    }
                }
            }
        }
    }
}
