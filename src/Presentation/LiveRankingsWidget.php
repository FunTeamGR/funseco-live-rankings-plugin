<?php

namespace Funseco\LiveRankings\Presentation;

use Funseco\Core\Models\Player;
use Funseco\Core\Services\Cache\CurrentPlayers;
use Funseco\Core\Services\ManiaLink\Facades\ManiaLink;
use Funseco\Core\SharedKernel\UI\RankedListPanel;
use Funseco\Core\SharedKernel\UI\Region;
use Funseco\Core\SharedKernel\UI\WidgetRegistry;
use Funseco\Core\SharedKernel\Values\RecordCollection;
use Funseco\LiveRankings\Infrastructure\LiveRankings;

class LiveRankingsWidget
{
    /** Config key under manialinks.php and WidgetRegistry name. */
    public const NAME = 'live_rankings';

    /** Default layout; config can override region/order/entries per gamemode. */
    public static function register(WidgetRegistry $registry): void
    {
        $registry->regionWidget(
            self::NAME,
            Region::MidRight,
            order: 20,
            width: (float) config('manialinks.' . self::NAME . '.width', 15.5),
            height: fn (int $entries) => RankedListPanel::height($entries),
        );
    }

    public static function render(Player $player, ?RecordCollection $records = null): void
    {
        // Position and entries for the current gamemode come from the WidgetRegistry.
        if (app(\Funseco\Core\SharedKernel\RecordWidgetVisibility::class)->hidden($player)) {
            ManiaLink::resetLogin($player, 'live_rankings');
            return;
        }
        $placement = app(WidgetRegistry::class)->placement(self::NAME);
        if ($placement === null) {
            // Disabled in this gamemode: clear it in case it was shown under another mode.
            ManiaLink::resetLogin($player, 'live_rankings');
            return;
        }

        $cfg      = config('manialinks.' . self::NAME);
        $topCount = (int) ($placement->settings['topcount'] ?? 3);
        $limit    = $placement->entries;

        $records = ($records ?? LiveRankings::all()->values())->forPlayer(
            $player,
            $limit,
            $limit,
            $topCount
        );

        $markOnline = filter_var(config('manialinks.mark_online_player_records', 'true'), FILTER_VALIDATE_BOOLEAN);
        $onlineIds  = CurrentPlayers::all()->pluck('id')->flip();

        $rows = $records->map(fn($record) => [
            'rank'  => (string) $record->rank,
            'label' => $record->player?->nickname ?? $player->nickname,
            'value' => format_score($record->score),
            'mine'  => (bool) ($record->mine ?? false),
            'online' => $markOnline && $onlineIds->has($record->player_id ?? 0),
        ]);

        $width = (float) $cfg['width'];

        $xml = RankedListPanel::render(
            x:            $placement->x,
            y:            $placement->y,
            title:        $cfg['title'],
            rows:         $rows,
            manialinkId:  ManiaLink::getAction('live_rankings'),
            actionId:     ManiaLink::getAction('live_rankings'),
            iconStyle:    $cfg['icon_style'],
            iconSubStyle: $cfg['icon_substyle'],
            width:        $width,
            entries:      $limit,
            topCount:     $topCount,
            theme:        app(\Funseco\Core\SharedKernel\UI\Theme\ThemeRegistry::class)->forPlayer($player),
        );

        ManiaLink::displayLogin($player, $xml);
    }
}
