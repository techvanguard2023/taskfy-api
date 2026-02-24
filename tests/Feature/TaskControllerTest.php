<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_own_tasks(): void
    {
        Task::factory()->create(['user_id' => $this->user->id]);
        Task::factory()->create(['user_id' => User::factory()->create()->id]);

        $response = $this->getJson('/api/v1/tasks');

        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    public function test_can_create_task(): void
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'priority' => 'high',
        ];

        $response = $this->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'New Task']);

        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
            'user_id' => $this->user->id
        ]);
    }

    public function test_can_show_own_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => $task->title]);
    }

    public function test_cannot_show_other_users_task(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    public function test_can_update_own_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $updateData = ['title' => 'Updated Title', 'completed' => true];

        $response = $this->putJson("/api/v1/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Updated Title', 'completed' => true]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'completed' => true,
        ]);
        
        $this->assertNotNull(Task::find($task->id)->completed_at);
    }

    public function test_can_delete_own_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
    
    public function test_cannot_delete_other_users_task(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(403);
    }
}
