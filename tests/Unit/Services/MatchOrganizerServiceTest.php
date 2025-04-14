<?php

namespace Tests\Unit\Services;

use App\Models\Season;
use App\Repositories\MatchRepositoryInterface;
use App\Services\MatchOrganizerService;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class MatchOrganizerServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $matchRepository;
    protected $season;
    protected $service;

    protected function setUp(): void
    {
        $this->matchRepository = Mockery::mock(MatchRepositoryInterface::class);
        $this->season = Mockery::mock(Season::class);
        $this->service = new MatchOrganizerService($this->matchRepository);
    }

    /** @dataProvider teamCountProvider */
    public function testGeneratesCorrectNumberOfMatches(int $teamCount, int $expectedTotalMatches): void
    {
        $teams = Collection::times($teamCount, fn($i) => (object)['team_id' => $i]);

        $this->matchRepository
            ->shouldReceive('create')
            ->times($expectedTotalMatches);

        $this->service->generateSchedule($this->season, $teams);
    }

    public static function teamCountProvider(): array
    {
        return [
            '2_teams' => [2, 2], // 2 matches total
            '4_teams' => [4, 12], // 12 matches total
            '5_teams' => [5, 20], // 20 matches total
        ];
    }

    public function testHomeAwayAlternatesBasedOnWeekParity(): void
    {
        $teams = new Collection([(object)['team_id' => 1], (object)['team_id' => 2]]);

        // Expect 2 matches with alternating home/away
        $this->matchRepository
            ->shouldReceive('create')
            ->with($this->season, 1, 2, 1)
            ->once();

        $this->matchRepository
            ->shouldReceive('create')
            ->with($this->season, 2, 1, 2)
            ->once();

        $this->service->generateSchedule($this->season, $teams);
    }

    public function testSkipMatchesWithSameTeam(): void
    {
        $teams = new Collection([
            (object)['team_id' => 1],
            (object)['team_id' => 2],
            (object)['team_id' => 3], // Middle team will pair with itself in some rounds
        ]);

        // 3 teams = 6 weeks × 1 valid match/week = 6 total matches
        $this->matchRepository
            ->shouldReceive('create')
            ->times(6);

        $this->service->generateSchedule($this->season, $teams);
    }

    public function testTeamRotationLogic(): void
    {
        $teams = new Collection([
            (object)['team_id' => 1],
            (object)['team_id' => 2],
            (object)['team_id' => 3],
            (object)['team_id' => 4],
        ]);

        $rotated1 = $this->invokePrivateMethod('rotateTeams', $teams);
        $this->assertEquals([1, 3, 4, 2], $rotated1->pluck('team_id')->toArray());

        $rotated2 = $this->invokePrivateMethod('rotateTeams', $rotated1);
        $this->assertEquals([1, 4, 2, 3], $rotated2->pluck('team_id')->toArray());
    }

    private function invokePrivateMethod(string $methodName, Collection $teams): Collection
    {
        $reflection = new \ReflectionClass(MatchOrganizerService::class);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($this->service, [$teams]);
    }
}