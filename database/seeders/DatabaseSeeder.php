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
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@gmail.com',
            'password' => Hash::make('manager123'),
            'role' => 'manager',
        ]);
        $dev = User::create([
            'name' => 'Developer User',
            'email' => 'developer@gmail.com',
            'password' => Hash::make('developer123'),
            'role' => 'developer',
        ]);
        $member = User::create([
            'name' => 'Member User',
            'email' => 'member@gmail.com',
            'password' => Hash::make('member123'),
            'role' => 'member',
        ]);

        // 2. BUAT DATA PROYEK (Menggunakan ID dari user manager)
        $project = Project::create([
            'name' => 'CostumeRent Platform',
            'description' => 'Aplikasi persewaan kostum berbasis IoT dan otomasi pembayaran.',
            'status' => 'active',
            'user_id' => $manager->id,
        ]);

        // Hubungkan semua user ke Proyek via tabel pivot
        $project->members()->attach([$admin->id, $manager->id, $dev->id, $member->id]);

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
                'user_id' => $manager->id,
                'depends_on_task_id' => ($index === 0) ? null : $taskIds[$index - 1],
                'title' => $title,
                'description' => "Deskripsi pengerjaan untuk modul: {$title}",
                'status' => ($index === 0) ? 'todo' : 'blocked',
                'priority' => 'high',
                'due_date' => now()->addDays($index + 2),
                'estimate_hours' => 4,
                'assigned_to' => $dev->id // Ditugaskan ke Developer
            ]);

            $taskIds[] = $task->id;
        }


    }
}