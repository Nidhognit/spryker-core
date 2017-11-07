<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCartConnector\Dependency\Facade;

class ProductCartConnectorToProductOptionFacadeBridge implements ProductCartConnectorToProductOptionFacadeInterface
{
    /**
     * @var \Spryker\Zed\ProductOption\Business\ProductOptionFacadeInterface
     */
    protected $productOptionFacade;

    /**
     * @param \Spryker\Zed\ProductOption\Business\ProductOptionFacadeInterface $productOptionFacade
     */
    public function __construct($productOptionFacade)
    {
        $this->productOptionFacade = $productOptionFacade;
    }

    /**
     * @param int $idProductOptionValue
     *
     * @return \Generated\Shared\Transfer\ProductOptionTransfer
     */
    public function getProductOptionValueById($idProductOptionValue)
    {
        return $this->productOptionFacade->getProductOptionValueById($idProductOptionValue);
    }
}
