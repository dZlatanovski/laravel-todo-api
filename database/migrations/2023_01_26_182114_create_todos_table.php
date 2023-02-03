<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->enum('state', ['Todo', 'Done'])->default('Todo');
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps();

            $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();
            $table->foreignId('project_id')
                    ->constrained()
                    ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('todos');
    }
};
