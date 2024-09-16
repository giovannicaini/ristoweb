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
        Schema::create('comande_dettagli', function (Blueprint $table) {
            $table->id();
            $table->integer('comanda_id');
            $table->integer('prodotto_id');
            $table->integer('quantita');
            $table->decimal('prezzo_unitario', 6, 2)->nullable();
            $table->decimal('prezzo_totale', 6, 2)->nullable();
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
        Schema::dropIfExists('comanda_dettaglios');
    }
};
