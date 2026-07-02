<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
    /**
     * Create a new class instance.
     */
    public function getAllProjects(): Collection
    {

        return Project::where('user_id', auth()->user()->id)->get();
    }

    public function createProject(array $data): Project
    {

        $data['user_id'] = auth()->id();

        return Project::create($data);
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
