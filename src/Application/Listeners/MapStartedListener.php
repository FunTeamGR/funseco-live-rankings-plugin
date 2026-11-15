<?php
namespace Funseco\LiveRankings\Application\Listeners;

use Funseco\Core\SharedKernel\Events\MapStarted;
use Funseco\LiveRankings\Infrastructure\LiveRankings;

class MapStartedListener
{
    /**
     * Rankings are per round of a map: a replayed or restarted map starts empty.
     */
    public function handle(MapStarted $event): void
    {
        LiveRankings::clear();
    }
}
