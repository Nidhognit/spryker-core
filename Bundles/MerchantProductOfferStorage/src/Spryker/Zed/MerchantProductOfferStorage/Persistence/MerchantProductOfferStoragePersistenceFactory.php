<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantProductOfferStorage\Persistence;

use Orm\Zed\MerchantProductOfferStorage\Persistence\SpyProductConcreteProductOffersStorageQuery;
use Orm\Zed\MerchantProductOfferStorage\Persistence\SpyProductOfferStorageQuery;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;
use Spryker\Zed\MerchantProductOfferStorage\Persistence\Propel\Mapper\ProductOfferStorageMapper;
use Spryker\Zed\MerchantProductOfferStorage\Persistence\Propel\Mapper\ProductOfferStorageMapperInterface;

/**
 * @method \Spryker\Zed\MerchantProductOfferStorage\Persistence\MerchantProductOfferStorageEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\MerchantProductOfferStorage\MerchantProductOfferStorageConfig getConfig()
 */
class MerchantProductOfferStoragePersistenceFactory extends AbstractPersistenceFactory
{
    /**
     * @return \Orm\Zed\MerchantProductOfferStorage\Persistence\SpyProductConcreteProductOffersStorageQuery
     */
    public function createProductConcreteProductOffersStoragePropelQuery(): SpyProductConcreteProductOffersStorageQuery
    {
        return SpyProductConcreteProductOffersStorageQuery::create();
    }

    /**
     * @return \Orm\Zed\MerchantProductOfferStorage\Persistence\SpyProductOfferStorageQuery
     */
    public function createProductOfferStoragePropelQuery(): SpyProductOfferStorageQuery
    {
        return SpyProductOfferStorageQuery::create();
    }

    /**
     * @return \Spryker\Zed\MerchantProductOfferStorage\Persistence\Propel\Mapper\ProductOfferStorageMapperInterface
     */
    public function createProductOfferStorageMapper(): ProductOfferStorageMapperInterface
    {
        return new ProductOfferStorageMapper();
    }
}