<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\WishlistsRestApi\Processor\Wishlists;

use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Generated\Shared\Transfer\WishlistOverviewRequestTransfer;
use Generated\Shared\Transfer\WishlistOverviewResponseTransfer;
use Generated\Shared\Transfer\WishlistRequestTransfer;
use Generated\Shared\Transfer\WishlistTransfer;
use Spryker\Client\WishlistsRestApi\WishlistsRestApiClientInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\WishlistsRestApi\Dependency\Client\WishlistsRestApiToWishlistClientInterface;
use Spryker\Glue\WishlistsRestApi\Processor\Mapper\WishlistItemMapperInterface;
use Spryker\Glue\WishlistsRestApi\Processor\Mapper\WishlistMapperInterface;
use Spryker\Glue\WishlistsRestApi\Processor\RestResponseBuilder\WishlistRestResponseBuilderInterface;
use Spryker\Glue\WishlistsRestApi\WishlistsRestApiConfig;

class WishlistReader implements WishlistReaderInterface
{
    /**
     * @uses \Spryker\Zed\Wishlist\Business\Model\Reader::ERROR_MESSAGE_WISHLIST_NOT_FOUND
     */
    protected const ERROR_MESSAGE_WISHLIST_NOT_FOUND = 'wishlist.not.found';

    /**
     * @var \Spryker\Glue\WishlistsRestApi\Dependency\Client\WishlistsRestApiToWishlistClientInterface
     */
    protected $wishlistClient;

    /**
     * @var \Spryker\Glue\WishlistsRestApi\Processor\Mapper\WishlistMapperInterface
     */
    protected $wishlistResourceMapper;

    /**
     * @var \Spryker\Glue\WishlistsRestApi\Processor\Mapper\WishlistItemMapperInterface
     */
    protected $wishlistItemResourceMapper;

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface
     */
    protected $restResourceBuilder;

    /**
     * @var \Spryker\Client\WishlistsRestApi\WishlistsRestApiClientInterface
     */
    protected $wishlistsRestApiClient;

    /**
     * @var \Spryker\Glue\WishlistsRestApi\Processor\RestResponseBuilder\WishlistRestResponseBuilderInterface
     */
    protected $wishlistRestResponseBuilder;

    /**
     * @param \Spryker\Glue\WishlistsRestApi\Dependency\Client\WishlistsRestApiToWishlistClientInterface $wishlistClient
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface $restResourceBuilder
     * @param \Spryker\Glue\WishlistsRestApi\Processor\Mapper\WishlistMapperInterface $wishlistResourceMapper
     * @param \Spryker\Glue\WishlistsRestApi\Processor\Mapper\WishlistItemMapperInterface $wishlistItemResourceMapper
     * @param \Spryker\Client\WishlistsRestApi\WishlistsRestApiClientInterface $wishlistsRestApiClient
     * @param \Spryker\Glue\WishlistsRestApi\Processor\RestResponseBuilder\WishlistRestResponseBuilderInterface $wishlistRestResponseBuilder
     */
    public function __construct(
        WishlistsRestApiToWishlistClientInterface $wishlistClient,
        RestResourceBuilderInterface $restResourceBuilder,
        WishlistMapperInterface $wishlistResourceMapper,
        WishlistItemMapperInterface $wishlistItemResourceMapper,
        WishlistsRestApiClientInterface $wishlistsRestApiClient,
        WishlistRestResponseBuilderInterface $wishlistRestResponseBuilder
    ) {
        $this->wishlistClient = $wishlistClient;
        $this->restResourceBuilder = $restResourceBuilder;
        $this->wishlistResourceMapper = $wishlistResourceMapper;
        $this->wishlistItemResourceMapper = $wishlistItemResourceMapper;
        $this->wishlistsRestApiClient = $wishlistsRestApiClient;
        $this->wishlistRestResponseBuilder = $wishlistRestResponseBuilder;
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function findWishlists(RestRequestInterface $restRequest): RestResponseInterface
    {
        $wishlistUuid = $restRequest->getResource()->getId();
        $customerId = $restRequest->getRestUser()->getSurrogateIdentifier();

        if ($wishlistUuid) {
            return $this->getWishlistResponseByIdCustomerAndUuid($customerId, $wishlistUuid);
        }

        return $this->readCurrentCustomerWishlists();
    }

    /**
     * @param string $customerReference
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface[]
     */
    public function getWishlistsByCustomerReference(string $customerReference): array
    {
        $restResources = [];

        $customerTransfer = (new CustomerTransfer())->setCustomerReference($customerReference);
        $wishlistCollectionTransfer = $this->wishlistClient->getWishlistCollection($customerTransfer);

        $wishlistTransfers = $wishlistCollectionTransfer->getWishlists();

        foreach ($wishlistTransfers as $wishlistTransfer) {
            $restWishlistsAttributesTransfer = $this->wishlistResourceMapper
                ->mapWishlistTransferToRestWishlistsAttributes($wishlistTransfer);

            $wishlistResource = $this->restResourceBuilder->createRestResource(
                WishlistsRestApiConfig::RESOURCE_WISHLISTS,
                $wishlistTransfer->getUuid(),
                $restWishlistsAttributesTransfer
            );

            $restResources[] = $wishlistResource;
        }

        return $restResources;
    }

    /**
     * @param int $idCustomer
     * @param string $uuidWishlist
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    protected function getWishlistResponseByIdCustomerAndUuid(int $idCustomer, string $uuidWishlist): RestResponseInterface
    {
        $wishlistRequestTransfer = (new WishlistRequestTransfer())
            ->setIdCustomer($idCustomer)
            ->setIdWishlist($uuidWishlist);
        $wishlistResponseTransfer = $this->wishlistClient->getWishlistByIdCustomerAndUuid($wishlistRequestTransfer);

        if (!$wishlistResponseTransfer->getIsSuccess()) {
            return $this->getRestResponseByErrors($wishlistResponseTransfer->getErrors());
        }

        return $this->wishlistRestResponseBuilder
            ->createWishlistsRestResponse($wishlistResponseTransfer->getWishlist());
    }

    /**
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    protected function readCurrentCustomerWishlists(): RestResponseInterface
    {
        $restResponse = $this->restResourceBuilder->createRestResponse();

        $customerWishlistCollectionTransfer = $this->wishlistClient->getCustomerWishlistCollection();
        $customerWishlists = $customerWishlistCollectionTransfer->getWishlists();

        foreach ($customerWishlists as $wishlistTransfer) {
            $restWishlistsAttributesTransfer = $this->wishlistResourceMapper->mapWishlistTransferToRestWishlistsAttributes($wishlistTransfer);

            $wishlistResource = $this->restResourceBuilder->createRestResource(
                WishlistsRestApiConfig::RESOURCE_WISHLISTS,
                $wishlistTransfer->getUuid(),
                $restWishlistsAttributesTransfer
            );

            $restResponse->addResource($wishlistResource);
        }

        return $restResponse;
    }

    /**
     * @param \Generated\Shared\Transfer\WishlistTransfer $wishlistTransfer
     *
     * @return \Generated\Shared\Transfer\WishlistOverviewResponseTransfer
     */
    protected function getWishlistOverviewWithoutProductDetails(WishlistTransfer $wishlistTransfer): WishlistOverviewResponseTransfer
    {
        $wishlistOverviewRequestTransfer = new WishlistOverviewRequestTransfer();
        $wishlistOverviewRequestTransfer->setWishlist($wishlistTransfer);
        $wishlistOverviewRequestTransfer->setPage(0);
        $wishlistOverviewRequestTransfer->setItemsPerPage(PHP_INT_MAX);

        return $this->wishlistClient->getWishlistOverviewWithoutProductDetails($wishlistOverviewRequestTransfer);
    }

    /**
     * @param array $errors
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    protected function getRestResponseByErrors(array $errors): RestResponseInterface
    {
        foreach ($errors as $error) {
            return $this->getRestErrorResponse($error);
        }

        return $this->getRestErrorResponse();
    }

    /**
     * @param string|null $error
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    protected function getRestErrorResponse(?string $error = null): RestResponseInterface
    {
        if (!$error || $error !== static::ERROR_MESSAGE_WISHLIST_NOT_FOUND) {
            return $this->restResourceBuilder->createRestResponse()
                ->addError((new RestErrorMessageTransfer())
                    ->setDetail('Unknown error.')
                    ->setCode('')
                    ->setStatus(422));
        }

        return $this->restResourceBuilder->createRestResponse()
            ->addError((new RestErrorMessageTransfer())
                ->setDetail(WishlistsRestApiConfig::RESPONSE_DETAIL_WISHLIST_NOT_FOUND)
                ->setCode(WishlistsRestApiConfig::RESPONSE_CODE_WISHLIST_NOT_FOUND)
                ->setStatus(404));
    }
}
