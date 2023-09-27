<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PickingList\Persistence;

use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\PickingListCollectionTransfer;
use Generated\Shared\Transfer\PickingListConditionsTransfer;
use Generated\Shared\Transfer\PickingListCriteriaTransfer;
use Generated\Shared\Transfer\PickingListItemCollectionTransfer;
use Generated\Shared\Transfer\PickingListItemCriteriaTransfer;
use Orm\Zed\PickingList\Persistence\SpyPickingListItemQuery;
use Orm\Zed\PickingList\Persistence\SpyPickingListQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;
use Spryker\Zed\Kernel\Persistence\EntityManager\InstancePoolingTrait;

/**
 * @method \Spryker\Zed\PickingList\Persistence\PickingListPersistenceFactory getFactory()
 */
class PickingListRepository extends AbstractRepository implements PickingListRepositoryInterface
{
    use InstancePoolingTrait;

    /**
     * @param \Generated\Shared\Transfer\PickingListCriteriaTransfer $pickingListCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\PickingListCollectionTransfer
     */
    public function getPickingListCollection(
        PickingListCriteriaTransfer $pickingListCriteriaTransfer
    ): PickingListCollectionTransfer {
        $this->disableInstancePooling();

        $pickingListQuery = $this->getFactory()
            ->createPickingListQuery()
            ->leftJoinWithSpyPickingListItem();

        $pickingListQuery = $this->applyPickingListFilters($pickingListQuery, $pickingListCriteriaTransfer);
        $pickingListQuery = $this->applyPickingListSorting($pickingListQuery, $pickingListCriteriaTransfer);

        $paginationTransfer = $pickingListCriteriaTransfer->getPagination();
        $pickingListCollectionTransfer = new PickingListCollectionTransfer();

        if ($paginationTransfer !== null) {
            $pickingListQuery = $this->applyPickingListPagination($pickingListQuery, $paginationTransfer);
            $pickingListCollectionTransfer->setPagination($paginationTransfer);
        }

        $pickingListCollectionTransfer = $this->getFactory()
            ->createPickingListMapper()
            ->mapPickingListEntityCollectionToPickingListCollectionTransfer(
                $pickingListQuery->find(),
                $pickingListCollectionTransfer,
            );

        $this->enableInstancePooling();

        return $pickingListCollectionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\PickingListItemCriteriaTransfer $pickingListItemCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\PickingListItemCollectionTransfer
     */
    public function getPickingListItemCollection(
        PickingListItemCriteriaTransfer $pickingListItemCriteriaTransfer
    ): PickingListItemCollectionTransfer {
        $pickingListItemQuery = $this->applyPickingListItemFilters(
            $this->getFactory()->createPickingListItemQuery(),
            $pickingListItemCriteriaTransfer,
        );

        /** @var \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\PickingList\Persistence\SpyPickingListItem> $pickingListItemEntityCollection */
        $pickingListItemEntityCollection = $pickingListItemQuery->find();

        return $this->getFactory()
            ->createPickingListItemMapper()
            ->mapPickingListItemEntityCollectionToPickingListItemCollectionTransfer(
                $pickingListItemEntityCollection,
                new PickingListItemCollectionTransfer(),
            );
    }

    /**
     * @param \Orm\Zed\PickingList\Persistence\SpyPickingListQuery $pickingListQuery
     * @param \Generated\Shared\Transfer\PickingListCriteriaTransfer $pickingListCriteriaTransfer
     *
     * @return \Orm\Zed\PickingList\Persistence\SpyPickingListQuery
     */
    protected function applyPickingListFilters(
        SpyPickingListQuery $pickingListQuery,
        PickingListCriteriaTransfer $pickingListCriteriaTransfer
    ): SpyPickingListQuery {
        $pickingListConditionsTransfer = $pickingListCriteriaTransfer->getPickingListConditions();
        if ($pickingListConditionsTransfer === null) {
            return $pickingListQuery;
        }

        return $this->buildPickingListQueryByConditions($pickingListConditionsTransfer, $pickingListQuery);
    }

    /**
     * @param \Orm\Zed\PickingList\Persistence\SpyPickingListItemQuery $pickingListItemQuery
     * @param \Generated\Shared\Transfer\PickingListItemCriteriaTransfer $pickingListItemCriteriaTransfer
     *
     * @return \Orm\Zed\PickingList\Persistence\SpyPickingListItemQuery
     */
    protected function applyPickingListItemFilters(
        SpyPickingListItemQuery $pickingListItemQuery,
        PickingListItemCriteriaTransfer $pickingListItemCriteriaTransfer
    ): SpyPickingListItemQuery {
        $pickingListItemConditionsTransfer = $pickingListItemCriteriaTransfer->getPickingListItemConditions();
        if (!$pickingListItemConditionsTransfer) {
            return $pickingListItemQuery;
        }

        $pickingListIds = $pickingListItemConditionsTransfer->getPickingListIds();
        if ($pickingListIds !== []) {
            $pickingListItemQuery->filterByFkPickingList_In($pickingListIds);
        }

        return $pickingListItemQuery;
    }

    /**
     * @param \Orm\Zed\PickingList\Persistence\SpyPickingListQuery $pickingListQuery
     * @param \Generated\Shared\Transfer\PaginationTransfer $paginationTransfer
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    protected function applyPickingListPagination(
        SpyPickingListQuery $pickingListQuery,
        PaginationTransfer $paginationTransfer
    ): ModelCriteria {
        if ($paginationTransfer->getOffset() !== null && $paginationTransfer->getLimit() !== null) {
            $paginationTransfer->setNbResults($pickingListQuery->count());

            $pickingListQuery->offset($paginationTransfer->getOffsetOrFail())
                ->setLimit($paginationTransfer->getLimitOrFail());

            return $pickingListQuery;
        }

        if ($paginationTransfer->getPage() !== null && $paginationTransfer->getMaxPerPage()) {
            $paginationModel = $pickingListQuery->paginate(
                $paginationTransfer->getPage(),
                $paginationTransfer->getMaxPerPage(),
            );

            $paginationTransfer->setNbResults($paginationModel->getNbResults())
                ->setFirstIndex($paginationModel->getFirstIndex())
                ->setLastIndex($paginationModel->getLastIndex())
                ->setFirstPage($paginationModel->getFirstPage())
                ->setLastPage($paginationModel->getLastPage())
                ->setNextPage($paginationModel->getNextPage())
                ->setPreviousPage($paginationModel->getPreviousPage());

            return $paginationModel->getQuery();
        }

        return $pickingListQuery;
    }

    /**
     * @param \Orm\Zed\PickingList\Persistence\SpyPickingListQuery $pickingListQuery
     * @param \Generated\Shared\Transfer\PickingListCriteriaTransfer $pickingListCriteriaTransfer
     *
     * @return \Orm\Zed\PickingList\Persistence\SpyPickingListQuery
     */
    protected function applyPickingListSorting(
        SpyPickingListQuery $pickingListQuery,
        PickingListCriteriaTransfer $pickingListCriteriaTransfer
    ): SpyPickingListQuery {
        $sortCollection = $pickingListCriteriaTransfer->getSortCollection();
        foreach ($sortCollection as $sortTransfer) {
            $pickingListQuery->orderBy(
                $sortTransfer->getFieldOrFail(),
                $sortTransfer->getIsAscending() ? Criteria::ASC : Criteria::DESC,
            );
        }

        return $pickingListQuery;
    }

    /**
     * @module Stock
     *
     * @param \Generated\Shared\Transfer\PickingListConditionsTransfer $pickingListConditionsTransfer
     * @param \Orm\Zed\PickingList\Persistence\SpyPickingListQuery $pickingListQuery
     *
     * @return \Orm\Zed\PickingList\Persistence\SpyPickingListQuery
     */
    protected function buildPickingListQueryByConditions(
        PickingListConditionsTransfer $pickingListConditionsTransfer,
        SpyPickingListQuery $pickingListQuery
    ): SpyPickingListQuery {
        $userUuids = $pickingListConditionsTransfer->getUserUuids();
        if ($userUuids !== []) {
            $pickingListQuery->filterByUserUuid_In($userUuids);
        }

        if ($userUuids !== [] && $pickingListConditionsTransfer->getWithUnassignedUser()) {
            $pickingListQuery->_or()->filterByUserUuid(null);
        }

        if ($userUuids === [] && $pickingListConditionsTransfer->getWithUnassignedUser()) {
            $pickingListQuery->filterByUserUuid(null);
        }

        $uuids = $pickingListConditionsTransfer->getUuids();
        if ($uuids !== []) {
            $pickingListQuery->filterByUuid_In($uuids);
        }

        $statuses = $pickingListConditionsTransfer->getStatuses();
        if ($statuses !== []) {
            $pickingListQuery->filterByStatus_In($statuses);
        }

        $salesOrderItemUuids = $pickingListConditionsTransfer->getSalesOrderItemUuids();
        if ($salesOrderItemUuids !== []) {
            $pickingListQuery->useSpyPickingListItemQuery()
                ->filterBySalesOrderItemUuid_In($salesOrderItemUuids)
                ->endUse();
        }

        $warehouseUuids = $pickingListConditionsTransfer->getWarehouseUuids();
        if ($warehouseUuids !== []) {
            $pickingListQuery->useSpyStockQuery()
                ->filterByUuid_In($warehouseUuids)
                ->endUse();
        }

        $warehouseIds = $pickingListConditionsTransfer->getWarehouseIds();
        if ($warehouseIds !== []) {
            $pickingListQuery->useSpyStockQuery()
                ->filterByIdStock_In($warehouseUuids)
                ->endUse();
        }

        return $pickingListQuery;
    }
}
