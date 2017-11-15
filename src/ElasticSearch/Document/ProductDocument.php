<?php

declare(strict_types=1);

namespace Urbanara\CatalogPromotionPlugin\ElasticSearch\Document;

use ONGR\ElasticsearchBundle\Annotation as ElasticSearch;
use ONGR\ElasticsearchBundle\Collection\Collection;
use Sylius\ElasticSearchPlugin\Document\PriceDocument;
use Sylius\ElasticSearchPlugin\Document\ProductDocument as BaseProductDocument;
use Urbanara\CatalogPromotionPlugin\ElasticSearch\Document\VariantDocument;

/**
 * @ElasticSearch\Document(type="product")
 */
class ProductDocument extends BaseProductDocument
{
    /**
     * @var PriceDocument
     *
     * @ElasticSearch\Embedded(class="Sylius\ElasticSearchPlugin\Document\PriceDocument")
     */
    protected $originalPrice;

    /**
     * @var Collection
     *
     * @ElasticSearch\Embedded(class="Urbanara\CatalogPromotionPlugin\ElasticSearch\Document\AppliedPromotionDocument", multiple=true)
     */
    protected $appliedPromotions;

    /**
     * @var Collection
     *
     * @ElasticSearch\Embedded(class="Urbanara\CatalogPromotionPlugin\ElasticSearch\Document\VariantDocument", multiple=true)
     */
    protected $variants;

    public function __construct()
    {
        parent::__construct();

        $this->appliedPromotions = new Collection();
    }

    /**
     * @return PriceDocument
     */
    public function getOriginalPrice(): ?PriceDocument
    {
        return $this->originalPrice;
    }

    /**
     * @param PriceDocument $originalPrice
     */
    public function setOriginalPrice(PriceDocument $originalPrice)
    {
        $this->originalPrice = $originalPrice;
    }

    /**
     * @return Collection
     */
    public function getAppliedPromotions(): Collection
    {
        return $this->appliedPromotions;
    }

    /**
     * @param Collection $appliedPromotions
     */
    public function setAppliedPromotions(Collection $appliedPromotions)
    {
        $this->appliedPromotions = $appliedPromotions;
    }
}
