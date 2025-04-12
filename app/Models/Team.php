<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'country', 'logo_url', 'home_venue_id'];

    public function seasonTeams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SeasonTeam::class);
    }

    public function homeVenue(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Venue::class, 'home_venue_id');
    }

    public function seasons()
    {
        return $this->belongsToMany(Season::class, 'season_teams')
            ->using(SeasonTeam::class)
            ->withPivot(['power', 'points', 'won', 'drawn', 'lost', 'goals_for', 'goals_against']);
    }
}