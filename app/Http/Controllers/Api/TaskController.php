<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(Request $request): JsonResponse
    {
        $projectId = $request->query('project_id');

        $tasks = $this->taskService->getAllTasks($projectId);

        return response()->json([
            "status" => true,
            "message" => "Data berhasil ditampilkan",
            "data" => $tasks
        ], 200);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $this->authorize('create', Task::class);

        $task = $this->taskService->createTask($request->validated());

        return response()->json([
            "status" => true,
            "message" => "Data berhasil ditambahkan",
            "data" => $task
        ], 201);
    }

    public function update(Task $task, UpdateTaskRequest $request): JsonResponse
    {
        $this->authorize('update', $task);

        $task = $this->taskService->updateTask($task, $request->validated());

        return response()->json([
            "status" => true,
            "message" => "Data berhasil diupdate",
            "data" => $task
        ], 201);
    }

    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task = $this->taskService->getTaskById($task->id);

        return response()->json([
            "status" => true,
            "message" => "Data berhasil ditampilkan",
            "data" => $task
        ], 201);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task = $this->taskService->deleteTask($task);

        return response()->json([
            "status" => true,
            "message" => "Data berhasil dihapus",
            "data" => $task
        ], 201);
    }

}
