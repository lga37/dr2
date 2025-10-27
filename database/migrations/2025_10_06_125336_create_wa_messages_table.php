<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wa_messages', function (Blueprint $table) {

            $table->id();


            $table->string('direction')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('body')->nullable();
            $table->text('raw')->nullable();

            $table->foreignId('message_id')->nullable()->constrained('messages')->cascadeOnDelete();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_messages');
    }
};
