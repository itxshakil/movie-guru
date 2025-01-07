<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movie_details', function (Blueprint $table) {
            $table->id();
            $table->string('imdb_id', 10)->unique();
            $table->string('title');
            $table->unsignedSmallInteger('year');
            $table->string('release_date');
            $table->string('poster');
            $table->string('type');
            $table->decimal('imdb_rating', 2, 1);
            $table->unsignedInteger('imdb_votes');
            $table->json('details')->comment('Full details with all the data from the API');
            $table->unsignedInteger('views')->default(0)->comment('How many times this movie was searched for');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_details');
    }
};
