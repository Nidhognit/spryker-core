<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPackagingUnit\Business\Model\OrderItem;

use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer;
use Spryker\Zed\Glossary\Business\Exception\MissingTranslationException;
use Spryker\Zed\ProductPackagingUnit\Dependency\Facade\ProductPackagingUnitToGlossaryFacadeInterface;

class OrderItemExpander implements OrderItemExpanderInterface
{
    /**
     * @var \Spryker\Zed\ProductPackagingUnit\Dependency\Facade\ProductPackagingUnitToGlossaryFacadeInterface
     */
    protected $glossaryFacade;

    /**
     * @param \Spryker\Zed\ProductPackagingUnit\Dependency\Facade\ProductPackagingUnitToGlossaryFacadeInterface $glossaryFacade
     */
    public function __construct(ProductPackagingUnitToGlossaryFacadeInterface $glossaryFacade)
    {
        $this->glossaryFacade = $glossaryFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer $salesOrderItemEntity
     *
     * @return \Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer
     */
    public function expandSalesOrderItemWithAmountSalesUnit(ItemTransfer $itemTransfer, SpySalesOrderItemEntityTransfer $salesOrderItemEntity): SpySalesOrderItemEntityTransfer
    {
        if (!$itemTransfer->getAmountSalesUnit()) {
            return $salesOrderItemEntity;
        }

        $amountBaseMeasurementUnitName = $itemTransfer->getAmountSalesUnit()
            ->getProductMeasurementBaseUnit()
            ->getProductMeasurementUnit()
            ->getName();

        $amountMeasurementUnitName = $itemTransfer->getAmountSalesUnit()
            ->getProductMeasurementUnit()
            ->getName();

        $salesOrderItemEntity->setAmountBaseMeasurementUnitName(
            $this->translate($amountBaseMeasurementUnitName)
        );
        $salesOrderItemEntity->setAmountMeasurementUnitName(
            $this->translate($amountMeasurementUnitName)
        );

        $salesOrderItemEntity->setAmountMeasurementUnitPrecision($itemTransfer->getAmountSalesUnit()->getPrecision());
        $salesOrderItemEntity->setAmountMeasurementUnitConversion($itemTransfer->getAmountSalesUnit()->getConversion());

        return $salesOrderItemEntity;
    }

    /**
     * @param string $msg
     *
     * @return string
     */
    protected function translate(string $msg)
    {
        try {
            $localizedMsg = $this->glossaryFacade
                ->translate($msg);
        } catch (MissingTranslationException $e) {
            $localizedMsg = $msg;
        }

        return $localizedMsg;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer $salesOrderItemEntity
     *
     * @return \Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer
     */
    public function expandSalesOrderItemWithAmountAndAmountSku(ItemTransfer $itemTransfer, SpySalesOrderItemEntityTransfer $salesOrderItemEntity): SpySalesOrderItemEntityTransfer
    {
        if (!$itemTransfer->getAmountLeadProduct()) {
            return $salesOrderItemEntity;
        }

        $packagingUnitLeadProductSku = $itemTransfer->getAmountLeadProduct()->getSku();
        $packagingUnitAmount = $itemTransfer->getAmount();

        $packagingUnitLeadProductAmount = (int)($packagingUnitAmount / $itemTransfer->getQuantity());

        $salesOrderItemEntity->setAmount($packagingUnitLeadProductAmount);
        $salesOrderItemEntity->setAmountSku($packagingUnitLeadProductSku);

        return $salesOrderItemEntity;
    }
}
