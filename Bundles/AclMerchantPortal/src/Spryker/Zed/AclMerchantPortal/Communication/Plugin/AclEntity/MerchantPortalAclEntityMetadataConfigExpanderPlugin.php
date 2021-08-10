<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AclMerchantPortal\Communication\Plugin\AclEntity;

use Generated\Shared\Transfer\AclEntityMetadataConfigTransfer;
use Spryker\Zed\AclEntityExtension\Dependency\Plugin\AclEntityMetadataConfigExpanderPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \Spryker\Zed\AclMerchantPortal\Business\AclMerchantPortalFacadeInterface getFacade()
 * @method \Spryker\Zed\AclMerchantPortal\AclMerchantPortalConfig getConfig()
 */
class MerchantPortalAclEntityMetadataConfigExpanderPlugin extends AbstractPlugin implements AclEntityMetadataConfigExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Expands provided `AclEntityMetadataCollection` transfer object with merchant order composite data.
     * - Expands provided `AclEntityMetadataCollection` transfer object with merchant product composite data.
     * - Expands provided `AclEntityMetadataCollection` transfer object with merchant composite data.
     * - Expands provided `AclEntityMetadataCollection` transfer object with product offer composite data.
     * - Expands provided `AclEntityMetadataCollection` transfer object with merchant read global entities.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AclEntityMetadataConfigTransfer $aclEntityMetadataConfigTransfer
     *
     * @return \Generated\Shared\Transfer\AclEntityMetadataConfigTransfer
     */
    public function expand(
        AclEntityMetadataConfigTransfer $aclEntityMetadataConfigTransfer
    ): AclEntityMetadataConfigTransfer {
        $aclEntityMetadataConfigTransfer = $this->getFacade()
            ->expandAclEntityMetadataConfigWithMerchantProductComposite($aclEntityMetadataConfigTransfer);

        $aclEntityMetadataConfigTransfer = $this->getFacade()
            ->expandAclEntityMetadataConfigWithMerchantComposite($aclEntityMetadataConfigTransfer);

        $aclEntityMetadataConfigTransfer = $this->getFacade()
            ->expandAclEntityMetadataConfigWithProductOfferComposite($aclEntityMetadataConfigTransfer);

        $aclEntityMetadataConfigTransfer = $this->getFacade()
            ->expandAclEntityMetadataConfigWithMerchantReadGlobalEntities($aclEntityMetadataConfigTransfer);

        $aclEntityMetadataConfigTransfer = $this->getFacade()
            ->expandAclEntityMetadataConfigWithWhitelist($aclEntityMetadataConfigTransfer);

        return $aclEntityMetadataConfigTransfer;
    }
}
