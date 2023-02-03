<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TodoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // Projects routes
    Route::post('/users/{user}/projects', [ProjectController::class, 'store'])->name('create_project');
    Route::get('/users/{user}/projects', [ProjectController::class, 'list'])->name('list_user_projects');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('view_project');
    // Todos routes
    Route::post('/projects/{project}/todos', [TodoController::class, 'store'])->name('create_todo');
    Route::get('/todos/{todo}', [TodoController::class, 'show'])->name('view_todo');
    Route::get('/projects/{project}/todos', [TodoController::class, 'list'])->name('list_project_todos');
    Route::patch('/todos/{todo}', [TodoController::class, 'update'])->name('update_todo');
    Route::delete('/todos/{todo}', [TodoController::class, 'destroy'])->name('delete_todo');
});
