<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Client\Product\Service\Storage;

use Generated\Shared\Transfer\AbstractProductTransfer;
use SprykerFeature\Client\Storage\Service\StorageClientInterface;
use SprykerFeature\Shared\FrontendExporter\Code\KeyBuilder\KeyBuilderInterface;

class ProductStorage implements ProductStorageInterface
{

    /**
     * @var StorageClientInterface
     */
    private $storage;

    /**
     * @var KeyBuilderInterface
     */
    private $keyBuilder;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var array
     */
    private $translations = [];

    /**
     * @param StorageClientInterface $storage
     * @param KeyBuilderInterface $keyBuilder
     * @param string $localeName
     */
    public function __construct($storage, $keyBuilder, $localeName)
    {
        $this->storage = $storage;
        $this->keyBuilder = $keyBuilder;
        $this->locale = $localeName;
    }

    public function getAbstractProductFromStorageById($idAbstractProduct)
    {
        $key = $this->keyBuilder->generateKey($idAbstractProduct, $this->locale);
        $product = $this->storage->get($key);
        return $product;
    }

}
