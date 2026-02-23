<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class DeleteTaskTool extends Tool
{
    protected string $description = 'Deleta uma tarefa pelo ID (irreversível).';

    public function handle(Request $request): Response
    {
        $userId = $request->user()?->id;
        $taskId = $request->get('task_id');

        $task = Task::forUser($userId ?? 0)->findOrFail($taskId);
        $title = $task->title;
        $task->delete();

        return Response::text("🗑️ Tarefa '{$title}' deletada com sucesso.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'task_id' => $schema->integer()->required()->description('ID da tarefa a deletar'),
        ];
    }
}
