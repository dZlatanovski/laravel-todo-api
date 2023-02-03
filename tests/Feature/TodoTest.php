<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TodoTest extends TestCase
{
    use RefreshDatabase;

    public function test_todo_create()
    {
        $user = User::factory()
                            ->has(Project::factory())
                            ->create();
        $requestData = [
            'description' => 'Test Todo'
        ];
        $project = $user->projects()->first();
        $uri = route('create_todo', [
            'project' => $project->id
        ]);
        $response = $this->actingAs($user)->postJson($uri, $requestData);

        $response->assertCreated()->assertJson($requestData);
        $this->assertDatabaseHas('todos', [
            'description' => $requestData['description'],
            'user_id' => $user->id
        ]);
    }

    public function test_todo_create_invalid_data()
    {
        $user = User::factory()
                            ->has(Project::factory())
                            ->create();
        $requestData = [];
        $project = $user->projects()->first();
        $uri = route('create_todo', [
            'project' => $project->id
        ]);
        $response = $this->actingAs($user)->postJson($uri, $requestData);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('todos', [
            'user_id' => $user->id,
            'project_id' => $project->id
        ]);
    }

    public function test_unauthenticated_todo_create()
    {
        $user = User::factory()
                            ->has(Project::factory())
                            ->create();
        $requestData = [
            'description' => 'Test Todo'
        ];
        $project = $user->projects()->first();
        $uri = route('create_todo', [
            'project' => $project->id
        ]);
        $response = $this->postJson($uri, $requestData);

        $response->assertUnauthorized()->assertJsonMissing($requestData);
        $this->assertDatabaseMissing('todos', [
            'description' => $requestData['description'],
            'user_id' => $user->id
        ]);
    }

    public function test_unauthorized_todo_create()
    {
        $users = User::factory()
                                ->count(2)
                                ->has(Project::factory())
                                ->create();
        $requestData = [
            'description' => 'Test Todo'
        ];
        $project = $users[0]->projects()->first();
        $uri = route('create_todo', [
            'project' => $project->id
        ]);
        $response = $this->actingAs($users[1])->postJson($uri, $requestData);

        $response->assertForbidden()->assertJsonMissing($requestData);
        $this->assertDatabaseMissing('todos', [
            'description' => $requestData['description'],
            'user_id' => $users[0]->id
        ]);
    }

    public function test_todo_view()
    {
        $user = User::factory()->create();
        $project = Project::factory()
                                        ->for($user)
                                        ->create();
        $todo = Todo::factory()
                                ->for($user)
                                ->for($project)
                                ->create();
        $uri = route('view_todo', [
            'todo' => $todo->id
        ]);
        $response = $this->actingAs($user)->getJson($uri);

        $todo->refresh();
        $response->assertOk()->assertJson($todo->toArray());
    }

    public function test_missing_todo_view()
    {
        $user = User::factory()
                                ->create();
        $uri = route('view_todo', [
            'todo' => Todo::all()->count() + 1
        ]);
        $response = $this->actingAs($user)->getJson($uri);

        $response->assertNotFound();
    }

    public function test_unauthenticated_todo_view()
    {
        $user = User::factory()->create();
        $project = Project::factory()
                                        ->for($user)
                                        ->create();
        $todo = Todo::factory()
                                ->for($user)
                                ->for($project)
                                ->create();
        $uri = route('view_todo', [
            'todo' => $todo->id
        ]);
        $response = $this->getJson($uri);

        $response->assertUnauthorized()->assertJsonMissing($todo->toArray());
    }

    public function test_unauthorized_todo_view()
    {
        $users = User::factory()
                                ->count(2)
                                ->has(Project::factory())
                                ->create();
        $todo = Todo::factory()
                                ->for($users[0])
                                ->for($users[0]->projects()->first())
                                ->create();
        $uri = route('view_todo', [
            'todo' => $todo->id
        ]);
        $response = $this->actingAs($users[1])->getJson($uri);

        $response->assertForbidden()->assertJsonMissing($todo->toArray());
    }

    public function test_project_todos_list()
    {
        $user = User::factory()->create();
        $project = Project::factory()
                                        ->for($user)
                                        ->create();
        $todos = Todo::factory()
                                ->count(3)
                                ->for($user)
                                ->for($project)
                                ->create();
        $uri = route('list_project_todos', [
            'project' => $project->id
        ]);
        $response = $this->actingAs($user)->getJson($uri);

        $todos = $user->todos;
        $response->assertOk()->assertJson($todos->toArray());
    }

    public function test_missing_project_todos_list()
    {
        $user = User::factory()->create();
        $uri = route('list_project_todos', [
            'project' => Project::all()->count() + 1
        ]);
        $response = $this->actingAs($user)->getJson($uri);

        $response->assertNotFound();
    }

    public function test_unauthenticated_project_todos_list()
    {
        $user = User::factory()->create();
        $project = Project::factory()
                                        ->for($user)
                                        ->create();
        $todos = Todo::factory()
                                ->count(3)
                                ->for($user)
                                ->for($project)
                                ->create();
        $uri = route('list_project_todos', [
            'project' => $project->id
        ]);
        $response = $this->getJson($uri);

        $response->assertUnauthorized()->assertJsonMissing($todos->toArray());
    }

    public function test_unauthorized_project_todos_list()
    {
        $users = User::factory()
                                ->count(2)
                                ->create();
        $project = Project::factory()
                                        ->for($users[0])
                                        ->create();
        $todos = Todo::factory()
                                ->count(3)
                                ->for($users[0])
                                ->for($project)
                                ->create();
        $uri = route('list_project_todos', [
            'project' => $project->id
        ]);
        $response = $this->actingAs($users[1])->getJson($uri);

        $response->assertForbidden()->assertJsonMissing($todos->toArray());
    }

    public function test_todo_mark_done()
    {
        $user = User::factory()->create();
        $project = Project::factory()
                                        ->for($user)
                                        ->create();
        $todo = Todo::factory()
                                ->for($user)
                                ->for($project)
                                ->create(['state' => 'Todo']);
        $requestData = [
            'state' => 'Done'
        ];
        $uri = route('update_todo', [
            'todo' => $todo->id
        ]);
        $response = $this->actingAs($user)->patchJson($uri, $requestData);

        $response->assertOk()->assertJson($requestData);
        $todo->refresh();
        $this->assertEquals('Done', $todo->state);
    }

    public function test_missing_todo_mark_done()
    {
        $user = User::factory()->has(Project::factory())->create();
        $requestData = [
            'state' => 'Done'
        ];
        $uri = route('update_todo', [
            'todo' => Todo::all()->count() + 1
        ]);
        $response = $this->actingAs($user)->patchJson($uri, $requestData);
        $response->assertNotFound()->assertJsonMissing($requestData);
    }

    public function test_todo_mark_done_invalid_data()
    {
        $user = User::factory()->create();
        $project = Project::factory()
                                        ->for($user)
                                        ->create();
        $todo = Todo::factory()
                                ->for($user)
                                ->for($project)
                                ->create(['state' => 'Todo']);
        $requestData = [
            'state' => 'invalid'
        ];
        $uri = route('update_todo', [
            'todo' => $todo->id
        ]);
        $response = $this->actingAs($user)->patchJson($uri, $requestData);

        $response->assertStatus(422)->assertJsonMissing($requestData);
        $todo->refresh();
        $this->assertNotEquals('Done', $todo->state);
    }

    public function test_unauthenticated_todo_mark_done()
    {
        $user = User::factory()->create();
        $project = Project::factory()
                                        ->for($user)
                                        ->create();
        $todo = Todo::factory()
                                ->for($user)
                                ->for($project)
                                ->create(['state' => 'Todo']);
        $requestData = [
            'state' => 'Done'
        ];
        $uri = route('update_todo', [
            'todo' => $todo->id
        ]);
        $response = $this->patchJson($uri, $requestData);

        $response->assertUnauthorized()->assertJsonMissing($requestData);
        $todo->refresh();
        $this->assertNotEquals('Done', $todo->state);
    }

    public function test_unauthorized_todo_mark_done()
    {
        $users = User::factory()->count(2)->has(Project::factory())->create();
        $todo = Todo::factory()
                                ->for($users[0])
                                ->for($users[0]->projects()->first())
                                ->create(['state' => 'Todo']);
        $requestData = [
            'state' => 'Done'
        ];
        $uri = route('update_todo', [
            'todo' => $todo->id
        ]);
        $response = $this->actingAs($users[1])->patchJson($uri, $requestData);

        $response->assertForbidden()->assertJsonMissing($requestData);
        $todo->refresh();
        $this->assertNotEquals('Done', $todo->state);
    }

    public function test_todo_delete()
    {
        $user = User::factory()->create();
        $project = Project::factory()
                                        ->for($user)
                                        ->create();
        $todo = Todo::factory()
                                ->for($user)
                                ->for($project)
                                ->create();
        $uri = route('delete_todo', [
            'todo' => $todo->id
        ]);
        $response = $this->actingAs($user)->deleteJson($uri);

        $response->assertNoContent();
        $this->assertDatabaseMissing('todos', [
            'id' => $todo->id
        ]);
    }

    public function test_missing_todo_delete()
    {
        $user = User::factory()->has(Project::factory())->create();
        $uri = route('delete_todo', [
            'todo' => Todo::all()->count() + 1
        ]);
        $response = $this->actingAs($user)->deleteJson($uri);

        $response->assertNotFound();
    }

    public function test_unauthenticated_todo_delete()
    {
        $user = User::factory()->create();
        $project = Project::factory()
                                        ->for($user)
                                        ->create();
        $todo = Todo::factory()
                                ->for($user)
                                ->for($project)
                                ->create();
        $uri = route('delete_todo', [
            'todo' => $todo->id
        ]);
        $response = $this->deleteJson($uri);

        $response->assertUnauthorized();
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id
        ]);
    }

    public function test_unauthorized_todo_delete()
    {
        $users = User::factory()->count(2)->has(Project::factory())->create();
        $todo = Todo::factory()
                                ->for($users[0])
                                ->for($users[0]->projects()->first())
                                ->create();
        $uri = route('delete_todo', [
            'todo' => $todo->id
        ]);
        $response = $this->actingAs($users[1])->deleteJson($uri);

        $response->assertForbidden();
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id
        ]);
    }

    public function test_todo_view_count_track()
    {
        $user = User::factory()->create();
        $project = Project::factory()
                                        ->for($user)
                                        ->create();
        $todos = Todo::factory()
                                ->count(3)
                                ->for($user)
                                ->for($project)
                                ->create();
        $viewTodoUri = route('view_todo', [
            'todo' => $todos[0]->id
        ]);
        $listTodosUri = route('list_project_todos', [
            'project' => $project->id
        ]);

        $viewTodoResponse = $this->actingAs($user)->getJson($viewTodoUri);
        $viewTodoResponse->assertJson(['view_count' => $todos[0]->view_count + 1]);

        $updatedTodo = Todo::where('id', $todos[0]->id)->first();
        // Check if the todo we requested has incremented view_count
        $this->assertEquals($updatedTodo->view_count, $todos[0]->view_count + 1);

        // Refresh the todos (we have dirty data because one of them was updated)
        $todos = Todo::where('project_id', $project->id)->get();

        $listTodosResponse = $this->actingAs($user)->getJson($listTodosUri);
        $updatedTodos = Todo::where('project_id', $project->id)->get();

        // Check if all the todos that we listed have incremented view_count
        foreach($updatedTodos as $index => $todo) {
            $this->assertEquals($todo->view_count, $todos[$index]->view_count + 1);
        }
    }
}
