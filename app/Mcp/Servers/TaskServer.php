<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;
use App\Mcp\Tools\CreateTaskTool;
use App\Mcp\Tools\ListTasksTool;
use App\Mcp\Tools\CompleteTaskTool;
use App\Mcp\Tools\DeleteTaskTool;

class TaskServer extends Server
{
    protected string $name = 'Gerenciador de Tarefas';
    protected string $version = '1.0.0';

    /**
     * Lista de tools disponíveis.
     */
    public array $tools = [
        CreateTaskTool::class,
        ListTasksTool::class,
        CompleteTaskTool::class,
        DeleteTaskTool::class,
    ];

    // Opcional: Adicione resources/prompts aqui
    // protected function resources(): array { return []; }
}
