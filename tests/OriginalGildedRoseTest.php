<?php

namespace App\Tests;

use App\GildedRose;
use App\GildedRoseInterface;
use App\Item;

final class OriginalGildedRoseTest extends AbstractGildedRose
{
    /**
     * @param Item[] $items
     */
    protected function getGildedRose(array $items): GildedRoseInterface
    {
        return new GildedRose($items);
    }
}
