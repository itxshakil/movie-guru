<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('movie_details', function (Blueprint $table): void {
            $table->timestamp('omdb_last_fetched_at')->nullable()->after('details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movie_details', function (Blueprint $table): void {
            $table->dropColumn('omdb_last_fetched_at');
        });
    }
};
