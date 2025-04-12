<?php

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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->integer('week');
            $table->foreignId('home_team_id')->constrained('season_teams')->onDelete('cascade');
            $table->foreignId('away_team_id')->constrained('season_teams')->onDelete('cascade');
            $table->integer('home_team_score')->nullable();
            $table->integer('away_team_score')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'postponed', 'canceled'])->default('scheduled');
            $table->dateTime('scheduled_at')->nullable();
            $table->foreignId('venue_id')->nullable()->constrained();

            $table->unique(['season_id', 'week', 'home_team_id', 'away_team_id']);

            $table->index(['season_id', 'week']);
            $table->index(['home_team_id']);
            $table->index(['away_team_id']);
            $table->index(['venue_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
