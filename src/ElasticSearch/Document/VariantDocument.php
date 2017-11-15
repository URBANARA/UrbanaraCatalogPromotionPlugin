<?php

declare(strict_types=1);

namespace Urbanara\CatalogPromotionPlugin\ElasticSearch\Document;

use ONGR\ElasticsearchBundle\Annotation as ElasticSearch;
use Sylius\ElasticSearchPlugin\Document\PriceDocument;
use Sylius\ElasticSearchPlugin\Document\VariantDocument as BaseVariantDocument;

/**
 * @ElasticSearch\Nested
 */
class VariantDocument extends BaseVariantDocument
{

    /**
     * @var PriceDocument|null
     *
     * @ElasticSearch\Embedded(class="Sylius\ElasticSearchPlugin\Document\PriceDocument")
     */
    protected $originalPrice;

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


}
