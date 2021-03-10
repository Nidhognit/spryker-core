<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantRelationshipExtension\Dependency\Plugin;

use Generated\Shared\Transfer\MerchantRelationshipTransfer;

/**
 * Provides extension capabilities for the MerchantRelationshipTransfer processing after a merchant relation is updated
 */
interface MerchantRelationshipPostUpdatePluginInterface
{
    /**
     * Specification:
     * - This plugin is executed after a MerchantRelationship update.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\MerchantRelationshipTransfer $merchantRelationshipTransfer
     *
     * @return \Generated\Shared\Transfer\MerchantRelationshipTransfer
     */
    public function execute(MerchantRelationshipTransfer $merchantRelationshipTransfer): MerchantRelationshipTransfer;
}
