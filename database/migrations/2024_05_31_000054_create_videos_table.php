<?php

use App\Models\Busca;
use App\Models\Canal;
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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();

            $table->string('cod'); #id do youtube

            $table->timestamp('del_at')->nullable();


            $table->string('nome')->nullable();
            $table->string('slug')->nullable(); #slug do nome

            $table->text('desc')->nullable();
            $table->text('caption')->nullable();
            
            #$table->json('keywords')->nullable(); #essa aqui eu pego da meta
            $table->json('hashtags')->nullable(); 

            $table->float('nlp1')->nullable();
            $table->float('nlp2')->nullable();
            $table->text('gpt')->nullable();


            $table->unsignedInteger('comments')->nullable();
            $table->unsignedInteger('likes')->nullable();
            $table->unsignedInteger('dislikes')->nullable();
            $table->unsignedInteger('views')->nullable();
            $table->unsignedInteger('favorites')->nullable();

            $table->unsignedInteger('duration')->nullable();
            $table->unsignedInteger('categ_id')->nullable();
            $table->string('lang')->nullable();
            $table->datetime('dt')->nullable();

            $table->foreignIdFor(Busca::class)->nullable();

            $table->foreignIdFor(Canal::class);
            $table->foreignIdFor(Tarefa::class);

            $table->unique(['cod', 'canal_id', 'tarefa_id']);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
