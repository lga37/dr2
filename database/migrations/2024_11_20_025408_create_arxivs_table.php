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
        Schema::create('arxivs', function (Blueprint $table) {
            $table->id();

                   
            $table->unsignedInteger('views')->nullable();
            $table->unsignedInteger('subscribers')->nullable();

            #$table->date('dt');
            $table->datetime('ts');
            $table->text('obs')->nullable();
            $table->boolean('parsed')->default(0);
            
            $table->foreignIdFor(Canal::class);

            $table->unique(['canal_id','ts']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arxivs');
    }
};
