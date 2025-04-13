<?php

namespace App\Repositories;

use App\Models\Team;

final readonly class TeamRepository implements TeamRepositoryInterface
{
    public function __construct(private string $model = Team::class)
    {
        //
    }

    public function all(): iterable
    {
        return $this->model::all();
    }
}