<?php

namespace App\Services;

use App\Models\Season;
use Illuminate\Support\Collection;

interface MatchOrganizerServiceInterface
{
    public function generateSchedule(Season $season, Collection $teams): void;
}