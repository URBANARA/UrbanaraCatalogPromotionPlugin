<?php

declare(strict_types=1);

namespace Urbanara\CatalogPromotionPlugin\ElasticSearch\Document;

use ONGR\ElasticsearchBundle\Annotation as ElasticSearch;
use ONGR\ElasticsearchBundle\Collection\Collection;

/**
 * @ElasticSearch\Object()
 */
class AppliedPromotionDocument
{
    /**
     * @var string
     *
     * @ElasticSearch\Property(type="keyword")
     */
    protected $code;

    /**
     * @var Collection
     *
     * @ElasticSearch\Embedded(class="Urbanara\CatalogPromotionPlugin\ElasticSearch\Document\DecorationDocument", multiple=true)
     */
    protected $decorations;

    public function __construct()
    {
        $this->decorations = new Collection();
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return Collection
     */
    public function getDecorations()
    {
        return $this->decorations;
    }

    /**
     * @param Collection $decorations
     */
    public function setDecorations(Collection $decorations)
    {
        $this->decorations = $decorations;
    }
}
