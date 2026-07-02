<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,review,done,blocked',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'required|date',
            'estimate_hours' => 'required|integer',
            'assigned_to' => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required' => 'Project ID wajib diisi.',
            'project_id.exists' => 'Project ID tidak ditemukan.',
            'title.required' => 'Title wajib diisi.',
            'title.max' => 'Title maksimal 255 karakter.',
            'description.required' => 'Description wajib diisi.',
            'description.max' => 'Description maksimal 255 karakter.',
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status tidak valid.',
            'priority.required' => 'Priority wajib diisi.',
            'priority.in' => 'Priority tidak valid.',
            'due_date.required' => 'Due date wajib diisi.',
            'due_date.date' => 'Due date tidak valid.',
            'estimate_hours.required' => 'Estimate hours wajib diisi.',
            'estimate_hours.numeric' => 'Estimate hours tidak valid.',
            'assigned_to.required' => 'Assigned to wajib diisi.',
            'assigned_to.exists' => 'Assigned to tidak ditemukan.',
            'created_by.required' => 'Created by wajib diisi.',
            'created_by.exists' => 'Created by tidak ditemukan.',
        ];
    }
}
