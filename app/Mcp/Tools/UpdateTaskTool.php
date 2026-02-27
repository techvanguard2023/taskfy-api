<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class UpdateTaskTool extends Tool
{
    protected string $description = 'Atualiza uma tarefa ou sub-tarefa existente.';

    public function handle(Request $request): Response
    {
        $phoneNumber = $request->get('phoneNumber');
        $taskId = $request->get('task_id');

        if (!$phoneNumber) {
            return Response::text("❌ Erro: O número de telefone (phoneNumber) é obrigatório.");
        }

        if (!$taskId) {
            return Response::text("❌ Erro: O ID da tarefa (task_id) é obrigatório.");
        }

        $user = User::where('phone', $phoneNumber)->first();

        if (!$user) {
            return Response::text("❌ Erro: Usuário com o telefone {$phoneNumber} não encontrado.");
        }

        $task = Task::forUser($user->id)->find($taskId);

        if (!$task) {
            return Response::text("❌ Erro: Tarefa não encontrada ou não pertence a este usuário.");
        }

        $updates = [];
        if ($request->has('title')) $updates['title'] = $request->get('title');
        if ($request->has('description')) $updates['description'] = $request->get('description');
        if ($request->has('priority')) $updates['priority'] = $request->get('priority');
        if ($request->has('parent_id')) $updates['parent_id'] = $request->get('parent_id');

        if (empty($updates)) {
            return Response::text("⚠️ Nenhuma alteração fornecida para a tarefa '{$task->title}'.");
        }

        $task->update($updates);

        return Response::text("✅ Tarefa '{$task->title}' (ID: {$task->id}) atualizada com sucesso!");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'phoneNumber' => $schema->string()
                ->required()
                ->description('O número de telefone do usuário (ex: +5521981321890)'),
            'task_id' => $schema->integer()
                ->required()
                ->description('ID da tarefa a ser atualizada'),
            'title' => $schema->string()
                ->description('Novo título da tarefa (opcional)'),
            'description' => $schema->string()
                ->description('Nova descrição (opcional)'),
            'priority' => $schema->string()
                ->enum(['low', 'medium', 'high'])
                ->description('Nova prioridade (opcional)'),
            'parent_id' => $schema->integer()
                ->description('ID da tarefa pai para converter em sub-tarefa ou mudar de pai (opcional)'),
        ];
    }
}
