<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\QuoteRequest;

use Generated\Shared\Transfer\QuoteRequestCollectionTransfer;
use Generated\Shared\Transfer\QuoteRequestFilterTransfer;
use Generated\Shared\Transfer\QuoteRequestResponseTransfer;
use Generated\Shared\Transfer\QuoteRequestTransfer;
use Generated\Shared\Transfer\QuoteRequestVersionCollectionTransfer;
use Generated\Shared\Transfer\QuoteRequestVersionFilterTransfer;
use Generated\Shared\Transfer\QuoteResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\QuoteValidationResponseTransfer;

interface QuoteRequestClientInterface
{
    /**
     * Specification:
     * - Makes Zed request.
     * - Creates "Request for Quote" for the provided company user with "Waiting" status.
     * - Generates unique reference number.
     * - Generates 1st version for the "Request for Quote" entity.
     * - Generates version reference based on unique reference number and version number.
     * - Stores provided metadata.
     * - Stores provided quote.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteRequestTransfer $quoteRequestTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteRequestResponseTransfer
     */
    public function create(QuoteRequestTransfer $quoteRequestTransfer): QuoteRequestResponseTransfer;

    /**
     * Specification:
     * - Makes Zed request.
     * - Finds a company by QuoteRequestTransfer::idQuoteRequest in the transfer.
     * - Updates fields in a "Request for Quote" entity.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteRequestTransfer $quoteRequestTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteRequestResponseTransfer
     */
    public function update(QuoteRequestTransfer $quoteRequestTransfer): QuoteRequestResponseTransfer;

    /**
     * Specification:
     * - Makes Zed request.
     * - Retrieves "Request for Quote" entities filtered by company user.
     * - Filters by quote request reference when provided.
     * - Excludes hidden "Request for Quote" entities.
     * - Selects latestVersion based on latest version id.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteRequestFilterTransfer $quoteRequestFilterTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteRequestCollectionTransfer
     */
    public function getQuoteRequestCollectionByFilter(QuoteRequestFilterTransfer $quoteRequestFilterTransfer): QuoteRequestCollectionTransfer;

    /**
     * Specification:
     * - Makes Zed request.
     * - Retrieves "Request for Quote" versions.
     * - Filters by "Request for Quote" id when provided.
     * - Filters by quote request version reference when provided.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteRequestVersionFilterTransfer $quoteRequestVersionFilterTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteRequestVersionCollectionTransfer
     */
    public function getQuoteRequestVersionCollectionByFilter(QuoteRequestVersionFilterTransfer $quoteRequestVersionFilterTransfer): QuoteRequestVersionCollectionTransfer;

    /**
     * Specification:
     * - Makes Zed request.
     * - Looks up one "Request for Quote" by provided quote request reference.
     * - Expects the related company user to be provided.
     * - Expects "Request for Quote" status to be "waiting".
     * - Sets status to "Cancelled".
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteRequestFilterTransfer $quoteRequestFilterTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteRequestResponseTransfer
     */
    public function cancelByReference(QuoteRequestFilterTransfer $quoteRequestFilterTransfer): QuoteRequestResponseTransfer;

    /**
     * Specification:
     * - Makes Zed request.
     * - Validates quote request if quote request reference exists in quote.
     * - Checks if quote request version exists in database.
     * - Checks status from quote request.
     * - Checks that the current version is the latest.
     * - Checks valid until from quote request with current time.
     * - Returns true if quote requests pass all checks.
     * - Adds error message if not valid.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteValidationResponseTransfer
     */
    public function checkCheckoutQuoteRequest(QuoteTransfer $quoteTransfer): QuoteValidationResponseTransfer;

    /**
     * Specification:
     * - Expects "Request for Quote" status to be "ready".
     * - Expects the related latest version to be provided.
     * - Replaces current customer quote by quote from latest quote request version.
     * - Locks quote.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteRequestTransfer $quoteRequestTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteResponseTransfer
     */
    public function convertQuoteRequestToQuote(QuoteRequestTransfer $quoteRequestTransfer): QuoteResponseTransfer;

    /**
     * Specification:
     * - Checks cancelable status from config.
     * - If "Request for Quote" cancelable - return true.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteRequestTransfer $quoteRequestTransfer
     *
     * @return bool
     */
    public function isQuoteRequestCancelable(QuoteRequestTransfer $quoteRequestTransfer): bool;

    /**
     * Specification:
     * - Checks convertible status from config.
     * - Checks if quote request latest version exists.
     * - If "Request for Quote" convertible - return true.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteRequestTransfer $quoteRequestTransfer
     *
     * @return bool
     */
    public function isQuoteRequestConvertible(QuoteRequestTransfer $quoteRequestTransfer): bool;
}
