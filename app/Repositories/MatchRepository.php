<?php

namespace App\Repositories;

use App\DTO\MatchStatusDTO;
use App\Models\Game;
use App\Models\Season;

final readonly class MatchRepository implements MatchRepositoryInterface
{
    public function update(Game $match, MatchStatusDTO $matchStatusDTO): void
    {
        $match->update([
            'home_team_score' => $matchStatusDTO->homeGoals,
            'away_team_score' => $matchStatusDTO->awayGoals,
            'status' => 'completed'
        ]);
    }
    
    public function create(Season $season, int $homeTeamId, int $awayTeamId, int $week): void
    {
        Game::create([
            'season_id' => $season->id,
            'home_team_id' => $homeTeamId,
            'away_team_id' => $awayTeamId,
            'week' => $week,
        ]);
    }
}