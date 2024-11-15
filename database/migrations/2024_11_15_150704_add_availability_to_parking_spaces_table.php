<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('parking_spaces', function (Blueprint $table) {
            // Add 'availability' column, defaulting to true (available)
            $table->boolean('availability')->default(true);
        });
    }

    public function down()
    {
        Schema::table('parking_spaces', function (Blueprint $table) {
            // Drop 'availability' column on rollback
            $table->dropColumn('availability');
        });
    }
};
