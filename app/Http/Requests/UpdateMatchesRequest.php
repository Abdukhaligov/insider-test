<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMatchesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            '*.id' => 'exists:matches,id',
            '*.away_team_score' => 'numeric|nullable',
            '*.home_team_score' => 'numeric|nullable',
        ];
    }
}
