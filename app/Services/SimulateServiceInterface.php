<?php

namespace App\Services;

use App\Models\Season;

interface SimulateServiceInterface
{
    public function currentWeek(Season $season): int;

    public function allWeeks(Season $season): int;

}