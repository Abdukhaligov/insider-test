<?php

namespace App\DTO;

final class SeasonTeamStatsDTO
{
    public function __construct(
        public int   $teamId,
        public float $strength = 0,
        public int   $points = 0,
        public int   $won = 0,
        public int   $drawn = 0,
        public int   $lost = 0,
        public int   $goalsFor = 0,
        public int   $goalsAgainst = 0
    )
    {
        //
    }

    public function toArray(): array
    {
        return [
            'team_id' => $this->teamId,
            'strength' => $this->strength,
            'points' => $this->points,
            'won' => $this->won,
            'drawn' => $this->drawn,
            'lost' => $this->lost,
            'goals_for' => $this->goalsFor,
            'goals_against' => $this->goalsAgainst,
        ];
    }
}