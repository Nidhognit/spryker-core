<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\MerchantCommission\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\MerchantCommissionAmountTransformerRequestBuilder;
use Generated\Shared\Transfer\MerchantCommissionAmountTransformerRequestTransfer;
use Spryker\Zed\MerchantCommissionExtension\Communication\Dependency\Plugin\MerchantCommissionCalculatorPluginInterface;
use SprykerTest\Zed\MerchantCommission\MerchantCommissionBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group MerchantCommission
 * @group Business
 * @group Facade
 * @group TransformMerchantCommissionAmountForPersistenceTest
 * Add your own group annotations below this line
 */
class TransformMerchantCommissionAmountForPersistenceTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_MERCHANT_COMMISSION_CALCULATOR_PLUGIN_TYPE = 'test-calculation-type';

    /**
     * @uses \Spryker\Zed\MerchantCommission\MerchantCommissionDependencyProvider::PLUGINS_MERCHANT_COMMISSION_CALCULATOR
     *
     * @var string
     */
    protected const PLUGINS_MERCHANT_COMMISSION_CALCULATOR = 'PLUGINS_MERCHANT_COMMISSION_CALCULATOR';

    /**
     * @var \SprykerTest\Zed\MerchantCommission\MerchantCommissionBusinessTester
     */
    protected MerchantCommissionBusinessTester $tester;

    /**
     * @return void
     */
    public function testShouldTransformMerchantCommissionAmount(): void
    {
        // Arrange
        $expectedTransformedAmount = 1000;
        $this->tester->setDependency(static::PLUGINS_MERCHANT_COMMISSION_CALCULATOR, [
            $this->createMerchantCommissionCalculatorPluginMock($expectedTransformedAmount),
        ]);

        $merchantCommissionAmountTransformerRequestTransfer = (new MerchantCommissionAmountTransformerRequestBuilder([
            MerchantCommissionAmountTransformerRequestTransfer::CALCULATOR_TYPE_PLUGIN => static::TEST_MERCHANT_COMMISSION_CALCULATOR_PLUGIN_TYPE,
            MerchantCommissionAmountTransformerRequestTransfer::AMOUNT_FOR_PERSISTENCE => 100,
        ]))->build();

        // Act
        $transformedAmount = $this->tester->getFacade()->transformMerchantCommissionAmountForPersistence(
            $merchantCommissionAmountTransformerRequestTransfer,
        );

        // Assert
        $this->assertSame($expectedTransformedAmount, $transformedAmount);
    }

    /**
     * @param int $transformedAmount
     *
     * @return \Spryker\Zed\MerchantCommissionExtension\Communication\Dependency\Plugin\MerchantCommissionCalculatorPluginInterface
     */
    protected function createMerchantCommissionCalculatorPluginMock(int $transformedAmount): MerchantCommissionCalculatorPluginInterface
    {
        $merchantCommissionCalculatorPluginMock = $this->getMockBuilder(MerchantCommissionCalculatorPluginInterface::class)
            ->getMock();
        $merchantCommissionCalculatorPluginMock->expects($this->once())
            ->method('getCalculatorType')
            ->willReturn('test-calculation-type');
        $merchantCommissionCalculatorPluginMock->expects($this->once())
            ->method('transformAmountForPersistence')
            ->willReturn($transformedAmount);

        return $merchantCommissionCalculatorPluginMock;
    }
}
