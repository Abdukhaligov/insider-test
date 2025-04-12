<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'start_date', 'end_date'];

    public function seasonTeams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SeasonTeam::class);
    }

    public function matches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function teams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'season_teams');
    }
}