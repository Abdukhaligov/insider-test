<?php

namespace App\Http\Controllers;

use App\Exceptions\NoMatchesFoundException;
use App\Models\Season;
use App\Services\SeasonServiceInterface;
use App\Services\SimulateServiceInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SimulateController extends Controller
{
    public function __construct(protected readonly SeasonServiceInterface $seasonService, 
                                protected readonly SimulateServiceInterface $simulateService)
    {
        //
    }

    public function currentWeek(Season $season): JsonResponse
    {
        try {
            $this->simulateService->currentWeek($season);
        } catch (NoMatchesFoundException $ignored) {
            return response()->json([
                'message' => 'No matches found for simulation'
            ], status: Response::HTTP_CONFLICT);
        }

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }

    public function allWeeks(Season $season): JsonResponse
    {
        $weeksSimulated = $this->simulateService->allWeeks($season);

        return response()->json([
            'message' => 'All remaining weeks simulated successfully',
            'weeks_simulated' => $weeksSimulated
        ], Response::HTTP_OK);
    }

    public function reset(Season $season): JsonResponse
    {
        $this->seasonService->reset($season);

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}