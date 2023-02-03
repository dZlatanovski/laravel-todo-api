<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_create()
    {
        $user = User::factory()->create();

        $requestData = [
            'name' => 'Test Project'
        ];
        $uri = route('create_project', [
            'user' => $user->id
        ]);
        $response = $this->actingAs($user)->postJson($uri, $requestData);

        $response->assertCreated()->assertJson($requestData);
        $this->assertDatabaseHas('projects', [
            'name' => $requestData['name'],
            'user_id' => $user->id
        ]);
    }

    public function test_project_create_invalid_data()
    {
        $user = User::factory()->create();

        $requestData = [];
        $uri = route('create_project', [
            'user' => $user->id
        ]);
        $response = $this->actingAs($user)->postJson($uri, $requestData);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('projects', [
            'user_id' => $user->id
        ]);
    }

    public function test_missing_user_project_create()
    {
        $user = User::factory()->create();

        $requestData = [
            'name' => 'Test Project'
        ];
        $uri = route('create_project', [
            'user' => User::all()->count() + 1
        ]);
        $response = $this->actingAs($user)->postJson($uri, $requestData);

        $response->assertNotFound()->assertJsonMissing($requestData);
        $this->assertDatabaseMissing('projects', [
            'name' => $requestData['name'],
            'user_id' => $user->id
        ]);
    }

    public function test_unauthenticated_project_create()
    {
        $user = User::factory()->create();

        $requestData = [
            'name' => 'Nonexisting Test Project'
        ];
        $uri = route('create_project', [
            'user' => $user->id
        ]);
        $response = $this->postJson($uri, $requestData);

        $response->assertUnauthorized();
        $this->assertDatabaseMissing('projects', [
            'name' => $requestData['name'],
            'user_id' => $user->id
        ]);
    }

    public function test_unauthorized_project_create()
    {
        $users = User::factory()->count(2)->create();

        $requestData = [
            'name' => 'Nonexisting Test Project'
        ];
        $uri = route('create_project', [
            'user' => $users[0]->id
        ]);
        $response = $this->actingAs($users[1])->postJson($uri, $requestData);

        $response->assertForbidden();
        $this->assertDatabaseMissing('projects', [
            'name' => $requestData['name'],
            'user_id' => $users[0]->id
        ]);
    }

    public function test_project_view()
    {
        $user = User::factory()
                            ->has(Project::factory())
                            ->create();
        $project = $user->projects()->first();
        $uri = route('view_project', [
            'project' => $project->id
        ]);
        $response = $this->actingAs($user)->getJson($uri);

        $response->assertOk()->assertJson($project->toArray());
    }

    public function test_missing_project_view()
    {
        $user = User::factory()->create();
        $uri = route('view_project', [
            'project' => Project::all()->count() + 1
        ]);
        $response = $this->actingAs($user)->getJson($uri);

        $response->assertNotFound();
    }

    public function test_unauthenticated_project_view()
    {
        $user = User::factory()
                            ->has(Project::factory())
                            ->create();
        $project = $user->projects()->first();
        $uri = route('view_project', [
            'project' => $project->id
        ]);
        $response = $this->getJson($uri);

        $response->assertUnauthorized()->assertJsonMissing($project->toArray());
    }

    public function test_unauthorized_project_view()
    {
        $users = User::factory()
                                ->count(2)
                                ->has(Project::factory())
                                ->create();
        $project = $users[0]->projects()->first();
        $uri = route('view_project', [
            'project' => $project->id
        ]);
        $response = $this->actingAs($users[1])->getJson($uri);

        $response->assertForbidden()->assertJsonMissing($project->toArray());
    }

    public function test_user_projects_list()
    {
        $user = User::factory()
                                ->hasProjects(2)
                                ->create();
        $uri = route('list_user_projects', [
            'user' => $user->id
        ]);
        $response = $this->actingAs($user)->getJson($uri);

        $response->assertOk()->assertJson($user->projects->toArray());
    }

    public function test_missing_user_projects_list()
    {
        $user = User::factory()
                                ->hasProjects(2)
                                ->create();
        $uri = route('list_user_projects', [
            'user' => User::all()->count() + 1
        ]);
        $response = $this->actingAs($user)->getJson($uri);

        $response->assertNotFound()->assertJsonMissing($user->projects->toArray());
    }

    public function test_unauthenticated_user_projects_list()
    {
        $user = User::factory()
                                ->hasProjects(2)
                                ->create();
        $uri = route('list_user_projects', [
            'user' => User::all()->count() + 1
        ]);
        $response = $this->getJson($uri);

        $response->assertUnauthorized()->assertJsonMissing($user->projects->toArray());
    }

    public function test_unauthorized_user_projects_list()
    {
        $users = User::factory()
                                ->count(2)
                                ->hasProjects(2)
                                ->create();
        $uri = route('list_user_projects', [
            'user' => $users[0]->id
        ]);
        $response = $this->actingAs($users[1])->getJson($uri);

        $response->assertForbidden()->assertJsonMissing($users[0]->projects->toArray());
    }
}
