<?php

namespace App\Http\Controllers;

use App\Http\Resources\SeasonResource;
use App\Models\Season;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeasonController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(SeasonResource::collection(Season::all()));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $season = Season::create(['date' => now()]);
                $teams = MatchOrganizer::registerTeams($season, $request->input('teams'));

                MatchOrganizer::scheduleMatches($season, $teams);

                return response()->json(['id' => $season->id]);
            });
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function show(Season $season): JsonResponse
    {
        return response()->json(SeasonResource::make($season));
    }

    public function update(Request $request, Season $season)
    {
        // Implementation remains unchanged
    }

    public function destroy(Season $season)
    {
        // Implementation remains unchanged
    }
}