<?php

namespace App\Services;

use App\DTO\SeasonTeamStatsDTO;
use App\Models\Season;
use App\Repositories\SeasonRepositoryInterface;
use Illuminate\Support\Facades\DB;

readonly final class SeasonService implements SeasonServiceInterface
{
    public function __construct(private SeasonRepositoryInterface $seasonRepository, private readonly MatchOrganizerServiceInterface $matchOrganizerService)
    {
        //
    }

    public function getAll(): iterable
    {
        return $this->seasonRepository->all();
    }

    public function reset(Season $season): void
    {
        DB::transaction(function () use ($season) {
            $this->seasonRepository->resetMatches($season);
            $this->seasonRepository->resetTeams($season);
            $this->seasonRepository->resetWeek($season);
        });
    }

    /**
     * @param SeasonTeamStatsDTO[] $teamDTOs
     * @return Season
     */
    public function create(array $teamDTOs): Season
    {
        return DB::transaction(function () use ($teamDTOs) {
            $season = $this->seasonRepository->create();

            $teamData = collect($teamDTOs)->map(fn(SeasonTeamStatsDTO $team) => [
                'team_id' => $team->teamId,
                'strength' => $team->strength,
                'points' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
            ]);

            $teams = $this->seasonRepository->createTeams($season, $teamData);

            $this->matchOrganizerService->generateSchedule($season, $teams);

            return $season;
        });
    }
}