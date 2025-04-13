<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\{Request, JsonResponse};

class TeamController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Team::all());
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Team $team)
    {
        //
    }

    public function update(Request $request, Team $team)
    {
        //
    }

    public function destroy(Team $team)
    {
        //
    }
}