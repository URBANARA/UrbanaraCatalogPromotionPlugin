<?php

declare(strict_types=1);

namespace Urbanara\CatalogPromotionPlugin\OrderProcessing;

use AppBundle\Entity\Order;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderItemUnit;
use Sylius\Component\Core\Model\PromotionInterface;
use Sylius\Component\Core\Promotion\Action\PercentageDiscountPromotionActionCommand;
use Sylius\Component\Core\Promotion\Action\UnitPercentageDiscountPromotionActionCommand;
use Sylius\Component\Order\Model\AdjustableInterface;
use Sylius\Component\Promotion\Model\PromotionActionInterface;
use Urbanara\CatalogPromotionPlugin\Action\CatalogDiscountActionCommandInterface;
use Urbanara\CatalogPromotionPlugin\Applicator\CatalogPromotionApplicatorInterface;
use Urbanara\CatalogPromotionPlugin\Entity\CatalogPromotionInterface;
use Urbanara\CatalogPromotionPlugin\Provider\CatalogPromotionProviderInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
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
        /** @var OrderItemUnit $orderItemUnit */
        $orderItemUnit = $item->getUnits()->first();

        $currentPrice = $item->getUnitPrice() + $orderItemUnit->getAdjustmentsTotal(
            AdjustmentInterface::TAX_ADJUSTMENT
        );

        /** @var Order $order */
        $order = $item->getOrder();
        /** @var Collection|AdjustmentInterface[] $adjustments */
        $adjustments = $this->getAdjustmentsRecursivelyByTypeArray(
            [
                AdjustmentInterface::ORDER_UNIT_PROMOTION_ADJUSTMENT,
                AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT,
                AdjustmentInterface::ORDER_ITEM_PROMOTION_ADJUSTMENT,
            ],
            $item
        );

        /** @var Collection|PromotionInterface[] $promotions */
        $promotions = $order->getPromotions()->filter(
            function (PromotionInterface $promotion) {
                return $promotion->getActions()->filter(
                        function (PromotionActionInterface $rule) {
                            return in_array(
                                $rule->getType(),
                                [
                                    PercentageDiscountPromotionActionCommand::TYPE,
                                    UnitPercentageDiscountPromotionActionCommand::TYPE,
                                ],
                                true
                            );
                        }
                    )->count() > 0;
            }
        );

        /** @var CatalogPromotionInterface $catalogPromotion */
        foreach ($this->catalogPromotionProvider->provide($channel, $variant) as $catalogPromotion) {
            /** @var CatalogDiscountActionCommandInterface $command */
            $command = $this->serviceRegistry->get($catalogPromotion->getDiscountType());

            $discount = $command->calculate($currentPrice, $channel, $catalogPromotion->getDiscountConfiguration());
            if (count($promotions) > 0) {
                foreach ($adjustments as $adjustment) {
                    if ($discount > abs($adjustment->getAmount())) {
                        /** @var AdjustableInterface $adjustable */
                        $adjustable = $adjustment->getAdjustable();
                        $adjustable->removeAdjustment($adjustment);
                    } else {
                        continue 2;
                    }
                }
            }

            $this->catalogPromotionApplicator->apply($item, $catalogPromotion, $discount, $catalogPromotion->getName());
            $currentPrice -= $discount;
        }

    }

    /**
     * {@inheritdoc}
     */
    public function getAdjustmentsRecursivelyByTypeArray(array $types, OrderItemInterface $orderItem): Collection
    {
        $adjustments = new ArrayCollection();
        foreach ($types as $type) {
            foreach ($orderItem->getAdjustments($type) as $adjustment) {
                $adjustments->add($adjustment);
            }

            foreach ($orderItem->getUnits() as $unit) {
                foreach ($unit->getAdjustments($type) as $adjustment) {
                    $adjustments->add($adjustment);
                }
            }
        }

        return $adjustments;
    }
}
