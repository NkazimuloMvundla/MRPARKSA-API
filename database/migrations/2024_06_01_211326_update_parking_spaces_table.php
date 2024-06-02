<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateParkingSpacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parking_spaces', function (Blueprint $table) {
            $table->text('cancellation_policy')->nullable();
            $table->json('access_hours')->nullable();
            $table->text('things_to_know')->nullable();
            $table->text('how_to_redeem')->nullable();
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
            $table->dropColumn('cancellation_policy');
            $table->dropColumn('access_hours');
            $table->dropColumn('things_to_know');
            $table->dropColumn('how_to_redeem');
        });
    }
}
