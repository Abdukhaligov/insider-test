<?php

namespace App\Repositories;

use App\Models\Season;

interface SeasonRepositoryInterface
{
    public function all(): iterable;
    
    public function create(): Season;

    public function resetMatches(Season $season): void;

    public function resetTeams(Season $season): void;

    public function resetWeek(Season $season): void;
}