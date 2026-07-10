<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
    /**
     * Create a new class instance.
     */
    public function getAllProjects(): Collection
    {
        $user = auth()->user();
        if (!$user) {
            return new Collection();
        }

        // Admin, Manager, dan Developer memiliki akses global ke seluruh project
        if ($user->isAdmin() || $user->isManager() || $user->isDeveloper()) {
            return Project::all();
        }

        // Member biasa hanya dapat melihat project di mana dia terdaftar sebagai anggota
        return Project::whereHas('members', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->get();
    }

    public function createProject(array $data): Project
    {
        $data['user_id'] = auth()->id();

        $project = Project::create($data);

        // Attach all users as members to this project
        $userIds = User::pluck('id')->toArray();
        $project->members()->attach($userIds);

        return $project;
    }

    public function updateProject(Project $project, array $data): Project
    {
        $project->update($data);

        return $project;
    }

    public function deleteProject(Project $project): bool
    {
        return $project->delete();
    }

}
