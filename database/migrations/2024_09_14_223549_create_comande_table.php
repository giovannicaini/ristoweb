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
        Schema::create('comande', function (Blueprint $table) {
            $table->id();
            $table->integer('evento_id');
            $table->integer('n_ordine');
            $table->string('nominativo')->nullable();
            $table->string('tavolo')->nullable();
            $table->boolean('asporto')->nullable();
            $table->integer('cassiere_id');
            $table->integer('cassa_id')->nullable();
            $table->decimal('totale', 6, 2)->default('0.00')->nullable();
            $table->decimal('pagato', 6, 2)->nullable();
            $table->decimal('sconto', 6, 2)->nullable();
            $table->decimal('buoni', 6, 2)->nullable();
            $table->decimal('su_conto', 6, 2)->nullable();
            $table->integer('conto_id')->nullable();
            $table->string('stato')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comande');
    }
};
