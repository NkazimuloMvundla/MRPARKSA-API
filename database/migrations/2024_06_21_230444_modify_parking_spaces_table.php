<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyParkingSpacesTable extends Migration
{
    public function up()
    {
        Schema::table('parking_spaces', function (Blueprint $table) {
            // Make capacity nullable
            $table->integer('capacity')->nullable()->change();

            // Make address required
            $table->string('address')->nullable(false)->change();

            // Make rating nullable
            $table->integer('rating')->nullable()->change();

            // Make cancellation_policy not nullable
            $table->string('cancellation_policy')->nullable(false)->change();

            // Make how_to_redeem not nullable
            $table->string('how_to_redeem')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('parking_spaces', function (Blueprint $table) {
            // Revert changes made in the 'up' method
            $table->integer('capacity')->nullable(false)->change();
            $table->string('address')->nullable()->change();
            $table->integer('rating')->nullable(false)->change();
            $table->string('cancellation_policy')->nullable()->change();
            $table->string('how_to_redeem')->nullable()->change();
        });
    }
}
