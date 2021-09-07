<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferGui;

use Orm\Zed\ProductOffer\Persistence\SpyProductOfferQuery;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\ProductOfferGui\Dependency\Facade\ProductOfferGuiToLocaleFacadeBridge;
use Spryker\Zed\ProductOfferGui\Dependency\Facade\ProductOfferGuiToProductFacadeBridge;
use Spryker\Zed\ProductOfferGui\Dependency\Facade\ProductOfferGuiToProductOfferFacadeBridge;

/**
 * @method \Spryker\Zed\ProductOfferGui\ProductOfferGuiConfig getConfig()
 */
class ProductOfferGuiDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const FACADE_LOCALE = 'FACADE_LOCALE';
    /**
     * @var string
     */
    public const FACADE_PRODUCT = 'FACADE_PRODUCT';
    /**
     * @var string
     */
    public const FACADE_PRODUCT_OFFER = 'FACADE_PRODUCT_OFFER';
    /**
     * @var string
     */
    public const PLUGINS_PRODUCT_OFFER_LIST_ACTION_VIEW_DATA_EXPANDER = 'PLUGINS_PRODUCT_OFFER_LIST_ACTION_VIEW_DATA_EXPANDER';
    /**
     * @var string
     */
    public const PLUGINS_PRODUCT_OFFER_TABLE_EXPANDER = 'PLUGINS_PRODUCT_OFFER_TABLE_EXPANDER';
    /**
     * @var string
     */
    public const PLUGINS_PRODUCT_OFFER_VIEW_SECTION = 'PLUGINS_PRODUCT_OFFER_VIEW_SECTION';
    /**
     * @var string
     */
    public const PROPEL_QUERY_PRODUCT_OFFER = 'PROPEL_QUERY_PRODUCT_OFFER';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);

        $container = $this->addLocaleFacade($container);
        $container = $this->addProductFacade($container);
        $container = $this->addProductOfferFacade($container);
        $container = $this->addProductOfferListActionViewDataExpanderPlugins($container);
        $container = $this->addProductOfferTableExpanderPlugins($container);
        $container = $this->addProductOfferViewSectionPlugins($container);
        $container = $this->addPropelProductOfferQuery($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPropelProductOfferQuery(Container $container): Container
    {
        $container->set(static::PROPEL_QUERY_PRODUCT_OFFER, $container->factory(function () {
            return SpyProductOfferQuery::create();
        }));

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addLocaleFacade(Container $container): Container
    {
        $container->set(static::FACADE_LOCALE, function (Container $container) {
            return new ProductOfferGuiToLocaleFacadeBridge($container->getLocator()->locale()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addProductOfferFacade(Container $container): Container
    {
        $container->set(static::FACADE_PRODUCT_OFFER, function (Container $container) {
            return new ProductOfferGuiToProductOfferFacadeBridge($container->getLocator()->productOffer()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addProductOfferListActionViewDataExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_PRODUCT_OFFER_LIST_ACTION_VIEW_DATA_EXPANDER, function () {
            return $this->getProductOfferListActionViewDataExpanderPlugins();
        });

        return $container;
    }

    /**
     * @return \Spryker\Zed\ProductOfferGuiExtension\Dependency\Plugin\ProductOfferListActionViewDataExpanderPluginInterface[]
     */
    protected function getProductOfferListActionViewDataExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addProductOfferTableExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_PRODUCT_OFFER_TABLE_EXPANDER, function () {
            return $this->getProductOfferTableExpanderPlugins();
        });

        return $container;
    }

    /**
     * @return \Spryker\Zed\ProductOfferGuiExtension\Dependency\Plugin\ProductOfferTableExpanderPluginInterface[]
     */
    protected function getProductOfferTableExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addProductFacade(Container $container): Container
    {
        $container->set(static::FACADE_PRODUCT, function (Container $container) {
            return new ProductOfferGuiToProductFacadeBridge(
                $container->getLocator()->product()->facade()
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addProductOfferViewSectionPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_PRODUCT_OFFER_VIEW_SECTION, function () {
            return $this->getProductOfferViewSectionPlugins();
        });

        return $container;
    }

    /**
     * @return \Spryker\Zed\ProductOfferGuiExtension\Dependency\Plugin\ProductOfferViewSectionPluginInterface[]
     */
    protected function getProductOfferViewSectionPlugins(): array
    {
        return [];
    }
}
