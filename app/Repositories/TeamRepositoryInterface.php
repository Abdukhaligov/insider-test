<?php

namespace App\Repositories;

interface TeamRepositoryInterface
{
    public function all(): iterable;
}