<?php

declare(strict_types=1);

namespace Urbanara\CatalogPromotionPlugin\OrderProcessing;

use Urbanara\CatalogPromotionPlugin\Action\CatalogDiscountActionCommandInterface;
use Urbanara\CatalogPromotionPlugin\Applicator\CatalogPromotionApplicatorInterface;
use Urbanara\CatalogPromotionPlugin\Entity\CatalogPromotionInterface;
use Urbanara\CatalogPromotionPlugin\Model\CatalogAdjustmentInterface;
use Urbanara\CatalogPromotionPlugin\Provider\CatalogPromotionProviderInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

final class CatalogPromotionProcessor implements OrderProcessorInterface
{
    /**
     * @var CatalogPromotionProviderInterface
     */
    private $catalogPromotionProvider;

    /**
     * @var ServiceRegistryInterface
     */
    private $serviceRegistry;

    /**
     * @var CatalogPromotionApplicatorInterface
     */
    private $catalogPromotionApplicator;

    /**
     * @param CatalogPromotionProviderInterface $catalogPromotionProvider
     * @param ServiceRegistryInterface $serviceRegistry
     * @param CatalogPromotionApplicatorInterface $catalogPromotionApplicator
     */
    public function __construct(
        CatalogPromotionProviderInterface $catalogPromotionProvider,
        ServiceRegistryInterface $serviceRegistry,
        CatalogPromotionApplicatorInterface $catalogPromotionApplicator
    ) {
        $this->catalogPromotionProvider = $catalogPromotionProvider;
        $this->serviceRegistry = $serviceRegistry;
        $this->catalogPromotionApplicator = $catalogPromotionApplicator;
    }

    /**
     * {@inheritdoc}
     */
    public function process(BaseOrderInterface $order): void
    {
        /** @var OrderInterface $order */
        Assert::isInstanceOf($order, OrderInterface::class);
        $channel = $order->getChannel();

        /** @var OrderItemInterface $item */
        foreach ($order->getItems() as $item) {
            if ($item->isImmutable()) {
                continue;
            }

            $this->applyPromotion($channel, $item);
        }
    }

    /**
     * @param ChannelInterface $channel
     * @param OrderItemInterface $item
     */
    private function applyPromotion(ChannelInterface $channel, OrderItemInterface $item)
    {
        $variant = $item->getVariant();
        $currentPrice = $variant->getChannelPricingForChannel($channel)->getPrice();

        /** @var CatalogPromotionInterface $catalogPromotion */
        foreach ($this->catalogPromotionProvider->provide($channel, $variant) as $catalogPromotion) {
            /** @var CatalogDiscountActionCommandInterface $command */
            $command = $this->serviceRegistry->get($catalogPromotion->getDiscountType());

            $discount = $command->calculate($currentPrice, $channel, $catalogPromotion->getDiscountConfiguration());

            $this->catalogPromotionApplicator->apply($item, $catalogPromotion, $discount, $catalogPromotion->getName());
            $currentPrice -= $discount;
        }
    }
}
