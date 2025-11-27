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
        Schema::create('outbound_routes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('voip_account_id');
            $table->unsignedBigInteger('voip_trunk_id');
            $table->unsignedBigInteger('random_clid_list_id')->nullable();
            $table->smallInteger('recording')->default(0);

            // Relacionamentos
            $table->foreign('voip_account_id')
                ->references('id')
                ->on('voip_accounts')
                ->onDelete('cascade');

            $table->foreign('voip_trunk_id')
                    ->references('id')
                    ->on('voip_trunks')
                    ->onDelete('cascade');

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
        Schema::dropIfExists('outbound_routes');
    }
};
