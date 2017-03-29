<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductApi\Business;

use Spryker\Zed\ProductApi\Business\Model\ProductApi;
use Spryker\Zed\ProductApi\Business\Transfer\CustomerTransferMapper;
use Spryker\Zed\ProductApi\ProductApiDependencyProvider;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\ProductApi\ProductApiConfig getConfig()
 * @method \Spryker\Zed\ProductApi\Persistence\ProductApiQueryContainer getQueryContainer()
 */
class ProductApiBusinessFactory extends AbstractBusinessFactory
{

    /**
     * @return \Spryker\Zed\ProductApi\Business\Model\ProductApi
     */
    public function createProductApi()
    {
        return new ProductApi(
            $this->getApiQueryContainer(),
            $this->getQueryContainer(),
            $this->createCustomerTransferMapper()
        );
    }

    /**
     * @return \Spryker\Zed\ProductApi\Business\Transfer\CustomerTransferMapper
     */
    public function createCustomerTransferMapper()
    {
        return new CustomerTransferMapper(
            $this->getApiQueryContainer()
        );
    }

    /**
     * @return \Spryker\Zed\ProductApi\Dependency\QueryContainer\ProductApiToApiInterface
     */
    protected function getApiQueryContainer()
    {
        return $this->getProvidedDependency(ProductApiDependencyProvider::QUERY_CONTAINER_API);
    }

}
