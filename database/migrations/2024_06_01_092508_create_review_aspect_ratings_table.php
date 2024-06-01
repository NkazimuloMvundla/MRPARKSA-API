<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewAspectRatingsTable extends Migration
{
    public function up()
    {
        Schema::create('review_aspect_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->onDelete('cascade');
            $table->foreignId('review_aspect_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('percentage'); // 0 to 100
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('review_aspect_ratings');
    }
}
