<?php

/**
 * Copyright © 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\Testify\Config;

interface TestifyConfigInterface
{

    /**
     * @param string $key
     * @param string|int|float|array|bool $value
     *
     * @return void
     */
    public function set($key, $value);

}
