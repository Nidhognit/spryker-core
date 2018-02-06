<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\UrlStorage;

use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;
use Spryker\Client\UrlStorage\Dependency\Client\UrlStorageToStorageBridge;
use Spryker\Client\UrlStorage\Dependency\Service\UrlStorageToSynchronizationServiceBridge;
use Spryker\Shared\Kernel\Store;

class UrlStorageDependencyProvider extends AbstractDependencyProvider
{
    const CLIENT_STORAGE = 'CLIENT_STORAGE';
    const SERVICE_SYNCHRONIZATION = 'SERVICE_SYNCHRONIZATION';
    const PLUGINS_URL_STORAGE_RESOURCE_MAPPER = 'PLUGINS_URL_STORAGE_RESOURCE_MAPPER';
    const STORE = 'STORE';

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    public function provideServiceLayerDependencies(Container $container)
    {
        $container[self::CLIENT_STORAGE] = function (Container $container) {
            return new UrlStorageToStorageBridge($container->getLocator()->storage()->client());
        };

        $container[self::SERVICE_SYNCHRONIZATION] = function (Container $container) {
            return new UrlStorageToSynchronizationServiceBridge($container->getLocator()->synchronization()->service());
        };

        $container[self::PLUGINS_URL_STORAGE_RESOURCE_MAPPER] = function (Container $container) {
            return $this->getUrlStorageResourceMapperPlugins();
        };

        $container[static::STORE] = function (Container $container) {
            return Store::getInstance();
        };

        return $container;
    }

    /**
     * @return \Spryker\Client\UrlStorage\Dependency\Plugin\UrlStorageResourceMapperPluginInterface[]
     */
    protected function getUrlStorageResourceMapperPlugins()
    {
        return [];
    }
}
