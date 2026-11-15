<?php

namespace Funseco\LiveRankings\Application\Listeners;

use Funseco\Core\Services\Cache\CurrentMap;
use Funseco\Core\Services\Funseco\Facades\Funseco;
use Funseco\Core\SharedKernel\Events\PlayerConnected;
use Funseco\Core\SharedKernel\Events\PlayerReady;
use Funseco\LiveRankings\Presentation\LiveRankingsWidget;

class PlayerConnectListener
{
    public function handle(PlayerConnected|PlayerReady $event): void
    {
        if (Funseco::getGameStatus() === 4 && CurrentMap::get()) {
            LiveRankingsWidget::render($event->player);
        }
    }
}
