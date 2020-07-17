<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductBundleStorage\Communication\Plugin\Publisher;

use Generated\Shared\Transfer\FilterTransfer;
use Generated\Shared\Transfer\ProductBundleCriteriaFilterTransfer;
use Spryker\Shared\ProductBundleStorage\ProductBundleStorageConfig;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\PublisherExtension\Dependency\Plugin\PublisherTriggerPluginInterface;

/**
 * @method \Spryker\Zed\ProductBundleStorage\ProductBundleStorageConfig getConfig()
 * @method \Spryker\Zed\ProductBundleStorage\Business\ProductBundleStorageFacadeInterface getFacade()
 * @method \Spryker\Zed\ProductBundleStorage\Communication\ProductBundleStorageCommunicationFactory getFactory()
 */
class ProductBundlePublisherTriggerPlugin extends AbstractPlugin implements PublisherTriggerPluginInterface
{
    // TODO: discuss it!
    /**
     * @uses \Orm\Zed\ProductBundle\Persistence\Map\SpyProductBundleTableMap::COL_FK_PRODUCT
     */
    protected const ID_PRODUCT_CONCRETE_BUNDLE = 'spy_product_bundle.id_product_concrete_bundle';

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $offset
     * @param int $limit
     *
     * @return \Generated\Shared\Transfer\ProductBundleTransfer[]
     */
    public function getData(int $offset, int $limit): array
    {
        $filterTransfer = (new FilterTransfer())
            ->setOffset($offset)
            ->setLimit($limit);

        $productBundleCriteriaFilterTransfer = (new ProductBundleCriteriaFilterTransfer())
            ->setFilter($filterTransfer)
            ->setIsGrouped(true);

        return $this->getFactory()
            ->getProductBundleFacade()
            ->getProductBundleCollectionByCriteriaFilter($productBundleCriteriaFilterTransfer)
            ->getProductBundles()
            ->getArrayCopy();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return string
     */
    public function getResourceName(): string
    {
        return ProductBundleStorageConfig::PRODUCT_BUNDLE_RESOURCE_NAME;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return string
     */
    public function getEventName(): string
    {
        return ProductBundleStorageConfig::PRODUCT_BUNDLE_PUBLISH;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return string|null
     */
    public function getIdColumnName(): ?string
    {
        return static::ID_PRODUCT_CONCRETE_BUNDLE;
    }
}
