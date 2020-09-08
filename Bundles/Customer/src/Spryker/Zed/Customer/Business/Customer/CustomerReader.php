<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Customer\Business\Customer;

use Generated\Shared\Transfer\CustomerCollectionTransfer;
use Generated\Shared\Transfer\CustomerCriteriaTransfer;
use Generated\Shared\Transfer\CustomerResponseTransfer;
use Spryker\Zed\Customer\Business\CustomerExpander\CustomerExpanderInterface;
use Spryker\Zed\Customer\Persistence\CustomerEntityManagerInterface;
use Spryker\Zed\Customer\Persistence\CustomerRepositoryInterface;

class CustomerReader implements CustomerReaderInterface
{
    /**
     * @var \Spryker\Zed\Customer\Persistence\CustomerEntityManagerInterface
     */
    protected $customerEntityManager;

    /**
     * @var \Spryker\Zed\Customer\Persistence\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Spryker\Zed\Customer\Business\Customer\AddressInterface
     */
    protected $addressManager;

    /**
     * @var \Spryker\Zed\Customer\Business\CustomerExpander\CustomerExpanderInterface
     */
    protected $customerExpander;

    /**
     * @param \Spryker\Zed\Customer\Persistence\CustomerEntityManagerInterface $customerEntityManager
     * @param \Spryker\Zed\Customer\Persistence\CustomerRepositoryInterface $customerRepository
     * @param \Spryker\Zed\Customer\Business\Customer\AddressInterface $addressManager
     * @param \Spryker\Zed\Customer\Business\CustomerExpander\CustomerExpanderInterface $customerExpander
     */
    public function __construct(
        CustomerEntityManagerInterface $customerEntityManager,
        CustomerRepositoryInterface $customerRepository,
        AddressInterface $addressManager,
        CustomerExpanderInterface $customerExpander
    ) {
        $this->customerEntityManager = $customerEntityManager;
        $this->customerRepository = $customerRepository;
        $this->addressManager = $addressManager;
        $this->customerExpander = $customerExpander;
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerCollectionTransfer $customerCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\CustomerCollectionTransfer
     */
    public function getCustomerCollection(CustomerCollectionTransfer $customerCollectionTransfer): CustomerCollectionTransfer
    {
        $customerCollectionTransfer = $this->customerRepository->getCustomerCollection($customerCollectionTransfer);
        $customerCollectionTransfer = $this->hydrateCustomersWithAddresses($customerCollectionTransfer);

        return $customerCollectionTransfer;
    }

    /**
     * @param string $customerReference
     *
     * @return \Generated\Shared\Transfer\CustomerResponseTransfer
     */
    public function findCustomerByReference(string $customerReference): CustomerResponseTransfer
    {
        $customerTransfer = $this->customerRepository->findCustomerByReference($customerReference);

        $customerResponseTransfer = (new CustomerResponseTransfer())
            ->setIsSuccess(false)
            ->setHasCustomer(false);

        if ($customerTransfer) {
            $customerTransfer->setAddresses($this->addressManager->getAddresses($customerTransfer));
            $customerResponseTransfer->setCustomerTransfer($customerTransfer)
                ->setHasCustomer(true)
                ->setIsSuccess(true);
        }

        return $customerResponseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerCriteriaTransfer $customerCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\CustomerResponseTransfer
     */
    public function getCustomerByCriteria(CustomerCriteriaTransfer $customerCriteriaTransfer): CustomerResponseTransfer
    {
        $customerTransfer = $this->customerRepository->findCustomerByCriteria($customerCriteriaTransfer);

        $customerResponseTransfer = (new CustomerResponseTransfer())
            ->setIsSuccess(false)
            ->setHasCustomer(false);

        if (!$customerTransfer) {
            return $customerResponseTransfer;
        }

        if ($customerCriteriaTransfer->getWithExpanders()) {
            $customerTransfer->setAddresses($this->addressManager->getAddresses($customerTransfer));
            $customerTransfer = $this->customerExpander->expand($customerTransfer);
        }

        return $customerResponseTransfer->setCustomerTransfer($customerTransfer)
            ->setHasCustomer(true)
            ->setIsSuccess(true);
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerCollectionTransfer $customerListTransfer
     *
     * @return \Generated\Shared\Transfer\CustomerCollectionTransfer
     */
    protected function hydrateCustomersWithAddresses(CustomerCollectionTransfer $customerListTransfer): CustomerCollectionTransfer
    {
        foreach ($customerListTransfer->getCustomers() as $customerTransfer) {
            $customerTransfer->setAddresses($this->addressManager->getAddresses($customerTransfer));
        }

        return $customerListTransfer;
    }
}
