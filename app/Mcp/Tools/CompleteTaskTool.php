<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CompleteTaskTool extends Tool
{
    protected string $description = 'Marca uma tarefa como concluída pelo ID.';

    public function handle(Request $request): Response
    {
        $userId = $request->user()?->id;
        $taskId = $request->get('task_id');

        $task = Task::forUser($userId ?? 0)->findOrFail($taskId);
        if ($task->completed) {
            return Response::text("❌ Tarefa '{$task->title}' já está concluída.");
        }

        $task->update([
            'completed' => true,
            'completed_at' => now(),
        ]);

        return Response::text("✅ Tarefa '{$task->title}' marcada como concluída em " . now()->format('d/m/Y H:i'));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'task_id' => $schema->integer()->required()->description('ID da tarefa'),
        ];
    }
}
