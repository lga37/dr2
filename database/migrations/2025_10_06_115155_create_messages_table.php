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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->string('wa_id', 32)->index();       // WaId do usuário (ex.: 55119...)
            $table->string('contact', 40)->nullable();  // whatsapp:+55...
            $table->string('display_name')->nullable(); // nome de perfil vindo do webhook
            #$table->timestamp('started_at')->useCurrent();
            #$table->timestamp('last_message_at')->nullable()->index();
            $table->json('meta')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
