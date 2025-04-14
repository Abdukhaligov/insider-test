<?php

namespace App\Services;

use App\Models\Season;

interface MatchServiceInterface
{
    public function updateMatches(Season $season, array $matchesData): void;
}