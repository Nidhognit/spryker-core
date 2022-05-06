<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Oauth\Business\Model\League;

use Generated\Shared\Transfer\OauthErrorTransfer;
use Generated\Shared\Transfer\OauthGrantTypeConfigurationTransfer;
use Generated\Shared\Transfer\OauthRequestTransfer;
use Generated\Shared\Transfer\OauthResponseTransfer;
use Spryker\Zed\Oauth\Business\Model\League\Grant\GrantBuilderInterface;
use Spryker\Zed\Oauth\Business\Model\League\Grant\GrantTypeConfigurationLoaderInterface;
use Spryker\Zed\Oauth\Business\Model\League\Grant\GrantTypeExecutorInterface;
use Spryker\Zed\Oauth\Business\Model\League\Grant\OauthGrantTypeConfigurationLoaderInterface;
use Spryker\Zed\Oauth\OauthConfig;

class AccessTokenRequestExecutor implements AccessTokenRequestExecutorInterface
{
    /**
     * @var \Spryker\Zed\Oauth\Business\Model\League\Grant\GrantTypeConfigurationLoaderInterface
     */
    protected $grantTypeConfigurationLoader;

    /**
     * @var \Spryker\Zed\Oauth\Business\Model\League\Grant\GrantBuilderInterface
     */
    protected $grantTypeBuilder;

    /**
     * @var \Spryker\Zed\Oauth\Business\Model\League\Grant\GrantTypeExecutorInterface
     */
    protected $grantTypeExecutor;

    /**
     * @var \Spryker\Zed\Oauth\OauthConfig
     */
    protected $oauthConfig;

    /**
     * @var \Spryker\Zed\Oauth\Business\Model\League\Grant\OauthGrantTypeConfigurationLoaderInterface
     */
    protected $oauthGrantTypeConfigurationLoader;

    /**
     * @param \Spryker\Zed\Oauth\Business\Model\League\Grant\GrantTypeConfigurationLoaderInterface $grantTypeConfigurationLoader
     * @param \Spryker\Zed\Oauth\Business\Model\League\Grant\GrantBuilderInterface $grantTypeBuilder
     * @param \Spryker\Zed\Oauth\Business\Model\League\Grant\GrantTypeExecutorInterface $grantTypeExecutor
     * @param \Spryker\Zed\Oauth\OauthConfig $oauthConfig
     * @param \Spryker\Zed\Oauth\Business\Model\League\Grant\OauthGrantTypeConfigurationLoaderInterface $oauthGrantTypeConfigurationLoader
     */
    public function __construct(
        GrantTypeConfigurationLoaderInterface $grantTypeConfigurationLoader,
        GrantBuilderInterface $grantTypeBuilder,
        GrantTypeExecutorInterface $grantTypeExecutor,
        OauthConfig $oauthConfig,
        OauthGrantTypeConfigurationLoaderInterface $oauthGrantTypeConfigurationLoader
    ) {
        $this->grantTypeConfigurationLoader = $grantTypeConfigurationLoader;
        $this->grantTypeBuilder = $grantTypeBuilder;
        $this->grantTypeExecutor = $grantTypeExecutor;
        $this->oauthConfig = $oauthConfig;
        $this->oauthGrantTypeConfigurationLoader = $oauthGrantTypeConfigurationLoader;
    }

    /**
     * @param \Generated\Shared\Transfer\OauthRequestTransfer $oauthRequestTransfer
     *
     * @return \Generated\Shared\Transfer\OauthResponseTransfer
     */
    public function executeByRequest(OauthRequestTransfer $oauthRequestTransfer): OauthResponseTransfer
    {
        $oauthGrantTypeConfigurationTransfer = new OauthGrantTypeConfigurationTransfer();
        $glueAuthenticationRequestContextTransfer = $oauthRequestTransfer->getGlueAuthenticationRequestContext();

        if ($glueAuthenticationRequestContextTransfer !== null) {
            $oauthGrantTypeConfigurationTransfer = $this->oauthGrantTypeConfigurationLoader
                ->loadGrantTypeConfiguration($oauthRequestTransfer, $glueAuthenticationRequestContextTransfer);
        }

        /*
         * For BC-reason only.
         */
        if ($glueAuthenticationRequestContextTransfer === null) {
            $oauthGrantTypeConfigurationTransfer = $this->loadGrantTypeConfigurationByGrantType($oauthRequestTransfer);
        }

        if (!$oauthGrantTypeConfigurationTransfer) {
            return $this->createUnsupportedGrantTypeError($oauthRequestTransfer);
        }

        $grant = $this->grantTypeBuilder->buildGrant($oauthGrantTypeConfigurationTransfer);
        $oauthRequestTransfer = $this->expandOauthRequestTransfer($oauthRequestTransfer);

        return $this->grantTypeExecutor->processAccessTokenRequest($oauthRequestTransfer, $grant);
    }

    /**
     * @param \Generated\Shared\Transfer\OauthRequestTransfer $oauthRequestTransfer
     *
     * @return \Generated\Shared\Transfer\OauthRequestTransfer
     */
    protected function expandOauthRequestTransfer(OauthRequestTransfer $oauthRequestTransfer): OauthRequestTransfer
    {
        if (!$oauthRequestTransfer->getClientId() && !$oauthRequestTransfer->getClientSecret()) {
            $oauthRequestTransfer
                ->setClientId($this->oauthConfig->getClientId())
                ->setClientSecret($this->oauthConfig->getClientSecret());
        }

        return $oauthRequestTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OauthRequestTransfer $oauthRequestTransfer
     *
     * @return \Generated\Shared\Transfer\OauthResponseTransfer
     */
    protected function createUnsupportedGrantTypeError(OauthRequestTransfer $oauthRequestTransfer): OauthResponseTransfer
    {
        $oauthResponseTransfer = new OauthResponseTransfer();
        $oauthErrorTransfer = new OauthErrorTransfer();
        $oauthErrorTransfer->setMessage(sprintf('Grant type "%s" not found', $oauthRequestTransfer->getGrantType()))
        ->setErrorType('unsupported_grant_type');
        $oauthResponseTransfer->setError($oauthErrorTransfer)
            ->setIsValid(false);

        return $oauthResponseTransfer;
    }

    /**
     * @deprecated Use {@link \Spryker\Zed\Oauth\Business\Model\League\Grant\OauthGrantTypeConfigurationLoaderInterface::loadGrantTypeConfiguration()} instead.
     *
     * @param \Generated\Shared\Transfer\OauthRequestTransfer $oauthRequestTransfer
     *
     * @return \Generated\Shared\Transfer\OauthGrantTypeConfigurationTransfer|null
     */
    protected function loadGrantTypeConfigurationByGrantType(
        OauthRequestTransfer $oauthRequestTransfer
    ): ?OauthGrantTypeConfigurationTransfer {
        return $this->grantTypeConfigurationLoader->loadGrantTypeConfigurationByGrantType($oauthRequestTransfer);
    }
}
