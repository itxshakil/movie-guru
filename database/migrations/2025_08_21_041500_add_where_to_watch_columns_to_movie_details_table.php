<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('movie_details', function (Blueprint $table) {
            $table->json('where_to_watch')->nullable()->after('details');
            $table->timestamp('where_to_watch_fetched_at')->nullable()->after('where_to_watch');
            $table->timestamp('where_to_watch_expires_at')->nullable()->after('where_to_watch_fetched_at');
        });
    }

    public function down(): void
    {
        Schema::table('movie_details', function (Blueprint $table) {
            $table->dropColumn('where_to_watch');
            $table->dropColumn('where_to_watch_fetched_at');
            $table->dropColumn('where_to_watch_expires_at');
        });
    }
};
