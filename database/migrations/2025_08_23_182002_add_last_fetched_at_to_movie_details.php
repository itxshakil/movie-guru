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
        Schema::table('movie_details', function (Blueprint $table) {
            $table->json('sources')->nullable()->after('details');
            $table->timestamp('source_last_fetched_at')->nullable()->after('sources');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movie_details', function (Blueprint $table) {
            $table->dropColumn('sources');
            $table->dropColumn('source_last_fetched_at');
        });
    }
};
