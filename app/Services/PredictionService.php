<?php

namespace App\Services;

use App\DTO\TeamPredictionDTO;
use App\Models\Season;
use Illuminate\Support\Collection;

readonly final class PredictionService implements PredictionServiceInterface
{
    /**
     * Calculate win percentage chance for each team in the season.
     *
     * @param Season $season
     * @return Collection
     */
    public function calculate(Season $season): Collection
    {
        $teams = $season->teams;

        $teams = $teams->map(function ($team) {
            $points = $team->points;
            $goalDifference = $team->goal_difference;
            $strength = $team->strength;

            $score = ($points * 3) + ($goalDifference * 0.1) + ($strength * 1);

            $team->calculated_score = $score;
            $team->goal_difference = $goalDifference;
            return $team;
        });

        $totalScore = $teams->sum('calculated_score');

        return $teams->map(function ($team) use ($totalScore, $teams) {
            $chance = $totalScore > 0
                ? ($team->calculated_score / $totalScore) * 100
                : 100 / max(1, $teams->count());

            return new TeamPredictionDTO($team->team->name, round($chance, 2));
        })->sortByDesc('percentage')->values();
    }
}