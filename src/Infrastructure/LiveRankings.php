<?php
namespace Funseco\LiveRankings\Infrastructure;

use Funseco\Core\Services\Cache\CurrentMap;
use Funseco\Core\Services\Cache\CurrentPlayers;
use Funseco\Core\SharedKernel\Events\LiveRankingsUpdated;
use Funseco\Core\SharedKernel\Values\Record;
use Funseco\Core\SharedKernel\Values\RecordCollection;

class LiveRankings
{
    public static function all(): RecordCollection
    {
        return state()->get(self::key(), new RecordCollection())->sortByRank();
    }

    public static function get(int $playerId): Record
    {
        return self::all()->first(
            fn($record) => $record->playerId() === $playerId,
            new Record(0, 0, 0, true)
        );
    }

    public static function update(string $login, int $score): void
    {
        $player   = CurrentPlayers::get($login);
        $previous = self::get($player->id);

        if ($previous->hasNoTime || $score < $previous->score()) {
            // a new or improved time ranks after every existing time with the same score
            $tiebreaker = self::all()->nextTiebreaker();

            $record         = new Record($player->id, $score, $tiebreaker);
            $record->player = $player;

            state()->forever(self::key(), self::all()
                ->keyBy(fn($r) => $r->playerId())
                ->put($player->id, $record)
                ->sortByRank()
            );

            event(new LiveRankingsUpdated);
        }
    }

    /** Rebuild from the server's GetCurrentRanking rows (after a controller restart mid race). */
    public static function restore(array $rows): void
    {
        \Log::info('[restore] live rows='.count($rows).' first='.json_encode($rows[0] ?? null).' map='.CurrentMap::get()?->id);
        $records = new RecordCollection();
        $i = 0;
        foreach ($rows as $row) {
            $time = (int) ($row['BestTime'] ?? -1);
            $player = CurrentPlayers::get((string) ($row['Login'] ?? ''));
            if ($time <= 0 || !$player) {
                continue;
            }
            $record = new Record($player->id, $time, $i++);
            $record->player = $player;
            $records->put($player->id, $record);
        }
        \Log::info('[restore] live records='.$records->count());
        if ($records->isEmpty()) {
            return;
        }
        state()->forever(self::key(), $records->sortByRank());
        event(new LiveRankingsUpdated);
    }

    public static function clear(): void
    {
        state()->forget(self::key());
    }

    public static function key(): string
    {
        return 'liveRankings:' . CurrentMap::get()?->id;
    }
}
