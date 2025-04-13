<?php

namespace App\Http\Controllers;

use App\Exceptions\NoMatchesFoundException;
use App\Models\Game;
use App\Models\Season;
use App\Models\SeasonTeam;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

//TODO:: Refactor this huge class
class MatchOrganizer
{
    protected Collection $teams;
    protected int $totalWeeks;

    public function __construct(Collection $teams)
    {
        $this->teams = $teams;
        $this->calculateTotalWeeks();
    }

    public static function registerTeams(Season $season, array $teams): Collection
    {
        $teamData = collect($teams)->map(fn($team) => [
            'team_id' => $team['id'],
            'strength' => $team['strength'],
            'points' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
        ]);

        return $season->teams()->createMany($teamData)->shuffle();
    }

    /**
     * @throws NoMatchesFoundException
     */
    public static function simulateWeek(Season $season)
    {
        $matches = $season->matches()
            ->with('homeTeam', 'awayTeam')
            ->where('week', $season->week)
            ->get();

        if ($matches->isEmpty()) {
            throw new NoMatchesFoundException('Current week has no matches to simulate');
        }

        return DB::transaction(function () use ($season, $matches) {
            $teamStats = [];
            $season->load('teams');

            foreach ($matches as $match) {
                $scores = MatchOrganizer::calculateMatchScore(
                    $match->homeTeam->strength,
                    $match->awayTeam->strength
                );

                MatchOrganizer::updateResults($match, $scores);
                MatchOrganizer::accumulateTeamStats($teamStats, $match, $scores);
            }

            MatchOrganizer::applyTeamStatistics($season, $teamStats);
            $season->increment('week');

            return $season->week;
        });
    }

    public static function scheduleMatches(Season $season, Collection $teams): void
    {
        $matchOrganizer = new self($teams);
        $matchOrganizer->generateSchedule($season);
    }

    public function generateSchedule(Season $season): void
    {
        for ($week = 1; $week <= $this->totalWeeks; $week++) {
            $this->createWeekMatches($season, $week);
            $this->rotateTeams();
        }
    }

    protected function calculateTotalWeeks(): void
    {
        $teamCount = $this->teams->count();
        $this->totalWeeks = ($teamCount % 2 === 0 ? $teamCount - 1 : $teamCount) * 2;
    }

    protected function createWeekMatches(Season $season, int $week): void
    {
        $teamCount = $this->teams->count();

        for ($index = 0; $index < $teamCount / 2; $index++) {
            $homeTeam = $this->teams->get($index);
            $awayTeam = $this->teams->get($teamCount - $index - 1);

            if (!$this->validMatchup($homeTeam, $awayTeam)) {
                continue;
            }

            $this->createMatch(
                $season,
                $this->determineHomeTeam($homeTeam, $awayTeam, $week),
                $this->determineAwayTeam($homeTeam, $awayTeam, $week),
                $week
            );
        }
    }

    protected function validMatchup(?object $homeTeam, ?object $awayTeam): bool
    {
        return $homeTeam && $awayTeam && $homeTeam->team_id !== $awayTeam->team_id;
    }

    protected function determineHomeTeam(object $homeTeam, object $awayTeam, int $week): int
    {
        return $week % 2 === 1 ? $homeTeam->team_id : $awayTeam->team_id;
    }

    protected function determineAwayTeam(object $homeTeam, object $awayTeam, int $week): int
    {
        return $week % 2 === 1 ? $awayTeam->team_id : $homeTeam->team_id;
    }

    protected function createMatch(Season $season, int $homeTeamId, int $awayTeamId, int $week): void
    {
        Game::create([
            'season_id' => $season->id,
            'home_team_id' => $homeTeamId,
            'away_team_id' => $awayTeamId,
            'week' => $week,
        ]);
    }

    protected function rotateTeams(): void
    {
        if ($this->teams->count() < 2) {
            return;
        }

        $first = $this->teams->shift();
        $second = $this->teams->shift();

        $this->teams->prepend($first)->push($second);
    }

    public static function calculateMatchScore(int $homeStrength, int $awayStrength): array
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

        return [
            'homeGoals' => $homeGoals,
            'awayGoals' => $awayGoals
        ];
    }

    public static function updateResults(Game $match, array $scores): void
    {
        $match->update([
            'home_team_score' => $scores['homeGoals'],
            'away_team_score' => $scores['awayGoals'],
            'status' => 'completed'
        ]);
    }

    public static function applyTeamStatistics(Season $season, array $teamStats): void
    {
        foreach ($teamStats as $teamId => $stats) {
            SeasonTeam::where('season_id', $season->id)->where('team_id', $teamId)->update([
                'won' => DB::raw("won + {$stats['won']}"),
                'drawn' => DB::raw("drawn + {$stats['drawn']}"),
                'lost' => DB::raw("lost + {$stats['lost']}"),
                'points' => DB::raw("points + {$stats['points']}"),
                'goals_for' => DB::raw("goals_for + {$stats['goals_for']}"),
                'goals_against' => DB::raw("goals_against + {$stats['goals_against']}")
            ]);
        }
    }

    public static function accumulateTeamStats(array &$stats, $match, array $scores): void
    {
        $homeId = $match->home_team_id;
        $awayId = $match->away_team_id;
        $homeGoals = $scores['homeGoals'];
        $awayGoals = $scores['awayGoals'];

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

    private static function initTeamStats(array &$stats, int $teamId): void
    {
        if (!isset($stats[$teamId])) {
            $stats[$teamId] = [
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'points' => 0,
                'goals_for' => 0,
                'goals_against' => 0
            ];
        }
    }

    private static function applyWinLoss(array &$stats, int $winnerId, int $loserId, int $winnerGoals, int $loserGoals): void
    {
        $stats[$winnerId]['won']++;
        $stats[$winnerId]['points'] += 3;
        $stats[$winnerId]['goals_for'] += $winnerGoals;
        $stats[$winnerId]['goals_against'] += $loserGoals;

        $stats[$loserId]['lost']++;
        $stats[$loserId]['goals_for'] += $loserGoals;
        $stats[$loserId]['goals_against'] += $winnerGoals;
    }

    private static function applyDraw(array &$stats, int $team1Id, int $team2Id, int $goals1, int $goals2): void
    {
        foreach ([$team1Id, $team2Id] as $teamId) {
            $stats[$teamId]['drawn']++;
            $stats[$teamId]['points']++;
            $stats[$teamId]['goals_for'] += ($teamId === $team1Id) ? $goals1 : $goals2;
            $stats[$teamId]['goals_against'] += ($teamId === $team1Id) ? $goals2 : $goals1;
        }
    }
}