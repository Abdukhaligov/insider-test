<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'city', 'address', 'capacity'];

    public function matches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function homeTeams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Team::class, 'home_venue_id');
    }
}