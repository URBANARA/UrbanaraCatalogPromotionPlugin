<?php

declare(strict_types=1);

namespace Urbanara\CatalogPromotionPlugin\Rule;

use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Urbanara\CatalogPromotionPlugin\Exception\CatalogPromotionRuleException;
use Urbanara\CatalogPromotionPlugin\Form\Type\Rule\IsItemDeliveryTimeType;
use Sylius\Component\Core\Model\ProductVariantInterface;

final class IsItemDeliveryTimeRuleChecker implements RuleCheckerInterface
{
    const TYPE = 'is_product_delivery_time';

    const CONFIG_KEY_CRITERIA = 'criteria';
    const CONFIG_KEY_WEEKS = 'weeks';

    const PRODUCT_ATTRIBUTE_DELIVERY_TIME = 'shop_eta_date';

    const CRITERIA_MORE = 'more';
    const CRITERIA_LESS = 'less';
    const CRITERIA_EQUAL = 'equal';

    const ERROR_MSG_ETA_NOT_FOUND = "ETA date invalid or not found for product %s";
    const ERROR_MSG_NO_CRITERIA_OR_WEEKS_NUMBER_ITEMS_FOUND =
        "Wrong rule configuration. No criteria or weeks number items found";

    const REGEX_VALID_ISO_DATETIME = '/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d(\.\d+)?(([+-]\d\d:\d\d)|Z)?$/i';

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFormType() : string
    {
        return IsItemDeliveryTimeType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function isEligible(ProductVariantInterface $productVariant, array $configuration) : bool
    {
        $etaAttribute = $productVariant->getProduct()->getAttributeByCodeAndLocale(
            self::PRODUCT_ATTRIBUTE_DELIVERY_TIME
        );

        if (!($etaAttribute instanceof AttributeValueInterface)) {
            throw new CatalogPromotionRuleException(
                sprintf(self::ERROR_MSG_ETA_NOT_FOUND, $productVariant->getProduct()->getId())
            );
        }

        $etaDate = $etaAttribute->getValue();

        if (!$etaDate || !($etaDate instanceof \DateTime)) {
            throw new CatalogPromotionRuleException(
                sprintf(self::ERROR_MSG_ETA_NOT_FOUND, $productVariant->getProduct()->getId())
            );
        }

        if (!isset($configuration[self::CONFIG_KEY_CRITERIA]) || !isset($configuration[self::CONFIG_KEY_WEEKS])) {
            throw new CatalogPromotionRuleException(self::ERROR_MSG_NO_CRITERIA_OR_WEEKS_NUMBER_ITEMS_FOUND);
        }

        return $this->validateElegibility($configuration[self::CONFIG_KEY_CRITERIA], $configuration[self::CONFIG_KEY_WEEKS], $etaDate);
    }

    /**
     * @param string    $criteria
     * @param \DateTime $etaDate
     * @param int       $referenceDeliveryWeeks
     *
     * @return bool
     */
    private function validateElegibility(string $criteria, int $referenceDeliveryWeeks, \DateTime $etaDate) : bool
    {
        if ($criteria == self::CRITERIA_MORE) {
            return $this->isGreaterThan($etaDate, $referenceDeliveryWeeks);
        }
        if ($criteria == self::CRITERIA_EQUAL) {
            return $this->isEqualsTo($etaDate, $referenceDeliveryWeeks);
        }
        if ($criteria == self::CRITERIA_LESS) {
            return $this->isLessThan($etaDate, $referenceDeliveryWeeks);
        }

        return false;
    }

    /**
     * @param \DateTime $etaDate
     * @param int       $referenceDeliveryWeeks
     *
     * @return bool
     */
    private function isGreaterThan(\DateTime $etaDate, int $referenceDeliveryWeeks) : bool
    {
        return $etaDate > $this->getDateTimeNumWeeksFromNow($referenceDeliveryWeeks);
    }

    /**
     * @param \DateTime $etaDate
     * @param int       $referenceDeliveryWeeks
     *
     * @return bool
     */
    private function isEqualsTo(\DateTime $etaDate, int $referenceDeliveryWeeks) : bool
    {
        return $etaDate === $this->getDateTimeNumWeeksFromNow($referenceDeliveryWeeks);
    }

    /**
     * @param \DateTime $etaDate
     * @param int       $referenceDeliveryWeeks
     *
     * @return bool
     */
    private function isLessThan(\DateTime $etaDate, int $referenceDeliveryWeeks) : bool
    {
        return $etaDate < $this->getDateTimeNumWeeksFromNow($referenceDeliveryWeeks);
    }

    /**
     * @param int $numWeeks
     *
     * @return \DateTime
     */
    private function getDateTimeNumWeeksFromNow(int $numWeeks) : \DateTime
    {
        $now = (new \DateTime())->setTime(0, 0, 0);

        if ($numWeeks <= 0) {
            return $now;
        }

        return date_add($now, new \DateInterval("P{$numWeeks}W"));
    }
}
