<?php
namespace Funseco\LiveRankings\Application\Listeners;

use Funseco\Core\Services\Cache\CurrentMap;
use Funseco\Core\Services\Cache\CurrentPlayers;
use Funseco\Core\Services\ManiaLink\Facades\ManiaLink;
use Funseco\Core\SharedKernel\Events\GameStatusChanged;
use Funseco\LiveRankings\Presentation\LiveRankingsWidget;

class GameStatusChangedListener
{
    public function handle(GameStatusChanged $event): void
    {
        if (!$event->isPlaying()) {
            ManiaLink::resetAll('live_rankings');
        } elseif (CurrentMap::get()) {
            foreach (CurrentPlayers::all() as $player) {
                LiveRankingsWidget::render($player);
            }
        }
    }
}
