<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Task::forUser(Auth::id());

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } else {
            $query->whereNull('parent_id');
        }

        $tasks = $query->with('children')->get();
        return response()->json($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'completed' => 'nullable|boolean',
            'parent_id' => 'nullable|exists:tasks,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('parent_id')) {
            $parentTask = Task::find($request->parent_id);
            if (!$parentTask || $parentTask->user_id !== Auth::id()) {
                return response()->json(['errors' => ['parent_id' => ['Tarefa pai inválida ou não pertence ao usuário.']]], 422);
            }
        }

        $task = Task::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'completed' => $request->completed ?? false,
            'completed_at' => ($request->completed ?? false) ? now() : null,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $this->authorizeOwnership($task);
        return response()->json($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $this->authorizeOwnership($task);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|required|in:low,medium,high',
            'completed' => 'sometimes|required|boolean',
            'parent_id' => 'nullable|exists:tasks,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('parent_id') && $request->parent_id !== null) {
            $parentTask = Task::find($request->parent_id);
            if (!$parentTask || $parentTask->user_id !== Auth::id()) {
                return response()->json(['errors' => ['parent_id' => ['Tarefa pai inválida ou não pertence ao usuário.']]], 422);
            }
        }

        $data = $request->only(['title', 'description', 'priority', 'completed', 'parent_id']);

        if ($request->has('completed')) {
            $data['completed_at'] = $request->completed ? now() : null;
        }

        $task->update($data);

        // Cascade completion to children if the parent is marked as completed
        if ($request->has('completed') && $request->completed === true) {
            Task::where('parent_id', $task->id)->update([
                'completed' => true,
                'completed_at' => now(),
            ]);
        }

        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorizeOwnership($task);
        $task->delete();

        return response()->json(['message' => 'Tarefa deletada com sucesso.']);
    }

    /**
     * Authorize that the authenticated user owns the task.
     */
    protected function authorizeOwnership(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
