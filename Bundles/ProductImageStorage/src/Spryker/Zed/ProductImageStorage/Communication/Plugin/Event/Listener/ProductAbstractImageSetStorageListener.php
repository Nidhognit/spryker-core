<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductImageStorage\Communication\Plugin\Event\Listener;

use Orm\Zed\ProductImage\Persistence\Map\SpyProductImageSetTableMap;
use Spryker\Zed\Event\Dependency\Plugin\EventBulkHandlerInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\ProductImage\Dependency\ProductImageEvents;
use Spryker\Zed\PropelOrm\Business\Transaction\DatabaseTransactionHandlerTrait;

/**
 * @deprecated Use {@link \Spryker\Zed\ProductImageStorage\Communication\Plugin\Event\Listener\ProductAbstractImageSetStoragePublishListener}
 *   and {@link \Spryker\Zed\ProductImageStorage\Communication\Plugin\Event\Listener\ProductAbstractImageSetStorageUnpublishListener} instead.
 *
 * @method \Spryker\Zed\ProductImageStorage\Persistence\ProductImageStorageQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\ProductImageStorage\Communication\ProductImageStorageCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductImageStorage\Business\ProductImageStorageFacadeInterface getFacade()
 * @method \Spryker\Zed\ProductImageStorage\ProductImageStorageConfig getConfig()
 */
class ProductAbstractImageSetStorageListener extends AbstractPlugin implements EventBulkHandlerInterface
{
    use DatabaseTransactionHandlerTrait;

    /**
     * @var array
     */
    protected const PUBLISH_EVENTS = [
        ProductImageEvents::ENTITY_SPY_PRODUCT_IMAGE_SET_UPDATE,
        ProductImageEvents::ENTITY_SPY_PRODUCT_IMAGE_SET_CREATE,
    ];

    /**
     * @api
     *
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventEntityTransfers
     * @param string $eventName
     *
     * @return void
     */
    public function handleBulk(array $eventEntityTransfers, $eventName)
    {
        $this->preventTransaction();
        $productAbstractIds = $this->getFactory()->getEventBehaviorFacade()->getEventTransferForeignKeys($eventEntityTransfers, SpyProductImageSetTableMap::COL_FK_PRODUCT_ABSTRACT);

        if ($eventName === ProductImageEvents::ENTITY_SPY_PRODUCT_IMAGE_SET_DELETE) {
            $this->getFacade()->unpublishProductAbstractImages($productAbstractIds);
        }

        if (in_array($eventName, static::PUBLISH_EVENTS)) {
            $this->getFacade()->publishProductAbstractImages($productAbstractIds);
        }
    }
}
