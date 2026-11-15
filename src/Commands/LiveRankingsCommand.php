<?php

namespace Funseco\LiveRankings\Commands;

use Funseco\Core\Models\Player;
use Funseco\Core\SharedKernel\Commands\Command;
use Funseco\Core\SharedKernel\Commands\DeclaresCommand;
use Funseco\Core\SharedKernel\Commands\Described;
use Funseco\Core\SharedKernel\UI\Window;
use Funseco\LiveRankings\Infrastructure\LiveRankings;

/**
 * The live rankings window (opened by /liverankings, by a click on the live rankings widget and
 * by the eyepiece current-ranking widget): every player's best time of the running track.
 */
class LiveRankingsCommand implements Command, Described
{
    use DeclaresCommand;

    protected string $name = 'liverankings';
    protected string $description = 'Shows the live rankings of the current track';

    public function handle(Player $player, array $args): void
    {
        $rows = [];
        foreach (LiveRankings::all()->values() as $i => $record) {
            $rows[] = [
                sprintf("%02d.", $i + 1),
                '{#black}'.str_replace(["\r", "\n"], '', (string) $record->player?->nickname),
                format_score($record->score()),
            ];
        }
        if ($rows === []) {
            \Server::query()->ChatSendServerMessageToLogin(format_colors('{#server}> {#error}No live rankings yet...'), $player->login);

            return;
        }
        Window::show($player, 'Live Rankings:', $rows, [1.0, 0.1, 0.65, 0.25], ['BgRaceScore2', 'LadderRank']);
    }
}
