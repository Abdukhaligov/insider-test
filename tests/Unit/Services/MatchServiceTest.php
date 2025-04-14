<?php

namespace Tests\Unit\Services;

use App\Models\Game;
use App\Repositories\MatchRepositoryInterface;
use App\Repositories\SeasonRepositoryInterface;
use App\Services\MatchService;
use Mockery;
use Tests\TestCase;

class MatchServiceTest extends TestCase
{
    protected $matchRepository;
    protected $seasonRepository;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matchRepository = Mockery::mock(MatchRepositoryInterface::class);
        $this->seasonRepository = Mockery::mock(SeasonRepositoryInterface::class);
        $this->service = new MatchService($this->matchRepository, $this->seasonRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_should_process_returns_true_when_completed_and_scores_changed()
    {
        $oldMatch = new Game(['status' => 'completed', 'home_team_score' => 1, 'away_team_score' => 0]);
        $updatedMatch = new Game(['status' => 'completed', 'home_team_score' => 2, 'away_team_score' => 1]);

        $result = $this->invokePrivateMethod($this->service, 'shouldProcess', [$oldMatch, $updatedMatch]);
        $this->assertTrue($result);
    }

    public function test_should_process_returns_false_when_not_completed()
    {
        $oldMatch = new Game(['status' => 'completed', 'home_team_score' => 1, 'away_team_score' => 0]);
        $updatedMatch = new Game(['status' => 'pending', 'home_team_score' => 2, 'away_team_score' => 1]);

        $result = $this->invokePrivateMethod($this->service, 'shouldProcess', [$oldMatch, $updatedMatch]);
        $this->assertFalse($result);
    }

    public function test_calculate_team_delta_adds_win()
    {
        $statsDelta = [];
        $this->invokePrivateMethod($this->service, 'calculateTeamDelta', [
            &$statsDelta,
            1,
            2,
            1,
            1
        ]);

        $this->assertEquals(3, $statsDelta[1]->points);
        $this->assertEquals(1, $statsDelta[1]->won);
        $this->assertEquals(2, $statsDelta[1]->goalsFor);
    }

    public function test_process_match_delta_updates_stats_correctly()
    {
        $oldMatch = new Game([
            'home_team_id' => 1,
            'away_team_id' => 2,
            'home_team_score' => 1,
            'away_team_score' => 0,
        ]);
        $updatedMatch = new Game([
            'home_team_id' => 1,
            'away_team_id' => 2,
            'home_team_score' => 0,
            'away_team_score' => 1,
        ]);

        $statsDelta = [];
        $this->invokePrivateMethod($this->service, 'processMatchDelta', [
            &$statsDelta,
            $oldMatch,
            $updatedMatch
        ]);

        $this->assertEquals(-3, $statsDelta[1]->points);
        $this->assertEquals(3, $statsDelta[2]->points);
    }

    public function test_calculate_team_delta_skips_null_team_id()
    {
        $statsDelta = [];
        $this->invokePrivateMethod($this->service, 'calculateTeamDelta', [
            &$statsDelta,
            null,
            2,
            1,
            1
        ]);

        $this->assertEmpty($statsDelta);
    }

    private function invokePrivateMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}