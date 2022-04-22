<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\StoreReference\Business;

use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\StoreReference\Business\StoreReferenceBusinessFactory getFactory()
 */
class StoreReferenceFacade extends AbstractFacade implements StoreReferenceFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $storeReference
     *
     * @throws \Spryker\Zed\StoreReference\Business\Exception\StoreReferenceNotFoundException
     *
     * @return \Generated\Shared\Transfer\StoreTransfer
     */
    public function getStoreByStoreReference(string $storeReference): StoreTransfer
    {
        return $this->getFactory()->createStoreReferenceMap()->getStoreByStoreReference($storeReference);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $storeName
     *
     * @throws \Spryker\Zed\StoreReference\Business\Exception\StoreReferenceNotFoundException
     *
     * @return \Generated\Shared\Transfer\StoreTransfer
     */
    public function getStoreByStoreName(string $storeName): StoreTransfer
    {
        return $this->getFactory()->createStoreReferenceMap()->getStoreByStoreName($storeName);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @throws \Spryker\Zed\StoreReference\Business\Exception\StoreReferenceNotFoundException
     *
     * @return \Generated\Shared\Transfer\StoreTransfer
     */
    public function getCurrentStore(): StoreTransfer
    {
        return $this->getFactory()->createStoreReferenceMap()->getCurrentStore();
    }
}
