<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ServicePoint\Business\Validator\Rule\ServicePointService;

use ArrayObject;
use Generated\Shared\Transfer\ErrorCollectionTransfer;
use Generated\Shared\Transfer\ServicePointConditionsTransfer;
use Generated\Shared\Transfer\ServicePointCriteriaTransfer;
use Generated\Shared\Transfer\ServicePointTransfer;
use Spryker\Zed\ServicePoint\Business\Validator\Util\ErrorAdderInterface;
use Spryker\Zed\ServicePoint\Persistence\ServicePointRepositoryInterface;

class ServicePointUuidExistenceServicePointServiceValidatorRule implements ServicePointServiceValidatorRuleInterface
{
    /**
     * @var string
     */
    protected const GLOSSARY_KEY_VALIDATION_SERVICE_POINT_ENTITY_NOT_FOUND = 'service_point.validation.service_point_entity_not_found';

    /**
     * @var \Spryker\Zed\ServicePoint\Persistence\ServicePointRepositoryInterface
     */
    protected ServicePointRepositoryInterface $servicePointRepository;

    /**
     * @var \Spryker\Zed\ServicePoint\Business\Validator\Util\ErrorAdderInterface
     */
    protected ErrorAdderInterface $errorAdder;

    /**
     * @param \Spryker\Zed\ServicePoint\Persistence\ServicePointRepositoryInterface $servicePointRepository
     * @param \Spryker\Zed\ServicePoint\Business\Validator\Util\ErrorAdderInterface $errorAdder
     */
    public function __construct(
        ServicePointRepositoryInterface $servicePointRepository,
        ErrorAdderInterface $errorAdder
    ) {
        $this->servicePointRepository = $servicePointRepository;
        $this->errorAdder = $errorAdder;
    }

    /**
     * @param \ArrayObject<array-key, \Generated\Shared\Transfer\ServicePointServiceTransfer> $servicePointServiceTransfers
     *
     * @return \Generated\Shared\Transfer\ErrorCollectionTransfer
     */
    public function validate(ArrayObject $servicePointServiceTransfers): ErrorCollectionTransfer
    {
        $errorCollectionTransfer = new ErrorCollectionTransfer();

        foreach ($servicePointServiceTransfers as $entityIdentifier => $servicePointServiceTransfer) {
            if (!$this->hasServicePointWithUuid($servicePointServiceTransfer->getServicePointOrFail())) {
                $this->errorAdder->addError(
                    $errorCollectionTransfer,
                    $entityIdentifier,
                    static::GLOSSARY_KEY_VALIDATION_SERVICE_POINT_ENTITY_NOT_FOUND,
                );
            }
        }

        return $errorCollectionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ServicePointTransfer $servicePointTransfer
     *
     * @return bool
     */
    protected function hasServicePointWithUuid(ServicePointTransfer $servicePointTransfer): bool
    {
        $servicePointConditionsTransfer = (new ServicePointConditionsTransfer())
            ->addUuid($servicePointTransfer->getUuidOrFail());

        $servicePointCriteriaTransfer = (new ServicePointCriteriaTransfer())
            ->setServicePointConditions($servicePointConditionsTransfer);

        /** @var \ArrayObject<array-key, \Generated\Shared\Transfer\ServicePointTransfer> $servicePointTransfers */
        $servicePointTransfers = $this->servicePointRepository
            ->getServicePointCollection($servicePointCriteriaTransfer)
            ->getServicePoints();

        return $servicePointTransfers->count() === 1;
    }
}
