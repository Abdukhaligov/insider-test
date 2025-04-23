<?php

namespace App\Http\Requests;

use App\DTO\SeasonTeamStatsDTO;
use Illuminate\Foundation\Http\FormRequest;

class SeasonTeamsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            '*.id' => ['required', 'integer', 'exists:teams,id'],
            '*.strength' => ['required', 'numeric', 'between:0,100', 'regex:/^\d{1,3}(\.\d{1,2})?$/'],
        ];
    }

    /**
     * @return SeasonTeamStatsDTO[]
     */
    public function toDTOs(): array
    {
        return collect($this->validated())
            ->map(fn($team) => new SeasonTeamStatsDTO(
                teamId: $team['id'],
                strength: $team['strength']
            ))
            ->all();
    }
}
