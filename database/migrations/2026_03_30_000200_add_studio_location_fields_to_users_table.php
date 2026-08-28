<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('studio_name')->nullable()->after('role');
            $table->string('studio_location')->nullable()->after('studio_name');
            $table->string('studio_maps_link')->nullable()->after('studio_location');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['studio_name', 'studio_location', 'studio_maps_link']);
        });
    }
};
