<?php

namespace App\DTO;

readonly final class MatchStatusDTO
{
    public function __construct(public int $homeGoals = 0, public int $awayGoals = 0)
    {
        //
    }
}