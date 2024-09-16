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
        Schema::create('conti', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->integer('evento_id');
            $table->integer('numero_persone')->nullable();
            $table->decimal('buono_a_testa', 6, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conti');
    }
};
