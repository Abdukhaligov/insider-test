<?php

namespace App\Http\Controllers;

use App\Models\Season;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SimulateController extends Controller
{
    public function currentWeek(Season $season): JsonResponse
    {
        $matches = $season->matches()
            ->with('homeTeam', 'awayTeam')
            ->where('week', $season->week)
            ->get();

        if ($matches->isEmpty()) {
            return response()->json(
                ['error' => 'Current week has no matches to simulate'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            return DB::transaction(function () use ($season, $matches) {
                $teamStats = [];
                $season->load('teams');

                foreach ($matches as $match) {
                    $scores = MatchOrganizer::calculateMatchScore(
                        $match->homeTeam->strength,
                        $match->awayTeam->strength
                    );

                    MatchOrganizer::updateResults($match, $scores);
                    MatchOrganizer::accumulateTeamStats($teamStats, $match, $scores);
                }

                MatchOrganizer::applyTeamStatistics($season, $teamStats);
                $season->increment('week');

                return response()->json([
                    'message' => 'Week simulated successfully',
                    'new_week' => $season->week
                ], Response::HTTP_OK);
            });
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function reset(Season $season): JsonResponse
    {
        try {
            return DB::transaction(function () use ($season) {
                $season->matches()->update([
                    'home_team_score' => null,
                    'away_team_score' => null,
                    'status' => 'scheduled'
                ]);

                $season->teams()->update([
                    'points' => 0,
                    'won' => 0,
                    'lost' => 0,
                    'drawn' => 0,
                    'goals_for' => 0,
                    'goals_against' => 0,
                ]);

                $season->update(['week' => 1]);

                return response()->json(
                    ['message' => 'Season reset to initial state'],
                    Response::HTTP_OK
                );
            });
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }
}