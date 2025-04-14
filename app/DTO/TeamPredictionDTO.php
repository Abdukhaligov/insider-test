<?php

namespace App\DTO;

readonly final class TeamPredictionDTO
{
    public function __construct(
        public string $name,
        public float  $percentage
    )
    {
        //
    }
}