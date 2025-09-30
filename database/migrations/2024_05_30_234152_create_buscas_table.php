<?php

use App\Models\Tarefa;
use App\Models\User;
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
        Schema::create('buscas', function (Blueprint $table) {
            $table->id();
            $table->string('q');
            $table->string('slug');

            #$table->timestamp('added_by_url_at')->nullable();

            #$table->boolean('parse')->default(false);

            $table->foreignIdFor(Tarefa::class)->nullable();

            $table->timestamps();

            $table->unique(['slug', 'tarefa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buscas');
    }
};
