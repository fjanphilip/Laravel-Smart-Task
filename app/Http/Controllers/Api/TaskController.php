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

    public function index(): JsonResponse
    {
        $task = $this->taskService->getAllTasks();

        return response()->json([
            "status" => true,
            "message" => "Data berhasil ditampilkan",
            "data" => $task
        ], 201);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->validated());

        return response()->json([
            "status" => true,
            "message" => "Data berhasil ditambahkan",
            "data" => $task
        ], 201);
    }

    public function update(Task $task, UpdateTaskRequest $request): JsonResponse
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json([
                "status" => false,
                "message" => "Anda tidak memiliki akses untuk mengubah ini"
            ], 403);
        }

        $task = $this->taskService->updateTask($task, $request->validated());

        return response()->json([
            "status" => true,
            "message" => "Data berhasil diupdate",
            "data" => $task
        ], 201);
    }

    public function show(Task $task): JsonResponse
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json([
                "status" => false,
                "message" => "Anda tidak memiliki akses untuk melihat ini"
            ], 403);
        }

        $task = $this->taskService->getTaskById($task->id);

        return response()->json([
            "status" => true,
            "message" => "Data berhasil ditampilkan",
            "data" => $task
        ], 201);
    }

    public function destroy(Task $task): JsonResponse
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json([
                "status" => false,
                "message" => "Anda tidak memiliki akses untuk menghapus ini"
            ], 403);
        }

        $task = $this->taskService->deleteTask($task);

        return response()->json([
            "status" => true,
            "message" => "Data berhasil dihapus",
            "data" => $task
        ], 201);
    }

}
