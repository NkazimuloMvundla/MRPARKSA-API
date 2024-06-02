<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPreApprovalRequiredToParkingSpacesTable extends Migration
{
    public function up()
    {
        Schema::table('parking_spaces', function (Blueprint $table) {
            $table->boolean('pre_approval_required')->default(false);
        });
    }

    public function down()
    {
        Schema::table('parking_spaces', function (Blueprint $table) {
            $table->dropColumn('pre_approval_required');
        });
    }
}
