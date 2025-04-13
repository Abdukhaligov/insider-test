<?php

namespace App\Http\Requests;

use App\DTO\SeasonTeamStatsDTO;
use Illuminate\Foundation\Http\FormRequest;

class SeasonTeamsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'teams.*.id' => ['required', 'integer', 'exists:teams,id'],
            'teams.*.strength' => ['required', 'numeric', 'between:0,100', 'regex:/^\d{1,3}(\.\d{1,2})?$/'],
        ];
    }

    /**
     * @return SeasonTeamStatsDTO[]
     */
    public function toDTOs(): array
    {
        return collect($this->validated('teams'))
            ->map(fn($team) => new SeasonTeamStatsDTO(
                teamId: $team['id'],
                strength: $team['strength']
            ))
            ->all();
    }
}
