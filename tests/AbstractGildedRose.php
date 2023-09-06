<?php

namespace App\Tests;

use App\GildedRoseInterface;
use App\Item;
use PHPUnit\Framework\TestCase;

abstract class AbstractGildedRose extends TestCase
{
    /**
     * @param Item[] $items
     */
    abstract protected function getGildedRose(array $items): GildedRoseInterface;

    /** @test */
    public function sellIn and quality degrades by one for a normal item(): void
    {
        // GIVEN
        $item = new Item('normal', 10, 10);
        $gildedRose = $this->getGildedRose([$item]);

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame(9, $item->sellIn);
        $this->assertSame(9, $item->quality);
    }

    /** @test */
    public function quality decrease by one when sellIn is 1 and quality is positive for normal items(): void
    {
        // GIVEN
        $item = new Item('normal', 1, 10);
        $gildedRose = $this->getGildedRose([$item]);

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame(0, $item->sellIn);
        $this->assertSame(9, $item->quality);
    }

    /**
     * @test
     * @dataProvider providesDataForNormalItemsThatQualityDoesNotGoBelow0
     */
    public function quality does not decrease by one when sellIn is passed and quality is 0 for normal items(Item $item): void
    {
        // GIVEN
        $gildedRose = $this->getGildedRose([$item]);
        $startSellIn = $item->sellIn;

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame($startSellIn - 1, $item->sellIn);
        $this->assertSame(0, $item->quality);
    }

    public static function providesDataForNormalItemsThatQualityDoesNotGoBelow0(): array
    {
        return [
            [new Item('normal', 0, 0)],
            [new Item('normal', -1, 0)],
        ];
    }

    /** @test */
    public function quality decrease by two when sellIn is negative and quality is positive for normal items(): void
    {
        // GIVEN
        $item = new Item('normal', -1, 10);
        $gildedRose = $this->getGildedRose([$item]);

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame(-2, $item->sellIn);
        $this->assertSame(8, $item->quality);
    }

    /** @test */
    public function once the sell by date has passed quality degrades twice as fast(): void
    {
        // GIVEN
        $item = new Item('normal', 0, 10);
        $gildedRose = $this->getGildedRose([$item]);

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame(-1, $item->sellIn);
        $this->assertSame(8, $item->quality);
    }

    /** @test */
    public function the quality of an item is never negative(): void
    {
        // GIVEN
        $item = new Item('normal', 10, 0);
        $gildedRose = $this->getGildedRose([$item]);

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame(9, $item->sellIn);
        $this->assertSame(0, $item->quality);
    }

    /** @test */
    public function aged brie actually increases in quality the older it gets(): void
    {
        // GIVEN
        $item = new Item('Aged Brie', 10, 10);
        $gildedRose = $this->getGildedRose([$item]);

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame(9, $item->sellIn, sprintf('SellIn should decrease by 1, got %d instead of %d', $item->sellIn, 9));
        $this->assertSame(11, $item->quality, sprintf('Quality should increase by 1, got %d instead of %d', $item->quality, 11));
    }

    /**
     * @test
     * @dataProvider providesDataForAgedBrieQualityIncreaseWhenSellInIsPassed
     */
    public function aged brie actually increases in quality the older it gets even if sellIn is passed(Item $item): void
    {
        // GIVEN
        $gildedRose = $this->getGildedRose([$item]);
        $startQuality = $item->quality;
        $startSellIn = $item->sellIn;

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame($startSellIn - 1, $item->sellIn, sprintf('SellIn should decrease by 1, got %d instead of %d', $item->sellIn, $startSellIn - 1));
        $this->assertSame($startQuality + 2, $item->quality, sprintf('Quality should increase by 2, got %d instead of %d', $item->quality, $startQuality + 2));
    }

    public static function providesDataForAgedBrieQualityIncreaseWhenSellInIsPassed(): array
    {
        return [
            [new Item('Aged Brie', 0, 10)],
            [new Item('Aged Brie', -1, 10)],
        ];
    }

    /**
     * @test
     */
    public function aged brie actually increases in quality the older it gets even if sellIn is passed but cannot be higher than 50(): void
    {
        // GIVEN
        $item = new Item('Aged Brie', -1, 50);
        $gildedRose = $this->getGildedRose([$item]);

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame(-2, $item->sellIn, sprintf('SellIn should decrease by 1, got %d instead of %d', $item->sellIn, -2));
        $this->assertSame(50, $item->quality, sprintf('Quality should not change, got %d instead of %d', $item->quality, 50));
    }

    /** @test */
    public function the quality of an item is never more than 50(): void
    {
        // GIVEN
        $item = new Item('Aged Brie', 10, 50);
        $gildedRose = $this->getGildedRose([$item]);

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame(9, $item->sellIn);
        $this->assertSame(50, $item->quality);
    }

    /** @test */
    public function sulfuras being a legendary item never has to be sold or decreases in quality(): void
    {
        // GIVEN
        $item = new Item('Sulfuras, Hand of Ragnaros', 10, 80);
        $gildedRose = $this->getGildedRose([$item]);

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame(10, $item->sellIn);
        $this->assertSame(80, $item->quality);
    }

    /** @test */
    public function backstage passes quality increases by 1 when there are 11 days or more(): void
    {
        // GIVEN
        $item = new Item('Backstage passes to a TAFKAL80ETC concert', 11, 10);
        $gildedRose = $this->getGildedRose([$item]);

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame(10, $item->sellIn);
        $this->assertSame(11, $item->quality);
    }

    /**
     * @test
     * @dataProvider provideItemsForBackstage10daysOrLess
     */
    public function backstage passes quality increases by 2 when there are 10 days or less(Item $item): void
    {
        // GIVEN
        $gildedRose = $this->getGildedRose([$item]);
        $startQuality = $item->quality;
        $startSellIn = $item->sellIn;

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame($startSellIn - 1, $item->sellIn);
        $this->assertSame($startQuality + 2, $item->quality);
    }

    public static function provideItemsForBackstage10daysOrLess(): array
    {
        return [
            [new Item('Backstage passes to a TAFKAL80ETC concert', 10, 10)],
            [new Item('Backstage passes to a TAFKAL80ETC concert', 6, 10)],
        ];
    }

    /** @test */
    public function backstage passes quality increases by 3 when there are 5 days or less(): void
    {
        // GIVEN
        $item = new Item('Backstage passes to a TAFKAL80ETC concert', 5, 10);
        $gildedRose = $this->getGildedRose([$item]);

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame(4, $item->sellIn);
        $this->assertSame(13, $item->quality);
    }

    /** @test */
    public function backstage passes quality does not increase if quality is 50 or more(): void
    {
        // GIVEN
        $item = new Item('Backstage passes to a TAFKAL80ETC concert', 5, 50);
        $gildedRose = $this->getGildedRose([$item]);

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame(4, $item->sellIn);
        $this->assertSame(50, $item->quality);
    }

    /** @test */
    public function backstage passes quality drops to 0 after the concert(): void
    {
        // GIVEN
        $item = new Item('Backstage passes to a TAFKAL80ETC concert', 0, 10);
        $gildedRose = $this->getGildedRose([$item]);

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame(-1, $item->sellIn);
        $this->assertSame(0, $item->quality);
    }

    /** @test */
    public function multiple items can be updated(): void
    {
        // GIVEN
        $backstagePasses = new Item('Backstage passes to a TAFKAL80ETC concert', 5, 10);
        $sulfuras = new Item('Sulfuras, Hand of Ragnaros', 10, 80);
        $agedBrie = new Item('Aged Brie', 10, 10);
        $normal = new Item('normal', 10, 10);
        $items = [$backstagePasses, $sulfuras, $agedBrie, $normal];
        $gildedRose = $this->getGildedRose($items);

        // WHEN
        $gildedRose->updateQuality();

        // THEN
        $this->assertSame(4, $backstagePasses->sellIn);
        $this->assertSame(13, $backstagePasses->quality);
        $this->assertSame(10, $sulfuras->sellIn);
        $this->assertSame(80, $sulfuras->quality);
        $this->assertSame(9, $agedBrie->sellIn);
        $this->assertSame(11, $agedBrie->quality);
        $this->assertSame(9, $normal->sellIn);
        $this->assertSame(9, $normal->quality);
    }
}
