<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategoryStorage\Business\Writer\CategoryAttribute;

use Orm\Zed\Category\Persistence\Map\SpyCategoryAttributeTableMap;
use Spryker\Zed\ProductCategoryStorage\Business\Writer\ProductCategoryStorageWriterInterface;
use Spryker\Zed\ProductCategoryStorage\Dependency\Facade\ProductCategoryStorageToEventBehaviorFacadeInterface;

class ProductCategoryStorageByCategoryAttributeEventsWriter implements ProductCategoryStorageByCategoryAttributeEventsWriterInterface
{
    /**
     * @var \Spryker\Zed\ProductCategoryStorage\Dependency\Facade\ProductCategoryStorageToEventBehaviorFacadeInterface
     */
    protected $eventBehaviorFacade;

    /**
     * @var \Spryker\Zed\ProductCategoryStorage\Business\Writer\ProductCategoryStorageWriterInterface
     */
    protected $productCategoryStorageWriter;

    /**
     * @param \Spryker\Zed\ProductCategoryStorage\Dependency\Facade\ProductCategoryStorageToEventBehaviorFacadeInterface $eventBehaviorFacade
     * @param \Spryker\Zed\ProductCategoryStorage\Business\Writer\ProductCategoryStorageWriterInterface $productCategoryStorageWriter
     */
    public function __construct(
        ProductCategoryStorageToEventBehaviorFacadeInterface $eventBehaviorFacade,
        ProductCategoryStorageWriterInterface $productCategoryStorageWriter
    ) {
        $this->eventBehaviorFacade = $eventBehaviorFacade;
        $this->productCategoryStorageWriter = $productCategoryStorageWriter;
    }

    /**
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventEntityTransfers
     *
     * @return void
     */
    public function writeCollectionByCategoryAttributeEvents(array $eventEntityTransfers): void
    {
        $categoryIds = $this->eventBehaviorFacade->getEventTransferForeignKeys(
            $eventEntityTransfers,
            SpyCategoryAttributeTableMap::COL_FK_CATEGORY
        );

        $this->productCategoryStorageWriter->writeCollectionByRelatedCategories($categoryIds, false);
    }

    /**
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventEntityTransfers
     *
     * @return void
     */
    public function writeCollectionByCategoryAttributeNameEvents(array $eventEntityTransfers): void
    {
        $modifiedColumnsEventTransfer = $this->eventBehaviorFacade
            ->getEventTransfersByModifiedColumns(
                $eventEntityTransfers,
                [
                    SpyCategoryAttributeTableMap::COL_NAME,
                ]
            );

        $this->writeCollectionByCategoryAttributeEvents($modifiedColumnsEventTransfer);
    }
}
