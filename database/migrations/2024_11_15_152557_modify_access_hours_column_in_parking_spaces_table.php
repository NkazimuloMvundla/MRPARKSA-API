<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('parking_spaces', function (Blueprint $table) {
            // Modify 'access_hours' column length to 500
            $table->string('access_hours', 500)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parking_spaces', function (Blueprint $table) {
            // Rollback the column change to original state, for example back to 255
            $table->string('access_hours', 255)->change();
        });
    }
};
