<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('movie_details', function (Blueprint $table) {
            $table->string('genre')->nullable()->after('imdb_votes');
            $table->string('director')->nullable()->after('genre');
            $table->string('writer')->nullable()->after('director');
            $table->string('actors')->nullable()->after('writer');
        });
    }

    public function down(): void
    {
        Schema::table('movie_details', function (Blueprint $table) {
            $table->dropColumn('genre');
            $table->dropColumn('director');
            $table->dropColumn('writer');
            $table->dropColumn('actors');
        });
    }
};
