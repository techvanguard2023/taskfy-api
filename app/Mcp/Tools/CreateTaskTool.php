<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Illuminate\Contracts\JsonSchema\JsonSchema; // <- CORRIGIDO: interface de contrato

class CreateTaskTool extends Tool {
    protected string $description = 'Cria uma nova tarefa com título, descrição e prioridade.';

    public function handle(Request $request): Response {
        $task = Task::create([
            'user_id' => $request->user()?->id,
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'priority' => $request->get('priority', 'medium'),
        ]);
        return Response::text("✅ Tarefa criada! ID: {$task->id} - {$task->title}");
    }

    public function schema(JsonSchema $schema): array {
        return [ // <- CORRIGIDO: array com objetos Type fluentes
            'title' => $schema->string()
                ->required()
                ->description('Título da tarefa'),
            'description' => $schema->string()
                ->description('Descrição opcional'),
            'priority' => $schema->enum(['low', 'medium', 'high'])
                ->default('medium')
                ->description('Prioridade da tarefa'),
        ];
    }
}
