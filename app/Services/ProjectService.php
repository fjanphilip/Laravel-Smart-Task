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
        $userId = auth()->id();

        return Project::where('user_id', $userId)
            ->orWhereHas('members', function ($query) use ($userId) {
                $query->where('users.id', $userId);
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
