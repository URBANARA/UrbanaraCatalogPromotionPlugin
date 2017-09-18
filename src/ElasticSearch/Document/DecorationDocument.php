<?php

declare(strict_types=1);

namespace Urbanara\CatalogPromotionPlugin\ElasticSearch\Document;

use ONGR\ElasticsearchBundle\Annotation as ElasticSearch;

/**
 * @ElasticSearch\Object()
 */
class DecorationDocument
{
    /**
     * @var string
     *
     * @ElasticSearch\Property(type="keyword")
     */
    protected $type;

    /**
     * JSON-encoded configuration array.
     *
     * @var string
     *
     * @ElasticSearch\Property(type="keyword")
     */
    protected $configuration;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param string $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }
}
