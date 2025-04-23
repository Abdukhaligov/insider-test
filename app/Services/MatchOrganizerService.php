<?php

namespace App\Services;

use App\Models\Season;
use App\Repositories\MatchRepositoryInterface;
use Illuminate\Support\Collection;

readonly final class MatchOrganizerService implements MatchOrganizerServiceInterface
{
    public function __construct(protected MatchRepositoryInterface $matchRepository)
    {
        //
    }

    public function generateSchedule(Season $season, Collection $teams): void
    {
        $totalWeeks = ($teams->count() % 2 === 0 ? $teams->count() - 1 : $teams->count()) * 2;

        for ($week = 1; $week <= $totalWeeks; $week++) {
            $this->createWeekMatches($season, $teams, $week);
            $teams = $this->rotateTeams($teams);
        }
    }

    private function rotateTeams(Collection $teams): Collection
    {
        if ($teams->count() < 2) return $teams;

        $rotated = clone $teams; // Work on a copy
        $first = $rotated->shift();
        $second = $rotated->shift();

        return $rotated->prepend($first)->push($second);
    }

    private function createWeekMatches(Season $season, Collection $teams, int $week): void
    {
        $teamCount = $teams->count();

        for ($index = 0; $index < $teamCount / 2; $index++) {
            $homeTeam = $teams->get($index);
            $awayTeam = $teams->get($teamCount - $index - 1);

            if (!($homeTeam && $awayTeam && ($homeTeam->id !== $awayTeam->id))) continue;

            $this->matchRepository->create(
                $season,
                $week % 2 === 1 ? $homeTeam->id : $awayTeam->id,
                $week % 2 === 1 ? $awayTeam->id : $homeTeam->id,
                $week
            );
        }
    }
}