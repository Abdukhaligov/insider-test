<?php

namespace App\Services;

use App\DTO\SeasonTeamStatsDTO;
use App\Models\Game;
use App\Models\Season;
use App\Repositories\MatchRepositoryInterface;
use App\Repositories\SeasonRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final readonly class MatchService implements MatchServiceInterface
{
    public function __construct(
        private MatchRepositoryInterface  $matchRepository,
        private SeasonRepositoryInterface $seasonRepository
    )
    {
        //
    }

    public function updateMatches(Season $season, array $matchesData): void
    {
        DB::transaction(function () use ($season, $matchesData) {
            // Get existing matches data
            $matchIds = collect($matchesData)->pluck('id');
            $oldMatches = $this->matchRepository->getByIds($matchIds);

            // Update matches
            $this->matchRepository->upsertScores($matchesData);

            // Get updated matches
            $updatedMatches = $this->matchRepository->getByIds($matchIds);

            // Calculate statistics delta
            $statsDelta = $this->calculateStatisticsDelta($oldMatches, $updatedMatches);

            // Apply statistics changes
            $this->seasonRepository->applyTeamStatistics($season, $statsDelta);
        });
    }

    private function calculateStatisticsDelta(Collection $oldMatches, Collection $updatedMatches): array
    {
        $statsDelta = [];

        foreach ($updatedMatches as $updatedMatch) {
            $oldMatch = $oldMatches->firstWhere('id', $updatedMatch->id);

            if (!$this->shouldProcess($oldMatch, $updatedMatch)) {
                continue;
            }

            $this->processMatchDelta(
                $statsDelta,
                $oldMatch,
                $updatedMatch
            );
        }

        return array_values($statsDelta);
    }

    private function shouldProcess(Game $oldMatch, Game $updatedMatch): bool
    {
        return $updatedMatch->status === 'completed' &&
            ($oldMatch->home_team_score != $updatedMatch->home_team_score ||
                $oldMatch->away_team_score != $updatedMatch->away_team_score);
    }

    private function processMatchDelta(array &$statsDelta, $oldMatch, $updatedMatch): void
    {
        // Subtract old results
        $this->calculateTeamDelta(
            $statsDelta,
            $oldMatch->home_team_id,
            $oldMatch->home_team_score,
            $oldMatch->away_team_score,
            -1
        );

        $this->calculateTeamDelta(
            $statsDelta,
            $oldMatch->away_team_id,
            $oldMatch->away_team_score,
            $oldMatch->home_team_score,
            -1
        );

        // Add new results
        $this->calculateTeamDelta(
            $statsDelta,
            $updatedMatch->home_team_id,
            $updatedMatch->home_team_score,
            $updatedMatch->away_team_score,
            1
        );

        $this->calculateTeamDelta(
            $statsDelta,
            $updatedMatch->away_team_id,
            $updatedMatch->away_team_score,
            $updatedMatch->home_team_score,
            1
        );
    }

    private function calculateTeamDelta(
        array &$statsDelta,
        ?int  $teamId,
        ?int  $goalsFor,
        ?int  $goalsAgainst,
        int   $multiplier
    ): void
    {
        if (!$teamId || null === $goalsFor || null === $goalsAgainst) {
            return;
        }

        $key = $teamId;

        if (!isset($statsDelta[$key])) {
            $statsDelta[$key] = new SeasonTeamStatsDTO(
                teamId: $teamId,
                points: 0,
                won: 0,
                drawn: 0,
                lost: 0,
                goalsFor: 0,
                goalsAgainst: 0,
            );
        }

        $delta = $statsDelta[$key];

        $result = match (true) {
            $goalsFor > $goalsAgainst => 'win',
            $goalsFor < $goalsAgainst => 'loss',
            default => 'draw'
        };

        match ($result) {
            'win' => $delta->won += (1 * $multiplier),
            'loss' => $delta->lost += (1 * $multiplier),
            'draw' => $delta->drawn += (1 * $multiplier),
        };

        $delta->points += match ($result) {
            'win' => 3 * $multiplier,
            'draw' => 1 * $multiplier,
            default => 0,
        };

        $delta->goalsFor += $goalsFor * $multiplier;
        $delta->goalsAgainst += $goalsAgainst * $multiplier;
    }
}