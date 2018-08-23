<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitTreeBuilder;

use Generated\Shared\Transfer\CompanyBusinessUnitTreeTransfer;
use Generated\Shared\Transfer\CustomerTransfer;

interface CompanyBusinessUnitTreeBuilderInterface
{
    /**
     * @param \Generated\Shared\Transfer\CustomerTransfer $customerTransfer
     *
     * @return \Generated\Shared\Transfer\CompanyBusinessUnitTreeTransfer
     */
    public function getCustomerCompanyBusinessUnitTree(CustomerTransfer $customerTransfer): CompanyBusinessUnitTreeTransfer;
}
