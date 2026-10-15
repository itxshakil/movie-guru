<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The watchlist becomes a library: every saved title has a status ("to_watch" or "watched"),
 * and watched titles can carry a like or dislike. Existing rows become "to_watch".
 * Rolling back drops watched state and reactions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('watchlists', 'library_items');

        Schema::table('library_items', function (Blueprint $table): void {
            $table->string('status', 16)->default('to_watch')->after('year');
            $table->string('reaction', 16)->nullable()->after('status');
            $table->timestamp('watched_at')->nullable()->after('reaction');

            $table->index(['user_id', 'status'], 'library_items_user_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('library_items', function (Blueprint $table): void {
            $table->dropIndex('library_items_user_id_status_index');
            $table->dropColumn(['status', 'reaction', 'watched_at']);
        });

        Schema::rename('library_items', 'watchlists');
    }
};
