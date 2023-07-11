<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\DynamicEntityBackendApi\Mapper;

use Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer;
use Generated\Shared\Transfer\DynamicEntityConditionsTransfer;
use Generated\Shared\Transfer\DynamicEntityCriteriaTransfer;
use Generated\Shared\Transfer\DynamicEntityFieldConditionTransfer;
use Generated\Shared\Transfer\DynamicEntityTransfer;
use Generated\Shared\Transfer\GlueRequestTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Spryker\Glue\DynamicEntityBackendApi\Dependency\Service\DynamicEntityBackendApiToUtilEncodingServiceInterface;
use Spryker\Glue\DynamicEntityBackendApi\DynamicEntityBackendApiConfig;
use Symfony\Component\HttpFoundation\Request;

class GlueRequestDynamicEntityMapper
{
    /**
     * @var string
     */
    protected const IDENTIFIER = 'identifier';

    /**
     * @var string
     */
    protected const DYNAMIC_ENTITY_PATH_PATTERN = '/\/([^\/]+)\/([\w-]+)/';

    /**
     * @var \Spryker\Glue\DynamicEntityBackendApi\Dependency\Service\DynamicEntityBackendApiToUtilEncodingServiceInterface
     */
    protected DynamicEntityBackendApiToUtilEncodingServiceInterface $serviceUtilEncoding;

    /**
     * @var \Spryker\Glue\DynamicEntityBackendApi\DynamicEntityBackendApiConfig
     */
    protected DynamicEntityBackendApiConfig $config;

    /**
     * @param \Spryker\Glue\DynamicEntityBackendApi\Dependency\Service\DynamicEntityBackendApiToUtilEncodingServiceInterface $serviceUtilEncoding
     * @param \Spryker\Glue\DynamicEntityBackendApi\DynamicEntityBackendApiConfig $config
     */
    public function __construct(DynamicEntityBackendApiToUtilEncodingServiceInterface $serviceUtilEncoding, DynamicEntityBackendApiConfig $config)
    {
        $this->serviceUtilEncoding = $serviceUtilEncoding;
        $this->config = $config;
    }

    /**
     * @param \Generated\Shared\Transfer\GlueRequestTransfer $glueRequestTransfer
     * @param string|null $id
     *
     * @return \Generated\Shared\Transfer\DynamicEntityCriteriaTransfer
     */
    public function mapGlueRequestToDynamicEntityCriteriaTransfer(
        GlueRequestTransfer $glueRequestTransfer,
        ?string $id = null
    ): DynamicEntityCriteriaTransfer {
        $dynamicEntityCriteriaTransfer = new DynamicEntityCriteriaTransfer();

        $paginationTransfer = $this->setDefaultPaginationLimit($glueRequestTransfer->getPagination());

        $dynamicEntityCriteriaTransfer->setPagination($paginationTransfer);

        $dynamicEntityConditionsTransfer = $this->createDynamicEntityConditionsTransfer($glueRequestTransfer, $id);
        $dynamicEntityCriteriaTransfer->setDynamicEntityConditions($dynamicEntityConditionsTransfer);

        return $dynamicEntityCriteriaTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\GlueRequestTransfer $glueRequestTransfer
     *
     * @return \Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer|null
     */
    public function mapGlueRequestToDynamicEntityCollectionRequestTransfer(
        GlueRequestTransfer $glueRequestTransfer
    ): ?DynamicEntityCollectionRequestTransfer {
        $dynamicEntityCollectionRequestTransfer = $this->createDynamicEntityCollectionRequestTransfer($glueRequestTransfer);

        if ($glueRequestTransfer->getContent() === null) {
            return null;
        }

        $dataCollection = $this->serviceUtilEncoding->decodeJson($glueRequestTransfer->getContent(), true)['data'] ?? null;

        if ($dataCollection === null || $dataCollection === []) {
            return null;
        }

        if ($glueRequestTransfer->getResourceOrFail()->getId() !== null) {
            return $this->mapContentForIdRequest($dataCollection, $glueRequestTransfer, $dynamicEntityCollectionRequestTransfer);
        }

        return $this->mapContentForCollectionRequest($dataCollection, $dynamicEntityCollectionRequestTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\GlueRequestTransfer $glueRequestTransfer
     *
     * @return \Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer
     */
    protected function createDynamicEntityCollectionRequestTransfer(GlueRequestTransfer $glueRequestTransfer): DynamicEntityCollectionRequestTransfer
    {
        $dynamicEntityCollectionRequestTransfer = new DynamicEntityCollectionRequestTransfer();
        $dynamicEntityCollectionRequestTransfer->setTableAlias(
            $this->extractTableAlias($glueRequestTransfer->getPathOrFail()),
        );

        if ($glueRequestTransfer->getResourceOrFail()->getMethod() === Request::METHOD_PUT) {
            $dynamicEntityCollectionRequestTransfer->setIsCreatable(true);
        }

        return $dynamicEntityCollectionRequestTransfer;
    }

    /**
     * @param array<mixed> $dataCollection
     * @param \Generated\Shared\Transfer\GlueRequestTransfer $glueRequestTransfer
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer|null
     */
    protected function mapContentForIdRequest(
        array $dataCollection,
        GlueRequestTransfer $glueRequestTransfer,
        DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer
    ): ?DynamicEntityCollectionRequestTransfer {
        if ($this->isAssociativeArray($dataCollection) === false) {
            return null;
        }

        $dataCollection[static::IDENTIFIER] = $glueRequestTransfer->getResourceOrFail()->getId();

        return $this->mapRequestContentToDynamicEntityTransfer($dynamicEntityCollectionRequestTransfer, $dataCollection);
    }

    /**
     * @param array<mixed> $dataCollection
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer|null
     */
    protected function mapContentForCollectionRequest(
        array $dataCollection,
        DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer
    ): ?DynamicEntityCollectionRequestTransfer {
        foreach ($dataCollection as $item) {
            if (!is_array($item)) {
                return null;
            }

            $dynamicEntityCollectionRequestTransfer = $this->mapRequestContentToDynamicEntityTransfer($dynamicEntityCollectionRequestTransfer, $item);
        }

        return $dynamicEntityCollectionRequestTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer
     * @param array<mixed> $fields
     *
     * @return \Generated\Shared\Transfer\DynamicEntityCollectionRequestTransfer
     */
    protected function mapRequestContentToDynamicEntityTransfer(
        DynamicEntityCollectionRequestTransfer $dynamicEntityCollectionRequestTransfer,
        array $fields
    ): DynamicEntityCollectionRequestTransfer {
        $dynamicEntityCollectionRequestTransfer->addDynamicEntity(
            (new DynamicEntityTransfer())->setFields($fields),
        );

        return $dynamicEntityCollectionRequestTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\GlueRequestTransfer $glueRequestTransfer
     * @param string|null $id
     *
     * @return \Generated\Shared\Transfer\DynamicEntityConditionsTransfer
     */
    protected function createDynamicEntityConditionsTransfer(
        GlueRequestTransfer $glueRequestTransfer,
        ?string $id = null
    ): DynamicEntityConditionsTransfer {
        $dynamicEntityConditionsTransfer = (new DynamicEntityConditionsTransfer())
            ->setTableAlias($this->extractTableAlias($glueRequestTransfer->getPathOrFail()));

        if ($id !== null) {
            $dynamicEntityConditionsTransfer->addFieldCondition(
                (new DynamicEntityFieldConditionTransfer())
                    ->setName(static::IDENTIFIER)
                    ->setValue($id),
            );
        }

        foreach ($glueRequestTransfer->getFilters() as $filter) {
            $dynamicEntityConditionsTransfer->addFieldCondition(
                (new DynamicEntityFieldConditionTransfer())
                    ->setName($filter->getField())
                    ->setValue($filter->getValue()),
            );
        }

        return $dynamicEntityConditionsTransfer;
    }

    /**
     * @param string $string
     *
     * @return string|null
     */
    protected function extractTableAlias(string $string): ?string
    {
        $matches = [];

        if (preg_match(static::DYNAMIC_ENTITY_PATH_PATTERN, $string, $matches)) {
            return $matches[2];
        }

        return null;
    }

    /**
     * @param array<mixed> $array
     *
     * @return bool
     */
    protected function isAssociativeArray(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * @param \Generated\Shared\Transfer\PaginationTransfer|null $paginationTransfer
     *
     * @return \Generated\Shared\Transfer\PaginationTransfer
     */
    protected function setDefaultPaginationLimit(?PaginationTransfer $paginationTransfer): PaginationTransfer
    {
        if ($paginationTransfer === null) {
            $paginationTransfer = new PaginationTransfer();
            $paginationTransfer->setLimit($this->config->getDefaultPaginationLimit());

            return $paginationTransfer;
        }

        if ($paginationTransfer->getLimit() === null) {
            $paginationTransfer->setLimit($this->config->getDefaultPaginationLimit());
        }

        return $paginationTransfer;
    }
}
