<?php
namespace Funseco\LiveRankings\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Funseco\LiveRankings\Tests\TestCase;
use Funseco\Core\Models\Map;
use Funseco\Core\Models\Player;
use Funseco\Core\Services\Cache\CurrentMap;
use Funseco\Core\Services\Cache\CurrentPlayers;
use Funseco\Core\SharedKernel\Events\LiveRankingsUpdated;
use Funseco\Core\SharedKernel\Events\MapStarted;
use Funseco\LiveRankings\Application\Listeners\MapStartedListener;
use Funseco\LiveRankings\Infrastructure\LiveRankings;

class LiveRankingsLifecycleTest extends TestCase
{
    public function test_map_start_clears_rankings_left_on_that_map()
    {
        Event::forget(LiveRankingsUpdated::class); // widget rendering needs a socket
        $map = Map::factory()->make(['id' => 1]);
        CurrentMap::set($map);
        CurrentPlayers::add(Player::factory()->make(['id' => 1, 'login' => 'racer']));
        LiveRankings::update('racer', 30000);

        $this->assertContains(MapStartedListener::class, app('events')->getRawListeners()[MapStarted::class]);
        app(MapStartedListener::class)->handle(new MapStarted($map, false)); // same map again, e.g. restart

        $this->assertCount(0, LiveRankings::all());
    }
}
