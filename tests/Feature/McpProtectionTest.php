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
        // First request to verify initial state or trigger any initialization
        $response = $this->getJson('/mcp/taskfy');

        // It should return 401 Unauthorized
        $response->assertStatus(401);
    }

    /**
     * Test that MCP route is accessible with Sanctum authentication.
     */
    public function test_mcp_route_is_accessible_with_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/mcp/taskfy');

        // It should NOT return 401. 
        // Depending on MCP implementation, it might return 200 or 405 (if POST is required) or something else, 
        // but not 401.
        $this->assertNotEquals(401, $response->getStatusCode());
    }
}
