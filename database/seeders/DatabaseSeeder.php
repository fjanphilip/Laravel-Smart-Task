<?php

namespace Database\Seeders;

use App\Models\User;     // <-- Import Model User
use App\Models\Project;  // <-- Import Model Project
use App\Models\Task;
use App\Models\TaskAutomation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. BUAT DATA USER TERLEBIH DAHULU (Agar ID 1 tersedia untuk Project dan Task)
        $user = User::create([
            'name' => 'Developer CostumeRent',
            'email' => 'developer@costumerent.com',
            'password' => Hash::make('password'), // mengamankan password
        ]);

        // 2. BUAT DATA PROYEK (Menggunakan ID dari user yang baru dibuat)
        $project = Project::create([
            'name' => 'CostumeRent Platform',
            'description' => 'Aplikasi persewaan kostum berbasis IoT dan otomasi pembayaran.',
            'status' => 'active',
            'user_id' => $user->id, // <-- Mengarah ke ID User 1
        ]);

        // 3. Array berisi 10 judul tugas berurutan untuk project CostumeRent
        $taskTitles = [
            'Analisis Kebutuhan Sistem & ERD CostumeRent',
            'Slicing UI Homepage dan Katalog Kostum',
            'Fitur Manajemen Stok & Inventaris Kostum',
            'Sistem Keranjang Belanja & Variasi Aksesoris',
            'Booking System & Validasi Tanggal Sewa',
            'Integrasi Payment Gateway Midtrans',
            'Fitur Pengembalian Kostum & Denda Otomatis',
            'Dashboard Laporan Keuangan Owner',
            'Testing Sandbox & Skenario Pembayaran',
            'Deploy Production ke VPS Docker'
        ];

        $taskIds = [];

        // 4. Looping untuk membuat 10 task ke database
        foreach ($taskTitles as $index => $title) {
            $task = Task::create([
                'project_id' => $project->id,
                'user_id' => $user->id,       // <-- Mengarah ke ID User 1
                'title' => $title,
                'description' => "Deskripsi pengerjaan untuk modul: {$title}",
                'status' => ($index === 0) ? 'todo' : 'blocked',
                'priority' => 'high',
                'due_date' => now()->addDays($index + 2),
                'estimate_hours' => 4,
                'assigned_to' => $user->id    // <-- Ditugaskan ke ID User 1
            ]);

            $taskIds[] = $task->id;
        }

        // 1. Aturan Berantai (Ganti action_type menjadi 'unlock_task')
        TaskAutomation::create([
            'project_id' => $project->id,
            'trigger_status' => 'done',
            'action_type' => 'unlock_task', // <-- Menggunakan enum kamu
            'action_value' => json_encode($taskIds),
            'is_active' => true
        ]);

        // 2. Aturan Oper ke Manager (Ganti action_type menjadi 'change_assignee')
        TaskAutomation::create([
            'project_id' => $project->id,
            'trigger_status' => 'review',  // <-- Menggunakan enum kamu
            'action_type' => 'change_assignee', // <-- Menggunakan enum kamu
            'action_value' => 'manager',
            'is_active' => true
        ]);
    }
}