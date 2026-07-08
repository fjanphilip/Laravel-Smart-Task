<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{

    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(): JsonResponse
    {
        $projects = $this->projectService->getAllProjects();

        return response()->json([
            'success' => true,
            'message' => 'Data project berhasil diambil',
            'data' => $projects
        ], 200);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        $projects = $this->projectService->createProject($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Data project berhasil ditambahkan',
            'data' => $projects
        ], 201);
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return response()->json([
            'success' => true,
            'message' => 'Data project berhasil diambil',
            'data' => $project
        ], 200);
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $projects = $this->projectService->updateProject($project, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Data project berhasil diupdate',
            'data' => $projects
        ], 200);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $projects = $this->projectService->deleteProject($project);

        return response()->json([
            'success' => true,
            'message' => 'Data project berhasil dihapus',
            'data' => $projects
        ], 200);
    }


}
