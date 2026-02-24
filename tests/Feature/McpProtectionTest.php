<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class McpProtectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that MCP route requires authentication.
     */
    public function test_mcp_route_requires_authentication(): void
    {
        $response = $this->postJson('/mcp/taskfy', [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'create-task-tool',
                'arguments' => [
                    'phoneNumber' => '+5521981321890',
                    'title' => 'Test Task',
                    'description' => 'Test Description',
                    'priority' => 'medium'
                ]
            ],
            'id' => 1
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test that MCP route is accessible with Sanctum authentication and correct phone.
     */
    public function test_mcp_route_creates_task_with_valid_phone(): void
    {
        $user = User::factory()->create([
            'phone' => '+5521981321890'
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/mcp/taskfy', [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'create-task-tool',
                'arguments' => [
                    'phoneNumber' => '+5521981321890',
                    'title' => 'Test Task',
                    'description' => 'Test Description',
                    'priority' => 'medium'
                ]
            ],
            'id' => 1
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'text' => "✅ Tarefa criada para {$user->name}! ID: 1 - Test Task"
        ]);
        
        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Test Task'
        ]);
    }

    /**
     * Test that MCP route returns error with invalid phone.
     */
    public function test_mcp_route_returns_error_with_invalid_phone(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/mcp/taskfy', [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'create-task-tool',
                'arguments' => [
                    'phoneNumber' => '+5500000000000',
                    'title' => 'Test Task',
                    'description' => 'Test Description',
                    'priority' => 'medium'
                ]
            ],
            'id' => 1
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'text' => "❌ Erro: Usuário com o telefone +5500000000000 não encontrado."
        ]);
    }
}
