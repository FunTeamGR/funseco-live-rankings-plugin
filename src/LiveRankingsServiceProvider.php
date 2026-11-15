<?php

namespace Funseco\LiveRankings;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Funseco\Core\SharedKernel\UI\WidgetRegistry;
use Funseco\LiveRankings\Presentation\LiveRankingsWidget;
use Funseco\Core\Services\ManiaLink\ManiaLinkService;
use Funseco\Core\SharedKernel\Events\GameStatusChanged;
use Funseco\Core\SharedKernel\Events\LiveRankingsUpdated;
use Funseco\Core\SharedKernel\Events\MapEnded;
use Funseco\Core\SharedKernel\Events\MapStarted;
use Funseco\Core\SharedKernel\Events\PlayerConnected;
use Funseco\Core\SharedKernel\Events\PlayerFinished;
use Funseco\Core\SharedKernel\Events\StartupEvent;
use Funseco\LiveRankings\Application\Listeners\GameStatusChangedListener;
use Funseco\LiveRankings\Application\Listeners\LiveRankingsUpdatedListener;
use Funseco\LiveRankings\Application\Listeners\MapEndedListener;
use Funseco\LiveRankings\Application\Listeners\MapStartedListener;
use Funseco\LiveRankings\Application\Listeners\PlayerConnectListener;
use Funseco\LiveRankings\Application\Listeners\PlayerFinishedListener;
use Funseco\LiveRankings\Application\Listeners\StartupListener;

class LiveRankingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        app(ManiaLinkService::class)->addActions(['live_rankings']);
        LiveRankingsWidget::register(app(WidgetRegistry::class));
        \Chat::register(\Funseco\LiveRankings\Commands\LiveRankingsCommand::class);

        Event::listen(StartupEvent::class,        StartupListener::class);
        Event::listen(LiveRankingsUpdated::class, LiveRankingsUpdatedListener::class);
        Event::listen([PlayerConnected::class, \Funseco\Core\SharedKernel\Events\PlayerReady::class], PlayerConnectListener::class);
        Event::listen(GameStatusChanged::class,   GameStatusChangedListener::class);
        Event::listen(PlayerFinished::class,      PlayerFinishedListener::class);
        Event::listen(\Funseco\Core\SharedKernel\Events\RecordWidgetsToggled::class, fn ($e) => LiveRankingsWidget::render($e->player));
        Event::listen(MapStarted::class,          MapStartedListener::class);
        Event::listen(MapEnded::class,            MapEndedListener::class);
    }
}
