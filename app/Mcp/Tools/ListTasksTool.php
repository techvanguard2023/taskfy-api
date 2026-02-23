<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListTasksTool extends Tool
{
    protected string $description = 'Lista tarefas pendentes ou todas do usuário, com filtros opcionais.';

    public function handle(Request $request): Response
    {
        $userId = $request->user()?->id;
        $query = Task::forUser($userId ?? 0);

        $status = $request->get('status');
        if ($status) {
            $query->where('completed', $status === 'completed');
        }

        $tasks = $query->orderBy('created_at', 'desc')->get();

        $output = "📋 **Suas tarefas** ({$tasks->count()} total):\n\n";
        foreach ($tasks as $task) {
            $statusEmoji = $task->completed ? '✅' : '⏳';
            $output .= "- {$statusEmoji} **{$task->title}** ({$task->priority})\n";
            if ($task->description) $output .= "  {$task->description}\n";
            $output .= "\n";
        }

        return Response::text($output);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()->enum(['completed', 'pending'])->description('Filtrar por status'),
        ];
    }
}
