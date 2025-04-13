<?php

namespace App\Repositories;

use App\DTO\SeasonTeamStatsDTO;
use App\Models\Season;
use App\Models\SeasonTeam;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final readonly class SeasonRepository implements SeasonRepositoryInterface
{
    public function __construct(private string $model = Season::class)
    {
        //
    }

    /**
     * @return Collection
     */
    public function all(): iterable
    {
        return $this->model::all();
    }

    /**
     * @return Season
     */
    public function create(): Season
    {
        return $this->model::create(['date' => now()]);
    }

    /**
     * @param Season $season
     * @return void
     */
    public function resetMatches(Season $season): void
    {
        $season->matches()->update([
            'home_team_score' => null,
            'away_team_score' => null,
            'status' => 'scheduled'
        ]);
    }

    /**
     * @param Season $season
     * @return void
     */
    public function resetTeams(Season $season): void
    {
        $season->teams()->update([
            'points' => 0,
            'won' => 0,
            'lost' => 0,
            'drawn' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
        ]);
    }

    /**
     * @param Season $season
     * @return void
     */
    public function resetWeek(Season $season): void
    {
        $season->update(['week' => 1]);
    }

    /**
     * @param Season $season
     * @param SeasonTeamStatsDTO[] $teams
     * @return Collection
     */
    public function createTeams(Season $season, iterable $teams): Collection
    {
        return $season->teams()->createMany($teams);
    }

    /**
     * @param Season $season
     * @param SeasonTeamStatsDTO[] $teams
     * @return void
     */
    public function applyTeamStatistics(Season $season, iterable $teams): void
    {
        /** @var SeasonTeamStatsDTO $stat */
        foreach ($teams as $team) {
            SeasonTeam::where('season_id', $season->id)->where('team_id', $team->teamId)->update([
                'won' => DB::raw("won + {$team->won}"),
                'drawn' => DB::raw("drawn + {$team->drawn}"),
                'lost' => DB::raw("lost + {$team->lost}"),
                'points' => DB::raw("points + {$team->points}"),
                'goals_for' => DB::raw("goals_for + {$team->goalsFor}"),
                'goals_against' => DB::raw("goals_against + {$team->goalsAgainst}")
            ]);
        }
    }
    
    public function nextWeek(Season $season): void
    {
        $season->increment('week');
    }
}