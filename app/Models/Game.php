<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;
    
    protected $table = 'matches';

    protected $fillable = [
        'season_id', 'week', 'home_team_id', 'away_team_id', 'home_team_score', 'away_team_score', 'status'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'played' => 'boolean'
    ];

    public function season(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function homeTeam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    // Business logic for handling match results
    public function recordResult(): void
    {
        if (!$this->played) return;

        $home = $this->homeTeam;
        $away = $this->awayTeam;

        // Update goals
        $home->goals_for += $this->home_team_score;
        $home->goals_against += $this->away_team_score;
        $away->goals_for += $this->away_team_score;
        $away->goals_against += $this->home_team_score;

        // Update match outcome
        if ($this->home_team_score > $this->away_team_score) {
            $home->won++;
            $away->lost++;
            $home->points += 3;
        } elseif ($this->home_team_score < $this->away_team_score) {
            $away->won++;
            $home->lost++;
            $away->points += 3;
        } else {
            $home->drawn++;
            $away->drawn++;
            $home->points += 1;
            $away->points += 1;
        }

        $home->save();
        $away->save();
    }
}