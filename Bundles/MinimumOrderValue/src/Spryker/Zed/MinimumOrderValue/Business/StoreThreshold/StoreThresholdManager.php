<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MinimumOrderValue\Business\StoreThreshold;

use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\MinimumOrderValueTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Zed\MinimumOrderValue\Business\Strategies\Exception\StrategyInvalidArgumentException;
use Spryker\Zed\MinimumOrderValue\Business\Strategies\MinimumOrderValueStrategyResolverInterface;
use Spryker\Zed\MinimumOrderValue\Persistence\MinimumOrderValueEntityManagerInterface;

class StoreThresholdManager implements StoreThresholdManagerInterface
{
    /**
     * @var \Spryker\Zed\MinimumOrderValue\Business\Strategies\MinimumOrderValueStrategyResolverInterface
     */
    protected $minimumOrderValueStrategyResolver;

    /**
     * @var \Spryker\Zed\MinimumOrderValue\Persistence\MinimumOrderValueEntityManagerInterface
     */
    protected $minimumOrderValueEntityManager;

    /**
     * @param \Spryker\Zed\MinimumOrderValue\Business\Strategies\MinimumOrderValueStrategyResolverInterface $minimumOrderValueStrategyResolver
     * @param \Spryker\Zed\MinimumOrderValue\Persistence\MinimumOrderValueEntityManagerInterface $minimumOrderValueEntityManager
     */
    public function __construct(
        MinimumOrderValueStrategyResolverInterface $minimumOrderValueStrategyResolver,
        MinimumOrderValueEntityManagerInterface $minimumOrderValueEntityManager
    ) {
        $this->minimumOrderValueStrategyResolver = $minimumOrderValueStrategyResolver;
        $this->minimumOrderValueEntityManager = $minimumOrderValueEntityManager;
    }

    /**
     * @param string $strategyKey
     * @param \Generated\Shared\Transfer\StoreTransfer $storeTransfer
     * @param \Generated\Shared\Transfer\CurrencyTransfer $currencyTransfer
     * @param int $value
     * @param int|null $fee
     *
     * @throws \Spryker\Zed\MinimumOrderValue\Business\Strategies\Exception\StrategyInvalidArgumentException
     *
     * @return \Generated\Shared\Transfer\MinimumOrderValueTransfer
     */
    public function setStoreThreshold(
        string $strategyKey,
        StoreTransfer $storeTransfer,
        CurrencyTransfer $currencyTransfer,
        int $value,
        ?int $fee = null
    ): MinimumOrderValueTransfer {
        $minimumOrderValueStrategy = $this->minimumOrderValueStrategyResolver
            ->resolveMinimumOrderValueStrategy($strategyKey);

        if (!$minimumOrderValueStrategy->validate($value, $fee)) {
            throw new StrategyInvalidArgumentException();
        }

        $minimumOrderValueStrategyTransfer = $this->minimumOrderValueEntityManager
            ->saveMinimumOrderValueType($minimumOrderValueStrategy->toTransfer());

        return $this->minimumOrderValueEntityManager
            ->setStoreThreshold(
                $minimumOrderValueStrategyTransfer,
                $storeTransfer,
                $currencyTransfer,
                $value,
                $fee
            );
    }
}
