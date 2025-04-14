<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{SeasonController, SimulateController, TeamController};

Route::apiResources([
    'seasons' => SeasonController::class,
    'teams' => TeamController::class,
]);

Route::prefix('seasons/{season}/simulate')->group(function () {
    Route::get('current-week', [SimulateController::class, 'currentWeek']);
    Route::get('all-weeks', [SimulateController::class, 'allWeeks']);
    Route::get('reset', [SimulateController::class, 'reset']);
});

Route::get('seasons/{season}/predictions', [SeasonController::class, 'predictions']);
Route::post('seasons/{season}/update-matches', [SeasonController::class, 'updateMatches']);