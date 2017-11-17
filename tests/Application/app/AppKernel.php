<?php

declare(strict_types=1);

use Sylius\Bundle\CoreBundle\Application\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

final class AppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        return array_merge(parent::registerBundles(), [
            new \Sylius\Bundle\AdminBundle\SyliusAdminBundle(),
            new \Sylius\Bundle\ShopBundle\SyliusShopBundle(),

            new \FOS\OAuthServerBundle\FOSOAuthServerBundle(), // Required by SyliusApiBundle
            new \Sylius\Bundle\AdminApiBundle\SyliusAdminApiBundle(),

            new \Urbanara\CatalogPromotionPlugin\CatalogPromotionPlugin(),

//            new \Sylius\ShopApiPlugin\ShopApiPlugin(),
//            new \League\Tactician\Bundle\TacticianBundle(),

//            new \Sylius\ElasticSearchPlugin\SyliusElasticSearchPlugin(),
//            new \ONGR\ElasticsearchBundle\ONGRElasticsearchBundle(),
//            new \ONGR\FilterManagerBundle\ONGRFilterManagerBundle(),
//            new \SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle(),
//            new \SimpleBus\SymfonyBridge\SimpleBusEventBusBundle(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/config/config.yml');
    }
}
