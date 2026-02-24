<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListTasksTool extends Tool
{
    protected string $description = 'Lista tarefas pendentes ou todas do usuário, com filtros opcionais.';

    public function handle(Request $request): Response
    {
        $phoneNumber = $request->get('phoneNumber');

        if (!$phoneNumber) {
            return Response::text("❌ Erro: O número de telefone (phoneNumber) é obrigatório.");
        }

        $user = User::where('phone', $phoneNumber)->first();

        if (!$user) {
            return Response::text("❌ Erro: Usuário com o telefone {$phoneNumber} não encontrado.");
        }

        $query = Task::forUser($user->id);

        $status = $request->get('status');
        if ($status) {
            $query->where('completed', $status === 'completed');
        }

        $parentId = $request->get('parent_id');
        if ($parentId) {
            $query->where('parent_id', $parentId);
        } else {
            $query->whereNull('parent_id');
        }

        $tasks = $query->with('children')->orderBy('created_at', 'desc')->get();

        $output = "📋 **Tarefas de {$user->name}** ({$tasks->count()} total):\n\n";
        foreach ($tasks as $task) {
            $statusEmoji = $task->completed ? '✅' : '⏳';
            $output .= "- {$statusEmoji} [ID: {$task->id}] **{$task->title}** ({$task->priority})\n";
            if ($task->description) $output .= "  {$task->description}\n";
            
            if (!$parentId && $task->children->count() > 0) {
                $output .= "  ↳ 📋 Possui {$task->children->count()} sub-tarefas/itens.\n";
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
            'status' => $schema->string()
                ->enum(['completed', 'pending'])
                ->description('Filtrar por status (opcional: lista todas se omitido)'),
            'parent_id' => $schema->integer()
                ->description('Filtrar pelos itens de uma lista específica pelo ID do pai (opcional)'),
        ];
    }
}
