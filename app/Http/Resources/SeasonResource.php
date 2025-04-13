<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeasonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'week' => $this->week,
            'teams' => $this->teams->pluck('team')->toArray(),
            'matches' => $this->matches->load(['homeTeam', 'awayTeam'])->toArray(),
        ];
    }

    public static function collection($resource)
    {
        return new SeasonResourceCollection($resource);
    }
}
