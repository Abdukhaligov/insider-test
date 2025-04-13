<?php

namespace App\Services;

use App\Models\Season;

interface SeasonServiceInterface
{
    public function getAll(): iterable;
    
    public function reset(Season $season): void;

    public function create(array $teamDTOs): Season;
}