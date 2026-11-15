<?php
namespace Funseco\LiveRankings\Application\Listeners;

use Funseco\Core\SharedKernel\Events\MapEnded;
use Funseco\LiveRankings\Infrastructure\LiveRankings;

class MapEndedListener
{
    public function handle(MapEnded $event): void
    {
        LiveRankings::clear();
        \Funseco\Core\Services\ManiaLink\Facades\ManiaLink::resetAll('live_rankings'); // not on the podium
    }
}
