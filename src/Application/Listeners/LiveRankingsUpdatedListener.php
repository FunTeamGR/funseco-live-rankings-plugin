<?php

namespace Funseco\LiveRankings\Application\Listeners;

use Funseco\Core\Services\Cache\CurrentMap;
use Funseco\Core\Services\Cache\CurrentPlayers;
use Funseco\Core\Services\Funseco\Facades\Funseco;
use Funseco\Core\SharedKernel\Events\LiveRankingsUpdated;
use Funseco\LiveRankings\Presentation\LiveRankingsWidget;

class LiveRankingsUpdatedListener
{
    public function handle(LiveRankingsUpdated $event): void
    {
        if (Funseco::getGameStatus() === 4 && CurrentMap::get()) {
            foreach (CurrentPlayers::all() as $player) {
                LiveRankingsWidget::render($player);
            }
        }
    }
}
