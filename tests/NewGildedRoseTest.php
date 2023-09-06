<?php

namespace App\Tests;

use App\GildedRoseInterface;
use App\Item;
use App\NewGildedRose;

final class NewGildedRoseTest extends AbstractGildedRose
{
    /**
     * @param Item[] $items
     */
    protected function getGildedRose(array $items): GildedRoseInterface
    {
        return new NewGildedRose($items);
    }

    /**
     * @test
     * @dataProvider providesDataForConjuredItems
     */
    public function conjured items degrade in quality twice as fast as normal items(Item $item, int $expectedQuality): void
    {
        // GIVEN
        $gildedRose = $this->getGildedRose([$item]);
        $startSellIn = $item->sellIn;

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame($startSellIn - 1, $item->sellIn, 'SellIn should decrease by 1');
        $this->assertSame($expectedQuality, $item->quality, 'Quality should decrease by 2');
    }

    public static function providesDataForConjuredItems(): array
    {
        return [
            [
                'item' => new Item('Conjured Bread', 1, 10),
                'expectedQuality' => 8,
            ],
            [
                'item' => new Item('Conjured Mana Cake', 0, 10),
                'expectedQuality' => 6,
            ],
            [
                'item' => new Item('Conjured Water', -1, 10),
                'expectedQuality' => 6,
            ],
        ];
    }
}
