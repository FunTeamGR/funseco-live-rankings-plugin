<?php

namespace Funseco\LiveRankings\Application\Listeners;

use Funseco\Core\Services\Cache\CurrentMap;
use Funseco\Core\Services\Cache\CurrentPlayers;
use Funseco\Core\Services\Funseco\Facades\Funseco;
use Funseco\Core\SharedKernel\Events\StartupEvent;
use Funseco\LiveRankings\Infrastructure\LiveRankings;
use Funseco\LiveRankings\Presentation\LiveRankingsWidget;

class StartupListener
{
    public function handle(StartupEvent $event): void
    {
        \Log::info('[restore] startup status='.Funseco::getGameStatus());
        if (Funseco::getGameStatus() !== 4) {
            \ManiaLink::resetAll('live_rankings');
            return;
        }

        if (CurrentMap::get()) {
            foreach (CurrentPlayers::all() as $player) {
                LiveRankingsWidget::render($player);
            }
        }

        // Restarted mid race: take the standings back from the game server.
        \Server::query()->GetCurrentRanking(300, 0, function ($result) {
            $rows = is_array($result[0] ?? null) ? $result[0] : [];
            LiveRankings::restore(array_values(array_filter($rows, 'is_array')));
        });
    }
}
