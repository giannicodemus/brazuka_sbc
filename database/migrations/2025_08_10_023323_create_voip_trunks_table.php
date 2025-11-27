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
        Schema::create('voip_trunks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('codec')->default('g729,alaw,ulaw');
            $table->smallInteger('auth_type')->default(0);
            $table->string('username')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('host')->nullable();
            $table->string('techprefix')->nullable();
            $table->smallInteger('removedigits')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voip_trunks');
    }
};
