<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('parking_spaces', function (Blueprint $table) {
            // Make `access_hours` column required
            $table->string('access_hours')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('parking_spaces', function (Blueprint $table) {
            // Revert `access_hours` column to nullable
            $table->string('access_hours')->nullable()->change();
        });
    }
};
