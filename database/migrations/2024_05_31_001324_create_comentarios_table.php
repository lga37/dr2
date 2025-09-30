<?php

use App\Models\Video;
use App\Models\Tarefa;
use Illuminate\Support\Facades\Schema;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comentarios', function (Blueprint $table) {
            $table->id();

            $table->string('username')->nullable();

            $table->string('cod');


            $table->text('texto');
            $table->unsignedInteger('likes')->nullable();
            $table->unsignedInteger('dislikes')->nullable();
            $table->timestamp('dt');
            $table->float('tox')->nullable();

            $table->timestamps();

            $table->foreignIdFor(Video::class);
            $table->foreignIdFor(Tarefa::class);


            $table->unique(['cod', 'tarefa_id', 'video_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comentarios');
    }
};
