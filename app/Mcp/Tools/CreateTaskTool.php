<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use App\Models\User;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Illuminate\Contracts\JsonSchema\JsonSchema; // <- CORRIGIDO: interface de contrato

class CreateTaskTool extends Tool {
    protected string $description = 'Cria uma nova tarefa com título, descrição e prioridade.';

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
        
        $task = Task::create([
            'user_id' => $user->id,
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'priority' => $request->get('priority', 'medium'),
            'parent_id' => $request->get('parent_id'),
        ]);
        
        return Response::text("✅ Tarefa criada para {$user->name}! ID: {$task->id} - {$task->title}");
    }


    public function schema(JsonSchema $schema): array
    {
        return [
            'phoneNumber' => $schema->string()
                ->required()
                ->description('O número de telefone do usuário (ex: +5521981321890)'),
            'title' => $schema->string()
                ->required()
                ->description('Título da tarefa'),
            'description' => $schema->string()
                ->description('Descrição opcional'),
            'priority' => $schema->string()
                ->enum(['low', 'medium', 'high'])
                ->default('medium')
                ->description('Prioridade da tarefa'),
            'parent_id' => $schema->integer()
                ->description('ID da tarefa pai (opcional, para criar uma sub-tarefa/item de lista)'),
        ];
    }

}
