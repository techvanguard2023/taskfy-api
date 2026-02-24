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

        $title = $task->title;
        $task->delete();

        return Response::text("🗑️ Tarefa '{$title}' de {$user->name} deletada com sucesso.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'phoneNumber' => $schema->string()
                ->required()
                ->description('O número de telefone do usuário (ex: +5521981321890)'),
            'task_id' => $schema->integer()->required()->description('ID da tarefa a deletar'),
        ];
    }
}
