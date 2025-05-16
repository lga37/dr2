<?php

use App\Models\Busca;
use App\Models\Video;
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
        Schema::create('canals', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('slug')->nullable(); #slug do nome
            $table->string('cod')->unique();
            $table->string('youtube_id')->nullable();
            $table->text('desc')->nullable();
            $table->json('links')->nullable();
            $table->boolean('parse')->default(false);
            $table->boolean('verificado')->default(false);
            $table->bigInteger('inscritos')->nullable();
            $table->bigInteger('views')->nullable();
            $table->date('dt')->nullable();
            $table->string('local')->nullable();

            $table->string('categ')->nullable();
            $table->bigInteger('videos')->nullable();
            $table->char('score',1)->nullable();
            $table->float('min')->nullable();
            $table->float('max')->nullable();
            $table->float('engagement')->nullable();
            $table->float('frequency')->nullable();
            $table->float('length')->nullable();
            

            #$table->foreignIdFor(Video::class);
            $table->foreignIdFor(Busca::class);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canals');
    }
};
