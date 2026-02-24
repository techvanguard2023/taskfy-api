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
        $phoneNumber = $request->get('phoneNumber');
        $taskId = $request->get('task_id');

        if (!$phoneNumber) {
            return Response::text("❌ Erro: O número de telefone (phoneNumber) é obrigatório.");
        }

        $user = \App\Models\User::where('phone', $phoneNumber)->first();

        if (!$user) {
            return Response::text("❌ Erro: Usuário com o telefone {$phoneNumber} não encontrado.");
        }

        $task = Task::forUser($user->id)->find($taskId);

        if (!$task) {
            return Response::text("❌ Erro: Tarefa não encontrada ou não pertence a este usuário.");
        }

        if ($task->completed) {
            return Response::text("❌ Tarefa '{$task->title}' já está concluída.");
        }

        $task->update([
            'completed' => true,
            'completed_at' => now(),
        ]);

        return Response::text("✅ Tarefa '{$task->title}' de {$user->name} marcada como concluída em " . now()->format('d/m/Y H:i'));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'phoneNumber' => $schema->string()
                ->required()
                ->description('O número de telefone do usuário (ex: +5521981321890)'),
            'task_id' => $schema->integer()
                ->required()
                ->description('ID da tarefa'),
        ];
    }
}
