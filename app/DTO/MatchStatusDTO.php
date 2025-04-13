<?php

namespace App\DTO;

class MatchStatusDTO
{
    public function __construct(public int $homeGoals = 0, public int $awayGoals = 0)
    {
        //
    }
}