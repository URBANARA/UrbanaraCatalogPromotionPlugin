<?php

declare(strict_types=1);

namespace Urbanara\CatalogPromotionPlugin\ElasticSearch\Factory;

use ONGR\FilterManagerBundle\Search\SearchResponse;
use Sylius\ElasticSearchPlugin\Controller\ProductListView;
use Sylius\ElasticSearchPlugin\Factory\View\ProductListViewFactoryInterface;
use Urbanara\CatalogPromotionPlugin\ElasticSearch\View\AppliedPromotionView;
use Urbanara\CatalogPromotionPlugin\ElasticSearch\View\DecorationView;
use Urbanara\CatalogPromotionPlugin\ElasticSearch\View\PriceView;
use Urbanara\CatalogPromotionPlugin\ElasticSearch\View\VariantView;
use Urbanara\CatalogPromotionPlugin\ElasticSearch\Document\AppliedPromotionDocument;
use Urbanara\CatalogPromotionPlugin\ElasticSearch\Document\DecorationDocument;
use Urbanara\CatalogPromotionPlugin\ElasticSearch\Document\ProductDocument;

final class ProductListViewFactory implements ProductListViewFactoryInterface
{
    /** @var ProductListViewFactoryInterface */
    private $decoratedFactory;

    /** @var string */
    private $appliedPromotionViewClass;

    /** @var string */
    private $decorationViewClass;

    /**
     * @param ProductListViewFactoryInterface $decoratedFactory
     * @param string $appliedPromotionViewClass
     * @param string $decorationViewClass
     */
    public function __construct(
        ProductListViewFactoryInterface $decoratedFactory,
        $appliedPromotionViewClass,
        $decorationViewClass
    ) {
        $this->decoratedFactory = $decoratedFactory;
        $this->appliedPromotionViewClass = $appliedPromotionViewClass;
        $this->decorationViewClass = $decorationViewClass;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromSearchResponse(SearchResponse $response): ProductListView
    {
        /** @var ProductListView $productListView */
        $productListView = $this->decoratedFactory->createFromSearchResponse($response);

        /** @var ProductDocument $productDocument */
        foreach ($response->getResult() as $productDocument) {
            foreach ($productListView->items as $productView) {
                if ($productView->code !== $productDocument->getCode()) {
                    continue;
                }

                /** @var VariantView $variantView */
                $variantView = current($productView->variants);

                /** @var PriceView $priceView */
                $priceView = $variantView->price;
                $priceView->original = $productDocument->getOriginalPrice() ? $productDocument->getOriginalPrice()->getAmount() : null;

                $variantView->appliedPromotions = array_map(function (AppliedPromotionDocument $appliedPromotionDocument) {
                    /** @var AppliedPromotionView $appliedPromotionView */
                    $appliedPromotionView = new $this->appliedPromotionViewClass();
                    $appliedPromotionView->code = $appliedPromotionDocument->getCode();
                    $appliedPromotionView->decorations = array_map(function (DecorationDocument $decorationDocument) {
                        /** @var DecorationView $decorationView */
                        $decorationView = new $this->decorationViewClass();
                        $decorationView->type = $decorationDocument->getType();
                        $decorationView->configuration = json_decode($decorationDocument->getConfiguration(), true) ?: [];

                        return $decorationView;
                    }, iterator_to_array($appliedPromotionDocument->getDecorations()));

                    return $appliedPromotionView;
                }, iterator_to_array($productDocument->getAppliedPromotions()));
            }
        }

        return $productListView;
    }
}
