<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantProductOfferStorage\Business\Writer;

use Generated\Shared\Transfer\ProductOfferCollectionTransfer;
use Generated\Shared\Transfer\ProductOfferCriteriaFilterTransfer;
use Orm\Zed\ProductOffer\Persistence\Map\SpyProductOfferTableMap;
use Spryker\Zed\MerchantProductOfferStorage\Business\Deleter\ProductConcreteOffersStorageDeleterInterface;
use Spryker\Zed\MerchantProductOfferStorage\Dependency\Facade\MerchantProductOfferStorageToEventBehaviorFacadeInterface;
use Spryker\Zed\MerchantProductOfferStorage\Dependency\Facade\MerchantProductOfferStorageToProductOfferFacadeInterface;
use Spryker\Zed\MerchantProductOfferStorage\Dependency\Facade\MerchantProductOfferStorageToStoreFacadeInterface;
use Spryker\Zed\MerchantProductOfferStorage\Persistence\MerchantProductOfferStorageEntityManagerInterface;

class ProductConcreteOffersStorageWriter implements ProductConcreteOffersStorageWriterInterface
{
    /**
     * @var array
     */
    public static $storeNames = [];

    /**
     * @uses \Spryker\Shared\ProductOffer\ProductOfferConfig::STATUS_APPROVED
     */
    public const STATUS_APPROVED = 'approved';

    /**
     * @var \Spryker\Zed\MerchantProductOfferStorage\Dependency\Facade\MerchantProductOfferStorageToEventBehaviorFacadeInterface
     */
    protected $eventBehaviorFacade;

    /**
     * @var \Spryker\Zed\MerchantProductOfferStorage\Dependency\Facade\MerchantProductOfferStorageToProductOfferFacadeInterface
     */
    protected $productOfferFacade;

    /**
     * @var \Spryker\Zed\MerchantProductOfferStorage\Persistence\MerchantProductOfferStorageEntityManagerInterface
     */
    protected $merchantProductOfferStorageEntityManager;

    /**
     * @var \Spryker\Zed\MerchantProductOfferStorage\Business\Deleter\ProductConcreteOffersStorageDeleterInterface
     */
    protected $productConcreteOffersStorageDeleter;

    /**
     * @var \Spryker\Zed\MerchantProductOfferStorage\Dependency\Facade\MerchantProductOfferStorageToStoreFacadeInterface
     */
    protected $storeFacade;

    /**
     * @var string[]
     */
    protected $storeNamesList;

    /**
     * @param \Spryker\Zed\MerchantProductOfferStorage\Dependency\Facade\MerchantProductOfferStorageToEventBehaviorFacadeInterface $eventBehaviorFacade
     * @param \Spryker\Zed\MerchantProductOfferStorage\Dependency\Facade\MerchantProductOfferStorageToProductOfferFacadeInterface $productOfferFacade
     * @param \Spryker\Zed\MerchantProductOfferStorage\Persistence\MerchantProductOfferStorageEntityManagerInterface $merchantProductOfferStorageEntityManager
     * @param \Spryker\Zed\MerchantProductOfferStorage\Business\Deleter\ProductConcreteOffersStorageDeleterInterface $productConcreteOffersStorageDeleter
     * @param \Spryker\Zed\MerchantProductOfferStorage\Dependency\Facade\MerchantProductOfferStorageToStoreFacadeInterface $storeFacade
     */
    public function __construct(
        MerchantProductOfferStorageToEventBehaviorFacadeInterface $eventBehaviorFacade,
        MerchantProductOfferStorageToProductOfferFacadeInterface $productOfferFacade,
        MerchantProductOfferStorageEntityManagerInterface $merchantProductOfferStorageEntityManager,
        ProductConcreteOffersStorageDeleterInterface $productConcreteOffersStorageDeleter,
        MerchantProductOfferStorageToStoreFacadeInterface $storeFacade
    ) {
        $this->eventBehaviorFacade = $eventBehaviorFacade;
        $this->productOfferFacade = $productOfferFacade;
        $this->merchantProductOfferStorageEntityManager = $merchantProductOfferStorageEntityManager;
        $this->productConcreteOffersStorageDeleter = $productConcreteOffersStorageDeleter;
        $this->storeFacade = $storeFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
     *
     * @return void
     */
    public function writeCollectionByProductSkuEvents(array $eventTransfers): void
    {
        $productSkus = $this->eventBehaviorFacade->getEventTransfersAdditionalValues($eventTransfers, SpyProductOfferTableMap::COL_CONCRETE_SKU);

        if (!$productSkus) {
            return;
        }

        $this->writeCollectionByProductSkus($productSkus);
    }

    /**
     * @param string[] $productConcreteSkus
     *
     * @return void
     */
    protected function writeCollectionByProductSkus(array $productConcreteSkus): void
    {
        $productConcreteSkus = array_unique($productConcreteSkus);

        $productOfferCriteriaFilterTransfer = $this->createProductOfferCriteriaFilterTransfer($productConcreteSkus);
        $productOfferCollectionTransfer = $this->productOfferFacade->find($productOfferCriteriaFilterTransfer);

        $productOfferReferencesGroupedByConcreteSku = $this->groupProductOfferReferencesByConcreteSku($productOfferCollectionTransfer);

        foreach ($productOfferReferencesGroupedByConcreteSku as $concreteSku => $productOfferReferencesGroupedByStore) {
            $storeNamesToRemove = $this->getStoreNamesList();
            $productOfferReferenceList = [];
            foreach ($productOfferReferencesGroupedByStore as $storeName => $productOfferReferenceList) {
                $this->merchantProductOfferStorageEntityManager->saveProductConcreteProductOffersStorage($concreteSku, $productOfferReferenceList, $storeName);
                $storeNamesToRemove = array_diff($storeNamesToRemove, [$storeName]);
            }

            if ($storeNamesToRemove && $productOfferReferenceList) {
                $this->deleteProductConcreteProductOffersStorage($storeNamesToRemove, $concreteSku);
            }
        }
    }

    /**
     * @param \Generated\Shared\Transfer\ProductOfferCollectionTransfer $productOfferCollectionTransfer
     *
     * @return array
     */
    protected function groupProductOfferReferencesByConcreteSku(ProductOfferCollectionTransfer $productOfferCollectionTransfer): array
    {
        $productOfferReferencesGroupedByConcreteSku = [];
        foreach ($productOfferCollectionTransfer->getProductOffers() as $productOfferTransfer) {
            if (!isset($productOfferReferencesGroupedByConcreteSku[$productOfferTransfer->getConcreteSku()])) {
                $productOfferReferencesGroupedByConcreteSku[$productOfferTransfer->getConcreteSku()] = [];
            }
            foreach ($productOfferTransfer->getStores() as $storeTransfer) {
                $productOfferReferencesGroupedByConcreteSku[$productOfferTransfer->getConcreteSku()][$storeTransfer->getName()][] =
                    mb_strtolower($productOfferTransfer->getProductOfferReference());
            }
        }

        return $productOfferReferencesGroupedByConcreteSku;
    }

    /**
     * @param string[] $productConcreteSkus
     *
     * @return \Generated\Shared\Transfer\ProductOfferCriteriaFilterTransfer
     */
    protected function createProductOfferCriteriaFilterTransfer(array $productConcreteSkus): ProductOfferCriteriaFilterTransfer
    {
        return (new ProductOfferCriteriaFilterTransfer())
            ->setConcreteSkus($productConcreteSkus)
            ->setIsActive(true)
            ->setIsActiveConcreteProduct(true)
            ->addApprovalStatus(static::STATUS_APPROVED);
    }

    /**
     * @param string[] $storeNamesToRemove
     * @param string $productSku
     *
     * @return void
     */
    protected function deleteProductConcreteProductOffersStorage(array $storeNamesToRemove, string $productSku): void
    {
        foreach ($storeNamesToRemove as $storeName) {
            $this->productConcreteOffersStorageDeleter->deleteCollectionByProductSkus(
                [$productSku],
                $storeName
            );
        }
    }

    /**
     * @return string[]
     */
    protected function getStoreNamesList(): array
    {
        if ($this->storeNamesList) {
            return $this->storeNamesList;
        }

        foreach ($this->storeFacade->getAllStores() as $storeTransfer) {
            $this->storeNamesList[] = $storeTransfer->getName();
        }

        return $this->storeNamesList;
    }
}
