<?php

namespace Funseco\LiveRankings\Tests;

use Funseco\Core\Testing\TestCase as CoreTestCase;
use Funseco\LiveRankings\LiveRankingsServiceProvider;

abstract class TestCase extends CoreTestCase
{
    /** Dependencies first, then this plugin (isolated runs load only these). */
    protected function plugins(): array
    {
        return [
            LiveRankingsServiceProvider::class,
        ];
    }
}
