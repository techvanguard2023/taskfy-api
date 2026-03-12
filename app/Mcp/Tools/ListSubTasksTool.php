<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListSubTasksTool extends Tool
{
    protected string $description = 'Lista as sub-tarefas de uma tarefa principal específica.';

    public function handle(Request $request): Response
    {
        $phoneNumber = $request->get('phoneNumber');
        $taskId = $request->get('taskId');

        if (!$phoneNumber) {
            return Response::text("❌ Erro: O número de telefone (phoneNumber) é obrigatório.");
        }

        if (!$taskId) {
            return Response::text("❌ Erro: O ID da tarefa principal (taskId) é obrigatório.");
        }

        $user = User::where('phone', $phoneNumber)->first();

        if (!$user) {
            return Response::text("❌ Erro: Usuário com o telefone {$phoneNumber} não encontrado.");
        }

        $parentTask = Task::forUser($user->id)->find($taskId);

        if (!$parentTask) {
            return Response::text("❌ Erro: Tarefa principal com ID {$taskId} não encontrada ou não pertence ao usuário.");
        }

        $query = Task::forUser($user->id)->where('parent_id', $taskId);

        $status = $request->get('status');
        if ($status) {
            $query->where('completed', $status === 'completed');
        }

        $tasks = $query->with('children')->orderBy('created_at', 'desc')->get();

        if ($tasks->isEmpty()) {
            return Response::text("📋 A tarefa principal **{$parentTask->title}** (ID: {$parentTask->id}) não possui sub-tarefas.");
        }

        $output = "📋 **Sub-tarefas de '{$parentTask->title}'** (ID: {$parentTask->id}) ({$tasks->count()} total):\n\n";
        foreach ($tasks as $task) {
            $statusEmoji = $task->completed ? '✅' : '⏳';
            $output .= "- {$statusEmoji} [ID: {$task->id}] **{$task->title}** ({$task->priority})\n";
            if ($task->description)
                $output .= "  {$task->description}\n";

            if ($task->children->count() > 0) {
                $output .= "  ↳ 📋 Possui {$task->children->count()} sub-tarefas/itens de nível inferior.\n";
            }
            $output .= "\n";
        }

        return Response::text($output);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'phoneNumber' => $schema->string()
            ->required()
            ->description('O número de telefone do usuário (ex: +5521981321890)'),
            'taskId' => $schema->integer()
            ->required()
            ->description('ID da tarefa principal para listar as sub-tarefas associadas'),
            'status' => $schema->string()
            ->enum(['completed', 'pending'])
            ->description('Filtrar por status (opcional: lista todas se omitido)'),
            'source' => $schema->string()
            ->description('Filtrar por fonte (opcional)'),
            'created_at' => $schema->string()
            ->description('Filtrar por data de criação (opcional)'),
            'updated_at' => $schema->string()
            ->description('Filtrar por data de atualização (opcional)'),
        ];
    }
}
