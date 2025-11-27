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
        Schema::table('voip_accounts', function (Blueprint $table) {
            $table->string('transport')->default('transport-udp');

            // Nome do campo: Sinalização: SIP ou Webrtc
            // Se for webrtc voce seta transport-wss
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voip_accounts', function (Blueprint $table) {
            //
        });
    }
};
