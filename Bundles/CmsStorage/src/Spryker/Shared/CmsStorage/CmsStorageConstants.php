<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\CmsStorage;

/**
 * Declares global environment configuration keys. Do not use it for other class constants.
 */
class CmsStorageConstants
{
    /**
     * Specification:
     * - Queue name as used for processing cms block messages
     *
     * @api
     * @var string
     */
    public const CMS_SYNC_STORAGE_QUEUE = 'sync.storage.cms';

    /**
     * Specification:
     * - Queue name as used for error cms block messages
     *
     * @api
     * @var string
     */
    public const CMS_SYNC_STORAGE_ERROR_QUEUE = 'sync.storage.cms.error';

    /**
     * Specification:
     * - Resource name, this will use for key generating
     *
     * @api
     * @var string
     */
    public const CMS_PAGE_RESOURCE_NAME = 'cms_page';
}
