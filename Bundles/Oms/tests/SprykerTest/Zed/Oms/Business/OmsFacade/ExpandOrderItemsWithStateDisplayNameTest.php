<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Oms\Business\OmsFacade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ItemTransfer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Oms
 * @group Business
 * @group OmsFacade
 * @group ExpandOrderItemsWithStateDisplayNameTest
 * Add your own group annotations below this line
 */
class ExpandOrderItemsWithStateDisplayNameTest extends Unit
{
    protected const DEFAULT_OMS_PROCESS_NAME = 'Test01';
    protected const FAKE_SALES_ORDER_ITEM_ID = 666;

    protected const SHIPPED_STATE_NAME = 'shipped';
    protected const DELIVERED_STATE_NAME = 'delivered';
    protected const SHIPPED_STATE_DISPLAY_NAME = 'in progress';

    /**
     * @var \SprykerTest\Zed\Oms\OmsBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->configureTestStateMachine([static::DEFAULT_OMS_PROCESS_NAME]);
    }

    /**
     * @return void
     */
    public function testExpandOrderItemsWithStateDisplayNameShouldExpandOrderItemsWithStateDisplayName(): void
    {
        // Arrange
        $orderTransfer = $this->tester->createOrderByStateMachineProcessName(static::DEFAULT_OMS_PROCESS_NAME);

        /** @var \Generated\Shared\Transfer\ItemTransfer $itemTransfer */
        $itemTransfer = $orderTransfer->getItems()->getIterator()->current();
        $this->tester->setItemState($itemTransfer->getIdSalesOrderItem(), static::SHIPPED_STATE_NAME);

        // Act
        $itemTransfers = $this->tester
            ->getFacade()
            ->expandOrderItemsWithStateDisplayName([$itemTransfer]);

        // Assert
        $this->assertSame(static::SHIPPED_STATE_DISPLAY_NAME, $itemTransfers[0]->getState()->getDisplayName());
    }

    /**
     * @return void
     */
    public function testExpandOrderItemsWithStateDisplayNameWithoutSalesOrderItemId(): void
    {
        // Act
        $itemTransfers = $this->tester
            ->getFacade()
            ->expandOrderItemsWithStateDisplayName([new ItemTransfer()]);

        // Assert
        $this->assertEmpty($itemTransfers[0]->getState());
    }
}
