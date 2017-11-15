<?php

declare(strict_types=1);

namespace Urbanara\CatalogPromotionPlugin\Action;

use Urbanara\CatalogPromotionPlugin\Form\Type\Action\PercentageCatalogDiscountType;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Webmozart\Assert\Assert;

final class PercentageCatalogDiscountCommand implements CatalogDiscountActionCommandInterface
{
    const TYPE = 'percentage_discount';

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFormType()
    {
        return PercentageCatalogDiscountType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function calculate($currentPrice, ChannelInterface $channel, array $configuration)
    {
        Assert::integer($currentPrice, 'Current price is not an integer.');

        $this->isConfigurationValid($configuration);

        $promotionDiscount = $configuration['percentage'];

        return ($currentPrice * $promotionDiscount);
    }

    /**
     * @param array $configuration
     *
     * @throws \InvalidArgumentException
     */
    private function isConfigurationValid(array $configuration)
    {
        Assert::keyExists($configuration, 'percentage', '"percentage" must be set and must be a float.');
        Assert::float($configuration['percentage'], '"percentage" must be set and must be a float.');
    }
}
