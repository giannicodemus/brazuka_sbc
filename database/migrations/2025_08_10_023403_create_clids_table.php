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
        Schema::create('clids', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('random_clid_list_id');
            $table->smallInteger('order');
            $table->integer('number');
            
            // Definição da Foreign Key
            $table->foreign('random_clid_list_id')
                ->references('id')
                ->on('random_clid_lists')
                ->onDelete('cascade'); // ou 'restrict', 'set null'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clids');
    }
};
