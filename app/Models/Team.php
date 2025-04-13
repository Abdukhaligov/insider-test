<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'country'];

    public function seasonTeams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SeasonTeam::class);
    }

    public function seasons()
    {
        return $this->belongsToMany(Season::class, 'season_teams')
            ->using(SeasonTeam::class)
            ->withPivot(['power', 'points', 'won', 'drawn', 'lost', 'goals_for', 'goals_against']);
    }
}