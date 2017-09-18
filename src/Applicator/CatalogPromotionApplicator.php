<?php

declare(strict_types=1);

namespace Urbanara\CatalogPromotionPlugin\Applicator;

use Sylius\Component\Core\Model\OrderItemUnitInterface;
use Sylius\Component\Core\Promotion\Applicator\UnitsPromotionAdjustmentsApplicatorInterface;
use Urbanara\CatalogPromotionPlugin\Entity\CatalogPromotionInterface;
use Urbanara\CatalogPromotionPlugin\Model\CatalogAdjustmentInterface;
use Sylius\Component\Order\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class CatalogPromotionApplicator implements CatalogPromotionApplicatorInterface
{
    /**
     * @var FactoryInterface
     */
    private $adjustmentFactory;

    /**
     * @var UnitsPromotionAdjustmentsApplicatorInterface
     */
    private $unitsPromotionAdjustmentsApplicator;

    /**
     * @param FactoryInterface $adjustmentFactory
     * @param UnitsPromotionAdjustmentsApplicatorInterface $unitsPromotionAdjustmentsApplicator
     */
    public function __construct(
        FactoryInterface $adjustmentFactory,
        UnitsPromotionAdjustmentsApplicatorInterface $unitsPromotionAdjustmentsApplicator
    )
    {
        $this->adjustmentFactory = $adjustmentFactory;
        $this->unitsPromotionAdjustmentsApplicator = $unitsPromotionAdjustmentsApplicator;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(OrderItemInterface $orderItem, CatalogPromotionInterface $catalogPromotion, $amount, $label = '')
    {
        foreach ($orderItem->getUnits() as $orderItemUnit) {
            $adjustment = $this->provideUnitAdjustment($orderItemUnit, $catalogPromotion);
            $adjustment->setAmount(-$amount);
            $adjustment->setLabel($label);
            $orderItemUnit->addAdjustment($adjustment);
            $orderItemUnit->recalculateAdjustmentsTotal();
        }
        $orderItem->recalculateUnitsTotal();
        $orderItem->recalculateAdjustmentsTotal();
    }

    private function provideUnitAdjustment(
        OrderItemUnitInterface $orderItem,
        CatalogPromotionInterface $catalogPromotion
    ): AdjustmentInterface {
        $adjustments = $orderItem->getAdjustments(CatalogAdjustmentInterface::CATALOG_PROMOTION_ADJUSTMENT);
        foreach ($adjustments as $adjustment) {
            if ($adjustment->getOriginCode() === $catalogPromotion->getCode()) {
                return $adjustment;
            }
        }

        /** @var AdjustmentInterface $adjustment */
        $adjustment = $this->adjustmentFactory->createNew();

        $adjustment->setType(\Sylius\Component\Core\Model\AdjustmentInterface::ORDER_UNIT_PROMOTION_ADJUSTMENT);
        $adjustment->setOriginCode($catalogPromotion->getCode());

        return $adjustment;
    }

}
