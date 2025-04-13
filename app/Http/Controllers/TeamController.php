<?php

namespace App\Http\Controllers;

use App\Http\Resources\TeamResource;
use App\Repositories\TeamRepositoryInterface;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    public function __construct(protected readonly TeamRepositoryInterface $teamRepository)
    {
        //
    }

    public function index(): JsonResponse
    {
        return response()->json(TeamResource::collection($this->teamRepository->all()));
    }
}