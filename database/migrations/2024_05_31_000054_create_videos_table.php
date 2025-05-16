<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Busca;
use App\Models\Canal;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();

            $table->string('cod')->unique();
            
            $table->string('nome')->nullable();
            $table->string('slug')->nullable(); #slug do nome

            $table->text('desc')->nullable();
            $table->text('caption')->nullable();
            #$table->json('hashtags')->nullable();
            $table->json('keywords')->nullable(); #essa aqui eu pego da meta
            $table->boolean('parse')->default(false);

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

            $table->foreignIdFor(Canal::class)->nullable();
            $table->foreignIdFor(Busca::class);


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
