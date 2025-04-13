<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SeasonResourceCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return $this->collection->map(function ($season) {
            $teamCount = $season->teams->count();
            $weeks = ($teamCount % 2 === 0 ? $teamCount - 1 : $teamCount) * 2;

            return [
                'id' => $season->id,
                'date' => $season->date->format('Y/m'),
                'week' => $season->week,
                'weeks' => $weeks
            ];
        });
    }
}
