<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeasonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Season $this */
        return [
            'id' => $this->id,
            'week' => $this->week,
            'stats' => $this->teams->load(['team'])->sortByDesc('points')->values()->toArray(),
            'weeks' => $this->matches
                ->load(['homeTeam', 'awayTeam'])
                ->groupBy('week')
                ->map(function ($matches) {
                    return [
                        'matches' => $matches->map(function ($match) {
                            return [
                                'id' => $match->id,
                                'week' => $match->week,
                                'home_team' => $match->homeTeam->name,
                                'home_team_id' => $match->home_team_id,
                                'away_team' => $match->awayTeam->name,
                                'away_team_id' => $match->away_team_id,
                                'away_team_score' => $match->away_team_score,
                                'home_team_score' => $match->home_team_score,
                            ];
                        })->values(),
                    ];
                })->values(),
        ];
    }

    public static function collection($resource)
    {
        return new SeasonResourceCollection($resource);
    }
}
