<?php

namespace App\Http\Controllers;

use App\Http\Requests\SeasonTeamsRequest;
use App\Http\Requests\UpdateMatchesRequest;
use App\Http\Resources\SeasonResource;
use App\Models\Season;
use App\Services\MatchServiceInterface;
use App\Services\PredictionServiceInterface;
use App\Services\SeasonServiceInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SeasonController extends Controller
{
    public function __construct(protected readonly SeasonServiceInterface $seasonService, 
                                protected readonly PredictionServiceInterface $predictionService,
                                protected readonly MatchServiceInterface $matchService)
    {
        //
    }

    public function index(): JsonResponse
    {
        return response()->json(SeasonResource::collection($this->seasonService->getAll()));
    }

    public function store(SeasonTeamsRequest $request): JsonResponse
    {
        $season = $this->seasonService->create($request->toDTOs());

        return response()->json(SeasonResource::make($season));
    }

    public function show(Season $season): JsonResponse
    {
        return response()->json(SeasonResource::make($season));
    }
    
    public function updateMatches(Season $season, UpdateMatchesRequest $request): JsonResponse
    {
        $this->matchService->updateMatches($season, $request->validated());
    
        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
    
    public function predictions(Season $season): JsonResponse
    {
        return response()->json($this->predictionService->calculate($season));
    }
}