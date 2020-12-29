<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Persistence;

use Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Orm\Zed\Category\Persistence\SpyCategory;
use Orm\Zed\Category\Persistence\SpyCategoryAttribute;
use Orm\Zed\Category\Persistence\SpyCategoryClosureTable;
use Orm\Zed\Category\Persistence\SpyCategoryNode;
use Orm\Zed\Category\Persistence\SpyCategoryStore;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;

/**
 * @method \Spryker\Zed\Category\Persistence\CategoryPersistenceFactory getFactory()
 */
class CategoryEntityManager extends AbstractEntityManager implements CategoryEntityManagerInterface
{
    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer
     */
    public function createCategory(CategoryTransfer $categoryTransfer): CategoryTransfer
    {
        $categoryMapper = $this->getFactory()->createCategoryMapper();

        $categoryEntity = $categoryMapper->mapCategoryTransferToCategoryEntity($categoryTransfer, new SpyCategory());
        $categoryEntity->save();

        return $categoryMapper->mapCategory($categoryEntity, $categoryTransfer);
    }

    /**
     * @param int $idCategory
     * @param \Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer $categoryLocalizedAttributesTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer
     */
    public function createCategoryAttribute(
        int $idCategory,
        CategoryLocalizedAttributesTransfer $categoryLocalizedAttributesTransfer
    ): CategoryLocalizedAttributesTransfer {
        $categoryMapper = $this->getFactory()->createCategoryMapper();

        $categoryAttributeEntity = $categoryMapper->mapCategoryLocalizedAttributeTransferToCategoryAttributeEntity(
            $categoryLocalizedAttributesTransfer,
            new SpyCategoryAttribute()
        );
        $categoryAttributeEntity->setFkCategory($idCategory);
        $categoryAttributeEntity->save();

        return $categoryMapper->mapCategoryAttributeEntityToCategoryLocalizedAttributeTransfer(
            $categoryAttributeEntity,
            $categoryLocalizedAttributesTransfer
        );
    }

    /**
     * @param \Generated\Shared\Transfer\NodeTransfer $nodeTransfer
     *
     * @return \Generated\Shared\Transfer\NodeTransfer
     */
    public function createCategoryNode(NodeTransfer $nodeTransfer): NodeTransfer
    {
        $categoryMapper = $this->getFactory()->createCategoryMapper();

        $categoryNodeEntity = $categoryMapper->mapNodeTransferToCategoryNodeEntity($nodeTransfer, new SpyCategoryNode());
        $categoryNodeEntity->save();

        return $categoryMapper->mapCategoryNode($categoryNodeEntity, $nodeTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     * @param \Generated\Shared\Transfer\NodeTransfer $extraParentNodeTransfer
     *
     * @return \Generated\Shared\Transfer\NodeTransfer
     */
    public function saveCategoryExtraParentNode(CategoryTransfer $categoryTransfer, NodeTransfer $extraParentNodeTransfer): NodeTransfer
    {
        $categoryNodeEntity = $this->getFactory()
            ->createCategoryNodeQuery()
            ->filterByFkCategory($categoryTransfer->getIdCategory())
            ->filterByIsMain(false)
            ->filterByFkParentCategoryNode($extraParentNodeTransfer->getIdCategoryNodeOrFail())
            ->findOneOrCreate();

        $categoryNodeEntity->save();

        return $this->getFactory()
            ->createCategoryMapper()
            ->mapCategoryNode($categoryNodeEntity, new NodeTransfer());
    }

    /**
     * @param int $idCategory
     * @param array $storeIds
     *
     * @return void
     */
    public function createCategoryStoreRelationForStores(int $idCategory, array $storeIds): void
    {
        foreach ($storeIds as $idStore) {
            (new SpyCategoryStore())
                ->setFkCategory($idCategory)
                ->setFkStore($idStore)
                ->save();
        }
    }

    /**
     * @param \Generated\Shared\Transfer\NodeTransfer $nodeTransfer
     *
     * @return void
     */
    public function createCategoryClosureTableRootNode(NodeTransfer $nodeTransfer): void
    {
        $idCategoryNode = $nodeTransfer->getIdCategoryNode();

        $this->createCategoryClosureTable($idCategoryNode, $idCategoryNode);
    }

    /**
     * @param \Generated\Shared\Transfer\NodeTransfer $nodeTransfer
     *
     * @return void
     */
    public function createCategoryClosureTableNodes(NodeTransfer $nodeTransfer): void
    {
        $idCategoryNode = $nodeTransfer->getIdCategoryNodeOrFail();
        $idParentCategoryNode = $nodeTransfer->getFkParentCategoryNodeOrFail();

        $categoryClosureTableEntities = $this->getFactory()
            ->createCategoryClosureTableQuery()
            ->filterByFkCategoryNodeDescendant($idParentCategoryNode)
            ->find();

        foreach ($categoryClosureTableEntities as $categoryClosureTableEntity) {
            $this->createCategoryClosureTable(
                $categoryClosureTableEntity->getFkCategoryNode(),
                $idCategoryNode,
                $categoryClosureTableEntity->getDepth() + 1
            );
        }

        $this->createCategoryClosureTable($idCategoryNode, $idCategoryNode);
    }

    /**
     * @param int $idCategoryNode
     * @param int $idCategoryNodeDescendant
     * @param int $depth
     *
     * @return void
     */
    protected function createCategoryClosureTable(int $idCategoryNode, int $idCategoryNodeDescendant, int $depth = 0): void
    {
        $pathEntity = (new SpyCategoryClosureTable())
            ->setFkCategoryNode($idCategoryNode)
            ->setFkCategoryNodeDescendant($idCategoryNodeDescendant)
            ->setDepth($depth);

        $pathEntity->save();
    }
}
