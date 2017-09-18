<?php

declare(strict_types=1);

namespace Urbanara\CatalogPromotionPlugin\Rule;

use Sylius\Bundle\CoreBundle\Form\Type\Promotion\Rule\CustomerGroupConfigurationType;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Component\Customer\Model\CustomerGroupInterface;

class IsCustomerGroupRuleChecker implements RuleCheckerInterface
{

    /**
     * @var CustomerContextInterface
     */
    protected $customerContext;

    /**
     * IsCustomerGroupRuleChecker constructor.
     *
     * @param CustomerContextInterface $customerContext
     */
    public function __construct(CustomerContextInterface $customerContext)
    {
        $this->customerContext = $customerContext;
    }

    public function getConfigurationFormType()
    {
        return CustomerGroupConfigurationType::class;
    }

    public function isEligible(ProductVariantInterface $productVariant, array $configuration)
    {
        $customer = $this->customerContext->getCustomer();
        if ($customer instanceof CustomerInterface) {
            if ($customer->getGroup() instanceof CustomerGroupInterface && $customer->getGroup()->getCode() === $configuration['group_code']) {
                return true;
            }
        }

        return false;
    }
}
