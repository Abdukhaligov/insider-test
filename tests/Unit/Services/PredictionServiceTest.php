<?php

namespace Tests\Unit\Services;

use App\DTO\TeamPredictionDTO;
use App\Models\Season;
use App\Services\PredictionService;
use Tests\TestCase;

class PredictionServiceTest extends TestCase
{
    public function test_calculate_predictions_for_two_teams()
    {
        // Create mock teams
        $teamA = $this->createTeam(10, 5, 2, 'Team A');
        $teamB = $this->createTeam(6, 2, 1, 'Team B');

        $season = $this->mockSeason([$teamA, $teamB]);
        $service = new PredictionService();
        $result = $service->calculate($season);

        $this->assertCount(2, $result);
        $this->assertEqualsWithDelta(100, $result->sum('percentage'), 0.01);

        $teamAResult = $result->where('name', 'Team A')->first();
        $this->assertEqualsWithDelta(62.86, $teamAResult->percentage, 0.01);

        $teamBResult = $result->where('name', 'Team B')->first();
        $this->assertEqualsWithDelta(37.14, $teamBResult->percentage, 0.01);

        $this->assertEquals('Team A', $result[0]->name);
        $this->assertEquals('Team B', $result[1]->name);
    }

    public function test_all_teams_have_zero_scores()
    {
        $teams = [
            $this->createTeam(0, 0, 0, 'Team A'),
            $this->createTeam(0, 0, 0, 'Team B'),
            $this->createTeam(0, 0, 0, 'Team C'),
        ];

        $season = $this->mockSeason($teams);
        $service = new PredictionService();
        $result = $service->calculate($season);

        $this->assertCount(3, $result);
        $result->each(function (TeamPredictionDTO $dto) {
            $this->assertEqualsWithDelta(33.33, $dto->percentage, 0.01);
        });
        $this->assertEqualsWithDelta(99.99, $result->sum('percentage'), 0.01);
    }

    public function test_single_team_has_100_percent()
    {
        $team = $this->createTeam(0, 0, 0, 'Team Solo');
        $season = $this->mockSeason([$team]);
        $service = new PredictionService();
        $result = $service->calculate($season);

        $this->assertCount(1, $result);
        $this->assertEquals(100.0, $result->first()->percentage);
    }

    public function test_teams_with_equal_scores()
    {
        $teams = [
            $this->createTeam(10, 0, 0, 'Team A'),
            $this->createTeam(10, 0, 0, 'Team B'),
        ];

        $season = $this->mockSeason($teams);
        $service = new PredictionService();
        $result = $service->calculate($season);

        $this->assertCount(2, $result);
        $result->each(function (TeamPredictionDTO $dto) {
            $this->assertEqualsWithDelta(50.0, $dto->percentage, 0.01);
        });
        $this->assertEquals(100.0, $result->sum('percentage'));
    }

    public function test_percentage_rounding()
    {
        $teams = [
            $this->createTeam(1, 0, 0, 'Team A'),
            $this->createTeam(1, 0, 0, 'Team B'),
            $this->createTeam(1, 0, 0, 'Team C'),
        ];

        $season = $this->mockSeason($teams);
        $service = new PredictionService();
        $result = $service->calculate($season);

        $result->each(function (TeamPredictionDTO $dto) {
            $this->assertEquals(33.33, $dto->percentage);
        });
        $this->assertEquals(99.99, $result->sum('percentage'));
    }

    public function test_sorting_order_descending()
    {
        $teams = [
            $this->createTeam(10, 0, 0, 'Team A'),
            $this->createTeam(20, 0, 0, 'Team B'),
        ];

        $season = $this->mockSeason($teams);
        $service = new PredictionService();
        $result = $service->calculate($season);

        $this->assertEquals('Team B', $result[0]->name);
        $this->assertEqualsWithDelta(66.67, $result[0]->percentage, 0.01);
        $this->assertEquals('Team A', $result[1]->name);
        $this->assertEqualsWithDelta(33.33, $result[1]->percentage, 0.01);
    }

    private function createTeam(int $points, int $goalDifference, int $strength, string $name): object
    {
        $team = new \stdClass();
        $team->points = $points;
        $team->goal_difference = $goalDifference;
        $team->strength = $strength;
        $team->team = new \stdClass();
        $team->team->name = $name;

        return $team;
    }

    private function mockSeason(array $teams): Season
    {
        $season = $this->createMock(Season::class);
        $season->method('__get')
            ->with('teams')
            ->willReturn(collect($teams));

        return $season;
    }
}