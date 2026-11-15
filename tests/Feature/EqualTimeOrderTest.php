<?php
namespace Funseco\LiveRankings\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Funseco\LiveRankings\Tests\TestCase;
use Funseco\Core\Models\Map;
use Funseco\Core\Models\Player;
use Funseco\Core\Services\Cache\CurrentMap;
use Funseco\Core\Services\Cache\CurrentPlayers;
use Funseco\Core\SharedKernel\Events\LiveRankingsUpdated;
use Funseco\LiveRankings\Infrastructure\LiveRankings;

/** On equal scores the earlier time keeps the better place; a later equal time never overtakes it. */
class EqualTimeOrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::forget(LiveRankingsUpdated::class);
        CurrentMap::set(Map::factory()->make(['id' => 1]));
    }

    private function players(int $n): array
    {
        $out = [];
        for ($i = 1; $i <= $n; $i++) {
            $out[] = $p = Player::factory()->make(['id' => $i, 'login' => "p$i"]);
            CurrentPlayers::add($p);
        }
        return $out;
    }

    private function logins(): array
    {
        return LiveRankings::all()->map(fn ($r) => $r->player->login)->values()->all();
    }

    public function test_improving_to_an_existing_time_ranks_below_the_earlier_one(): void
    {
        $this->players(2);
        LiveRankings::update('p1', 32000);
        LiveRankings::update('p2', 30000);
        LiveRankings::update('p1', 30000);

        $this->assertSame(['p2', 'p1'], $this->logins());
    }

    public function test_fifty_equal_times_keep_arrival_order(): void
    {
        $this->players(51);
        LiveRankings::update('p1', 40000);
        for ($i = 2; $i <= 51; $i++) {
            LiveRankings::update("p$i", 30000);
        }
        LiveRankings::update('p1', 30000);

        $expected = [...array_map(fn ($i) => "p$i", range(2, 51)), 'p1'];
        $this->assertSame($expected, $this->logins());
    }
}
