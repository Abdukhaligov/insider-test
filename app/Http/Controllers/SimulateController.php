<?php

namespace App\Http\Controllers;

use App\Exceptions\NoMatchesFoundException;
use App\Models\Season;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SimulateController extends Controller
{
    public function currentWeek(Season $season): JsonResponse
    {
        try {
            return DB::transaction(function () use ($season) {
                return response()->json([
                    'message' => 'Week simulated successfully',
                    'new_week' => MatchOrganizer::simulateWeek($season),
                ], Response::HTTP_OK);
            });
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function allWeeks(Season $season): JsonResponse
    {
        DB::beginTransaction();

        try {
            $weeksSimulated = 0;

            while (true) {
                MatchOrganizer::simulateWeek($season);
                $weeksSimulated++;
            }
        } catch (NoMatchesFoundException $e) {
            DB::commit();

            return response()->json([
                'message' => 'All remaining weeks simulated successfully',
                'weeks_simulated' => $weeksSimulated,
                'current_week' => $season->week
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on any error
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