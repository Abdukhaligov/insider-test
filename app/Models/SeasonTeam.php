<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeasonTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id', 'team_id', 'power', 'points',
        'won', 'drawn', 'lost', 'goals_for', 'goals_against'
    ];

    public function season(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function homeMatches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Game::class, 'home_team_id');
    }

    public function awayMatches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Game::class, 'away_team_id');
    }
}