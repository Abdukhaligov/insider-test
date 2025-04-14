<?php

namespace App\Services;

use App\Exceptions\NoMatchesFoundException;
use App\Models\Season;

interface SimulateServiceInterface
{
    /**
     * @throws NoMatchesFoundException
     */
    public function currentWeek(Season $season): int;

    public function allWeeks(Season $season): int;
}