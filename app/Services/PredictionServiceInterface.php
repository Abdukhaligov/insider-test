<?php

namespace App\Services;

use App\Models\Season;
use Illuminate\Support\Collection;

interface PredictionServiceInterface
{
    public function calculate(Season $season): Collection;
}