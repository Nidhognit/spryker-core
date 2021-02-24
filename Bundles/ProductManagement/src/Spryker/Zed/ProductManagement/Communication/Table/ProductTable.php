<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductManagement\Communication\Table;

use Generated\Shared\Transfer\LocaleTransfer;
use Orm\Zed\Product\Persistence\Map\SpyProductAbstractLocalizedAttributesTableMap;
use Orm\Zed\Product\Persistence\Map\SpyProductAbstractTableMap;
use Orm\Zed\Product\Persistence\SpyProductAbstract;
use Orm\Zed\Tax\Persistence\Map\SpyTaxSetTableMap;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;
use Spryker\Zed\Product\Persistence\ProductQueryContainerInterface;
use Spryker\Zed\ProductManagement\Communication\Controller\EditController;
use Spryker\Zed\ProductManagement\Communication\Helper\ProductTypeHelperInterface;
use Spryker\Zed\ProductManagement\Persistence\ProductManagementRepositoryInterface;

class ProductTable extends AbstractProductTable
{
    public const COL_ID_PRODUCT_ABSTRACT = 'id_product_abstract';
    public const COL_NAME = 'name';
    public const COL_SKU = 'sku';
    public const COL_TAX_SET = 'tax_set';
    public const COL_VARIANT_COUNT = 'variants';
    public const COL_STATUS = 'status';
    public const COL_ACTIONS = 'actions';
    public const COL_STORE_RELATION = 'store_relation';
    public const COL_PRODUCT_TYPES = 'product_types';

    protected const COL_NAME_FALLBACK = 'name_fallback';
    protected const RELATION_LOCALE_FALLBACK = 'locale_fallback';

    /**
     * @var \Spryker\Zed\Product\Persistence\ProductQueryContainerInterface
     */
    protected $productQueryQueryContainer;

    /**
     * @var \Generated\Shared\Transfer\LocaleTransfer
     */
    protected $localeTransfer;

    /**
     * @var \Spryker\Zed\ProductManagement\Communication\Helper\ProductTypeHelperInterface
     */
    protected $productTypeHelper;

    /**
     * @var \Spryker\Zed\ProductManagement\Persistence\ProductManagementRepositoryInterface
     */
    protected $productManagementRepository;

    /**
     * @var \Spryker\Zed\ProductManagementExtension\Dependency\Plugin\ProductTableDataExpanderPluginInterface[]
     */
    protected $productTableDataExpanderPlugins;

    /**
     * @param \Spryker\Zed\Product\Persistence\ProductQueryContainerInterface $productQueryContainer
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     * @param \Spryker\Zed\ProductManagement\Communication\Helper\ProductTypeHelperInterface $productTypeHelper
     * @param \Spryker\Zed\ProductManagement\Persistence\ProductManagementRepositoryInterface $productManagementRepository
     * @param \Spryker\Zed\ProductManagementExtension\Dependency\Plugin\ProductTableDataExpanderPluginInterface[] $productTableDataExpanderPlugins
     */
    public function __construct(
        ProductQueryContainerInterface $productQueryContainer,
        LocaleTransfer $localeTransfer,
        ProductTypeHelperInterface $productTypeHelper,
        ProductManagementRepositoryInterface $productManagementRepository,
        array $productTableDataExpanderPlugins
    ) {
        $this->productQueryQueryContainer = $productQueryContainer;
        $this->localeTransfer = $localeTransfer;
        $this->productTypeHelper = $productTypeHelper;
        $this->productManagementRepository = $productManagementRepository;
        $this->productTableDataExpanderPlugins = $productTableDataExpanderPlugins;
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return mixed
     */
    protected function configure(TableConfiguration $config)
    {
        $url = Url::generate(
            '/table',
            $this->getRequest()->query->all()
        );

        $config->setUrl($url);

        $config->setHeader([
            static::COL_ID_PRODUCT_ABSTRACT => 'Product ID',
            static::COL_NAME => 'Name',
            static::COL_SKU => 'Sku',
            static::COL_TAX_SET => 'Tax Set',
            static::COL_VARIANT_COUNT => 'Variants',
            static::COL_STATUS => 'Status',
            static::COL_PRODUCT_TYPES => 'Types',
            static::COL_STORE_RELATION => 'Stores',
            static::COL_ACTIONS => 'Actions',
        ]);

        $config->setRawColumns([
            static::COL_STATUS,
            static::COL_PRODUCT_TYPES,
            static::COL_STORE_RELATION,
            static::COL_ACTIONS,
        ]);

        $config->setSearchable([
            SpyProductAbstractTableMap::COL_SKU,
            SpyProductAbstractLocalizedAttributesTableMap::COL_NAME,
            SpyTaxSetTableMap::COL_NAME,
        ]);

        $config->setSortable([
            static::COL_ID_PRODUCT_ABSTRACT,
            static::COL_SKU,
            static::COL_NAME,
            static::COL_TAX_SET,
        ]);

        $config->setDefaultSortDirection(TableConfiguration::SORT_DESC);

        return $config;
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return mixed
     */
    protected function prepareData(TableConfiguration $config)
    {
        $query = $this
            ->productQueryQueryContainer
            ->queryProductAbstract()
            ->leftJoinSpyTaxSet()
            ->leftJoinSpyProductAbstractLocalizedAttributes()
            ->addJoinCondition('SpyProductAbstractLocalizedAttributes', 'SpyProductAbstractLocalizedAttributes.fkLocale = ?', $this->localeTransfer->getIdLocale())
            ->withColumn(SpyProductAbstractLocalizedAttributesTableMap::COL_NAME, static::COL_NAME)
            ->leftJoinSpyProductAbstractLocalizedAttributes(static::RELATION_LOCALE_FALLBACK)
            ->withColumn(static::RELATION_LOCALE_FALLBACK . '.name', static::COL_NAME_FALLBACK)
            ->withColumn(SpyTaxSetTableMap::COL_NAME, static::COL_TAX_SET)
            ->groupByIdProductAbstract();

        $query = $this->expandPropelQuery($query);

        $queryResults = $this->runQuery($query, $config, true);

        $productAbstractCollection = [];
        foreach ($queryResults as $productAbstractEntity) {
            $productAbstractCollection[] = $this->generateItem($productAbstractEntity);
        }

        return $productAbstractCollection;
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductAbstract $productAbstractEntity
     *
     * @return array
     */
    protected function generateItem(SpyProductAbstract $productAbstractEntity)
    {
        $item = [
            static::COL_ID_PRODUCT_ABSTRACT => $productAbstractEntity->getIdProductAbstract(),
            static::COL_SKU => $productAbstractEntity->getSku(),
            static::COL_NAME => $this->resolveProductName($productAbstractEntity),
            static::COL_TAX_SET => $productAbstractEntity->getVirtualColumn(static::COL_TAX_SET),
            static::COL_VARIANT_COUNT => $productAbstractEntity->getSpyProducts()->count(),
            static::COL_STATUS => $this->getAbstractProductStatusLabel($productAbstractEntity),
            static::COL_PRODUCT_TYPES => $this->getTypeName($productAbstractEntity),
            static::COL_STORE_RELATION => $this->getStoreNames($productAbstractEntity->getIdProductAbstract()),
            static::COL_ACTIONS => implode(' ', $this->createActionColumn($productAbstractEntity)),
        ];

        return $this->executeItemDataExpanderPlugins($item);
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function executeItemDataExpanderPlugins(array $item): array
    {
        foreach ($this->productTableDataExpanderPlugins as $productTableDataExpanderPlugin) {
            $item = $productTableDataExpanderPlugin->expand($item);
        }

        return $item;
    }

    /**
     * @param int $idProductAbstract
     *
     * @return string
     */
    protected function getStoreNames($idProductAbstract)
    {
        /** @var \Orm\Zed\Product\Persistence\SpyProductAbstractStore[] $productAbstractStoreCollection */
        $productAbstractStoreCollection = $this->getProductAbstractStoreWithStore($idProductAbstract);

        $storeNames = [];
        foreach ($productAbstractStoreCollection as $productAbstractStoreEntity) {
            $storeNames[] = sprintf(
                '<span class="label label-info">%s</span>',
                $productAbstractStoreEntity->getSpyStore()->getName()
            );
        }

        return implode(' ', $storeNames);
    }

    /**
     * @param int $idProductAbstract
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstractStoreQuery
     */
    protected function getProductAbstractStoreWithStore($idProductAbstract)
    {
        return $this->productQueryQueryContainer->queryProductAbstractStoreWithStoresByFkProductAbstract($idProductAbstract);
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductAbstract $productAbstractEntity
     *
     * @return string
     */
    protected function getTypeName(SpyProductAbstract $productAbstractEntity)
    {
        if ($this->productTypeHelper->isProductBundleByProductAbstractEntity($productAbstractEntity)) {
            return 'Product Bundle';
        }

        if ($this->productTypeHelper->isGiftCardByProductAbstractEntity($productAbstractEntity)) {
            return 'Gift card';
        }

        return 'Product';
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductAbstract $item
     *
     * @return array
     */
    protected function createActionColumn(SpyProductAbstract $item)
    {
        $urls = [];

        $urls[] = $this->generateViewButton(
            Url::generate('/product-management/view', [
                EditController::PARAM_ID_PRODUCT_ABSTRACT => $item->getIdProductAbstract(),
            ]),
            'View'
        );

        $urls[] = $this->generateEditButton(
            Url::generate('/product-management/edit', [
                EditController::PARAM_ID_PRODUCT_ABSTRACT => $item->getIdProductAbstract(),
            ]),
            'Edit'
        );

        $urls[] = $this->generateEditButton(
            Url::generate('/product-attribute-gui/view/product-abstract', [
                EditController::PARAM_ID_PRODUCT_ABSTRACT => $item->getIdProductAbstract(),
            ]),
            'Manage Attributes'
        );

        return $urls;
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductAbstract $productAbstractEntity
     *
     * @return string
     */
    protected function getAbstractProductStatusLabel(SpyProductAbstract $productAbstractEntity)
    {
        $isActive = false;
        foreach ($productAbstractEntity->getSpyProducts() as $spyProductEntity) {
            if ($spyProductEntity->getIsActive()) {
                $isActive = true;
            }
        }

        return $this->getStatusLabel($isActive);
    }

    /**
     * @deprecated Use ProductTypeHelperInterface::isProductBundleByProductAbstractEntity() instead
     *
     * @param \Orm\Zed\Product\Persistence\SpyProductAbstract $productAbstractEntity
     *
     * @return string
     */
    protected function getIsBundleProductLable(SpyProductAbstract $productAbstractEntity)
    {
        foreach ($productAbstractEntity->getSpyProducts() as $spyProductEntity) {
            if ($spyProductEntity->getSpyProductBundlesRelatedByFkProduct()->count() > 0) {
                return $this->generateLabel('Yes', null);
            }
        }

        return $this->generateLabel('No', null);
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria $query
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    protected function expandPropelQuery(ModelCriteria $query): ModelCriteria
    {
        return $this->productManagementRepository->expandQuery($query);
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductAbstract $productAbstractEntity
     *
     * @return string|null
     */
    protected function resolveProductName(SpyProductAbstract $productAbstractEntity): ?string
    {
        return $productAbstractEntity->getVirtualColumn(static::COL_NAME) ?? $productAbstractEntity->getVirtualColumn(static::COL_NAME_FALLBACK);
    }
}
