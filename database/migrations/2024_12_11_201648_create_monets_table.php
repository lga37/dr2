<?php

use App\Models\Canal;
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
        Schema::create('monets', function (Blueprint $table) {
            $table->id();

                   
            $table->unsignedInteger('vlr')->nullable();

            $table->datetime('dt');
            $table->text('obs')->nullable();

            
            $table->foreignIdFor(Canal::class);

            $table->unique(['canal_id','dt']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monets');
    }
};
