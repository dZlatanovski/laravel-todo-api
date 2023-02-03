<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Models\Project;
use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Project $project, Request $request)
    {
        if ($request->user()->cannot('view', $project)) {
            abort(403);
        }
        $project->todos()->increment('view_count');
        return response($project->todos);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Project $project, StoreTodoRequest $request)
    {
        $todo = Todo::create(
            $request->validated() + [
                'user_id' => $request->user()->id,
                'project_id' => $project->id,
            ]
        );
        return response($todo, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Todo $todo, Request $request)
    {
        if ($request->user()->cannot('view', $todo)) {
            abort(403);
        }
        $todo->increment('view_count');
        return $todo;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Todo $todo, UpdateTodoRequest $request)
    {
        $todo->update($request->validated());
        return response($todo, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Todo $todo, Request $request)
    {
        if ($request->user()->cannot('delete', $todo)) {
            abort(403);
        }
        $todo->delete();
        return response(status:204);
    }
}
