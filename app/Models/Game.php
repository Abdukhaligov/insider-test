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
        return $this->belongsTo(SeasonTeam::class, 'home_team_id');
    }

    public function awayTeam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SeasonTeam::class, 'away_team_id');
    }
}