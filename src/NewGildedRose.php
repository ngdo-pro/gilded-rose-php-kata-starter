<?php

namespace App;

final readonly class NewGildedRose implements GildedRoseInterface
{
    private const SULFURAS_HAND_OF_RAGNAROS = 'Sulfuras, Hand of Ragnaros';
    private const BACKSTAGE_PASSES = 'Backstage passes to a TAFKAL80ETC concert';
    private const AGED_BRIE = 'Aged Brie';

    /** @var Item[] */
    private array $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function updateQuality(): void
    {
        foreach ($this->items as $item) {
            if ($item->name === self::SULFURAS_HAND_OF_RAGNAROS) {
                continue;
            }

            $item->quality = match (true) {
                $item->name === self::BACKSTAGE_PASSES => $this->handleBackstagePassQuality($item),
                $item->name === self::AGED_BRIE => $this->handleAgedBrieQuality($item),
                $this->isItemConjured($item) => $this->handleConjuredItemQuality($item),
                default => $this->handleNormalItemQuality($item),
            };

            $item->sellIn--;
        }
    }

    private function handleBackstagePassQuality(Item $item): int
    {
        return match (true) {
            $item->sellIn <= 0 => 0,
            $item->sellIn <= 5 => $this->getUpdatedItemQualityByDelta($item, 3),
            $item->sellIn <= 10 => $this->getUpdatedItemQualityByDelta($item, 2),
            default => $this->getUpdatedItemQualityByDelta($item, 1),
        };
    }

    private function handleAgedBrieQuality(Item $item): int
    {
        return $this->handleItemQualityBasedOnSellIn($item, 1, 2);
    }

    private function handleConjuredItemQuality(Item $item): int
    {
        return $this->handleItemQualityBasedOnSellIn($item, -2, -4);
    }

    private function handleNormalItemQuality(Item $item): int
    {
        return $this->handleItemQualityBasedOnSellIn($item, -1, -2);
    }

    private function isItemConjured(Item $item): bool
    {
        return str_contains($item->name, 'Conjured');
    }

    private function handleItemQualityBasedOnSellIn(Item $item, int $normalDelta, int $expiredDelta): int
    {
        $delta = $item->sellIn <= 0 ? $expiredDelta : $normalDelta;
        return $this->getUpdatedItemQualityByDelta($item, $delta);
    }

    private function getUpdatedItemQualityByDelta(Item $item, int $delta): int
    {
        // Ensure the range (0 - 50) is maintained
        return max(min($item->quality + $delta, 50), 0);
    }
}
