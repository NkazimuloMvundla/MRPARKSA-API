<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParkingSpacesTable extends Migration
{
    public function up()
    {
        Schema::create('parking_spaces', function (Blueprint $table) {
            $table->id();
            // $table->string('type'); // hourly, monthly, airport
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('address')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key to users table
            // $table->decimal('base_price_hourly', 8, 2)->after('longitude');
            // $table->decimal('base_price_monthly', 8, 2)->after('base_price_hourly');
            $table->integer('capacity')->default(1);
            $table->string('contact_info')->nullable();
            $table->json('amenities')->nullable();
            $table->float('rating', 2, 1)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parking_spaces');
    }
}
