<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200)
                 ->assertJsonCount(4);
    }

    public function test_can_create_user(): void
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'phone' => '+5521912345678',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/users', $userData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['email' => 'newuser@example.com']);

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    public function test_can_show_user(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_can_update_user(): void
    {
        $user = User::factory()->create();
        $updateData = ['name' => 'Updated Name'];

        $response = $this->putJson("/api/v1/users/{$user->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name'
        ]);
    }

    public function test_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_can_find_user_by_phone(): void
    {
        $user = User::factory()->create(['phone' => '+5521977777777']);

        $response = $this->getJson("/api/v1/users/phone/" . urlencode($user->phone));

        $response->assertStatus(200)
                 ->assertJsonFragment(['phone' => $user->phone]);
    }

    public function test_unauthenticated_user_cannot_access_users(): void
    {
        auth()->logout();

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(401);
    }
}
