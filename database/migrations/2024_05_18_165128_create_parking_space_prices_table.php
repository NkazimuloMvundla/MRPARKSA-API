<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParkingSpacePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parking_space_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_space_id')->constrained()->onDelete('cascade');
            $table->foreignId('parking_type_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parking_space_prices');
    }
}
