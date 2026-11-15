<?php
namespace Funseco\LiveRankings\Application\Listeners;

use Funseco\Core\SharedKernel\Events\PlayerFinished;
use Funseco\LiveRankings\Infrastructure\LiveRankings;

class PlayerFinishedListener
{
    public function handle(PlayerFinished $event): void
    {
        LiveRankings::update($event->player->login, $event->score);
    }
}
