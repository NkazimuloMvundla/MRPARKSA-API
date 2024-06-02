<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToParkingSpacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    // public function up()
    // {
    //     Schema::table('parking_spaces', function (Blueprint $table) {
    //         $table->foreignId('user_id')->constrained()->onDelete('cascade')->after('id'); // Add user_id column
    //     });
    // }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    // public function down()
    // {
    //     Schema::table('parking_spaces', function (Blueprint $table) {
    //         $table->dropForeign(['user_id']);
    //         $table->dropColumn('user_id');
    //     });
    // }
      /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            //
        });
    }
}
