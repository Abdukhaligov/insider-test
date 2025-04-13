<?php

namespace App\Services;

use App\DTO\MatchStatusDTO;
use App\DTO\SeasonTeamStatsDTO;
use App\Exceptions\NoMatchesFoundException;
use App\Models\Game;
use App\Models\Season;
use App\Repositories\MatchRepositoryInterface;
use App\Repositories\SeasonRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

readonly final class SimulateService implements SimulateServiceInterface
{
    public function __construct(private MatchRepositoryInterface  $matchRepository,
                                private SeasonRepositoryInterface $seasonRepository)
    {
        //
    }

    /**
     * @throws NoMatchesFoundException
     */
    public function currentWeek(Season $season): int
    {
        $matches = $season->matches()
            ->with('homeTeam', 'awayTeam')
            ->where('week', $season->week)
            ->get();

        if ($matches->isEmpty()) {
            throw new NoMatchesFoundException('Current week has no matches to simulate');
        }

        return DB::transaction(function () use ($season, $matches) {
            $teamStats = collect();
            $season->load('teams');

            foreach ($matches as $match) {
                $scores = self::calculateMatchScore(
                    $match->homeTeam->strength,
                    $match->awayTeam->strength
                );

                $this->matchRepository->update($match, $scores);
                self::accumulateTeamStats($teamStats, $match, $scores);
            }

            $this->seasonRepository->applyTeamStatistics($season, $teamStats);
            $this->seasonRepository->nextWeek($season);

            return $season->week;
        });
    }

    /**
     * @param Season $season
     * @return int
     */
    public function allWeeks(Season $season): int
    {
        $weeksSimulated = 0;

        DB::transaction(function () use ($season, &$weeksSimulated) {
            while ($this->hasRemainingMatches($season)) {
                $this->currentWeek($season);
                $weeksSimulated++;
            }
        });

        return $weeksSimulated;
    }

    /**
     * Check if there are unsimulated matches left in the season.
     * @param Season $season
     * @return bool
     */
    private function hasRemainingMatches(Season $season): bool
    {
        return $season->matches()->where('status', 'scheduled')->exists();
    }

    /**
     * @param int $homeStrength
     * @param int $awayStrength
     * @return MatchStatusDTO
     */
    public static function calculateMatchScore(int $homeStrength, int $awayStrength): MatchStatusDTO
    {
        $homeGoals = 0;
        $awayGoals = 0;

        // Apply home advantage (15% boost to home team)
        $homeEffective = max(1, $homeStrength * 1.15);
        $awayEffective = max(1, $awayStrength);

        // Calculate total match attacks (base 20-30)
        $totalAttacks = rand(20, 30);
        $strengthSum = $homeEffective + $awayEffective;

        // Distribute attacks between teams based on relative strength
        $homeAttacks = round(($homeEffective / $strengthSum) * $totalAttacks);
        $awayAttacks = $totalAttacks - $homeAttacks;

        // Calculate scoring probabilities with defense factor
        $baseConversion = 0.22; // Base 22% conversion rate

        // Home team attacks
        foreach (range(1, $homeAttacks) as $i) {
            $attackStrength = $homeEffective * 1.1; // Home attacking bonus
            $defenseStrength = $awayEffective * 0.9; // Away defense penalty
            $chance = ($attackStrength / ($attackStrength + $defenseStrength)) * $baseConversion * 100;

            if (rand(1, 100) <= $chance) {
                $homeGoals++;
            }
        }

        // Away team attacks
        foreach (range(1, $awayAttacks) as $i) {
            $defenseStrength = $homeEffective * 1.1; // Home defense bonus
            $chance = ($awayEffective / ($awayEffective + $defenseStrength)) * $baseConversion * 100;

            if (rand(1, 100) <= $chance) {
                $awayGoals++;
            }
        }

        return new MatchStatusDTO($homeGoals, $awayGoals);
    }

    /**
     * @param Collection $stats
     * @param Game $match
     * @param MatchStatusDTO $scores
     * @return void
     */
    private static function accumulateTeamStats(Collection $stats, Game $match, MatchStatusDTO $scores): void
    {
        $homeId = $match->home_team_id;
        $awayId = $match->away_team_id;
        $homeGoals = $scores->homeGoals;
        $awayGoals = $scores->awayGoals;

        self::initTeamStats($stats, $homeId);
        self::initTeamStats($stats, $awayId);

        if ($homeGoals > $awayGoals) {
            self::applyWinLoss($stats, $homeId, $awayId, $homeGoals, $awayGoals);
        } elseif ($homeGoals < $awayGoals) {
            self::applyWinLoss($stats, $awayId, $homeId, $awayGoals, $homeGoals);
        } else {
            self::applyDraw($stats, $homeId, $awayId, $homeGoals, $awayGoals);
        }
    }

    /**
     * @param Collection $stats
     * @param int $teamId
     * @return void
     */
    private static function initTeamStats(Collection $stats, int $teamId): void
    {
        if ($stats->where('teamId', $teamId)->isEmpty()) {
            $stats->push(new SeasonTeamStatsDTO(teamId: $teamId));
        }
    }

    /**
     * @param Collection $stats
     * @param int $winnerId
     * @param int $loserId
     * @param int $winnerGoals
     * @param int $loserGoals
     * @return void
     */
    private static function applyWinLoss(Collection $stats, int $winnerId, int $loserId, int $winnerGoals, int $loserGoals): void
    {
        /** @var SeasonTeamStatsDTO $winner */
        $winner = $stats->where('teamId', $winnerId)->first();
        /** @var SeasonTeamStatsDTO $loser */
        $loser = $stats->where('teamId', $loserId)->first();

        $winner->won++;
        $winner->points += 3;
        $winner->goalsFor += $winnerGoals;
        $winner->goalsAgainst += $loserGoals;

        $loser->lost++;
        $loser->goalsFor += $loserGoals;
        $loser->goalsAgainst += $winnerGoals;
    }

    /**
     * @param Collection $stats
     * @param int $team1Id
     * @param int $team2Id
     * @param int $goals1
     * @param int $goals2
     * @return void
     */
    private static function applyDraw(Collection $stats, int $team1Id, int $team2Id, int $goals1, int $goals2): void
    {
        foreach ([$team1Id, $team2Id] as $teamId) {
            /** @var SeasonTeamStatsDTO $seasonTeam */
            $seasonTeam = $stats->where('teamId', $teamId)->first();

            $seasonTeam->drawn++;
            $seasonTeam->points++;
            $seasonTeam->goalsFor += ($teamId === $team1Id) ? $goals1 : $goals2;
            $seasonTeam->goalsAgainst += ($teamId === $team1Id) ? $goals2 : $goals1;
        }
    }
}