<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CategoryGui\Communication\Controller;

use Generated\Shared\Transfer\CategoryCriteriaTransfer;
use Generated\Shared\Transfer\CategoryNodeUrlCriteriaTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\NodeCollectionTransfer;
use Spryker\Zed\CategoryGui\Communication\Form\DeleteType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\CategoryGui\Persistence\CategoryGuiRepositoryInterface getRepository()
 * @method \Spryker\Zed\CategoryGui\Communication\CategoryGuiCommunicationFactory getFactory()
 */
class DeleteController extends CategoryAbstractController
{
    protected const REQUEST_PARAM_ID_CATEGORY = 'id-category';

    /**
     * @uses \Spryker\Zed\CategoryGui\Communication\Controller\ListController::indexAction()
     */
    protected const ROUTE_CATEGORY_LIST = '/category-gui/list';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request)
    {
        $idCategory = $this->castId($request->get(static::REQUEST_PARAM_ID_CATEGORY));
        $categoryTransfer = $this->findCategory($idCategory);

        if (!$categoryTransfer) {
            return $this->redirectResponse(static::ROUTE_CATEGORY_LIST);
        }

        $form = $this->getFactory()->createCategoryDeleteForm($idCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categoryResponseTransfer = $this->getFactory()
                ->createCategoryDeleteFormHandler()
                ->deleteCategory($form->getData()[DeleteType::FIELD_FK_NODE_CATEGORY]);

            if ($categoryResponseTransfer->getIsSuccessful()) {
                $this->addSuccessMessages($categoryResponseTransfer->getMessages());

                return $this->redirectResponse(static::ROUTE_CATEGORY_LIST);
            }

            $this->addErrorMessages($categoryResponseTransfer->getMessages());
        }

        return $this->viewResponse([
            'form' => $form->createView(),
            'category' => $categoryTransfer,
            'urls' => $this->getUrls($categoryTransfer),
            'relations' => $this->getRelations($categoryTransfer),
            'parentCategory' => $this->findParentCategory($categoryTransfer),
            'childNodes' => $this->getCategoryChildNodeCollection($categoryTransfer),
        ]);
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer|null
     */
    protected function findParentCategory(CategoryTransfer $categoryTransfer): ?CategoryTransfer
    {
        if (!$categoryTransfer->getParentCategoryNode()) {
            return null;
        }

        return $this->findCategory($categoryTransfer->getParentCategoryNodeOrFail()->getFkCategoryOrFail());
    }

    /**
     * @param int $idCategory
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer|null
     */
    protected function findCategory(int $idCategory): ?CategoryTransfer
    {
        $localeTransfer = $this->getCurrentLocale();

        $categoryCriteriaTransfer = (new CategoryCriteriaTransfer())
            ->setIdCategory($idCategory)
            ->setLocaleName($localeTransfer->getLocaleName())
            ->setWithChildrenRecursively(true);

        $categoryTransfer = $this
            ->getFactory()
            ->getCategoryFacade()
            ->findCategory($categoryCriteriaTransfer);

        if (!$categoryTransfer) {
            return null;
        }

        return $categoryTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return \Generated\Shared\Transfer\UrlTransfer[]
     */
    protected function getUrls(CategoryTransfer $categoryTransfer): array
    {
        $categoryNodeIds = [];

        foreach ($categoryTransfer->getNodeCollectionOrFail()->getNodes() as $nodeTransfer) {
            $categoryNodeIds[] = $nodeTransfer->getIdCategoryNodeOrFail();
        }

        $categoryNodeUrlCriteriaTransfer = (new CategoryNodeUrlCriteriaTransfer())
            ->setCategoryNodeIds($categoryNodeIds);

        return $this->getFactory()->getCategoryFacade()->getCategoryNodeUrls($categoryNodeUrlCriteriaTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return array
     */
    protected function getRelations(CategoryTransfer $categoryTransfer): array
    {
        $relations = [];
        $localeTransfer = $this->getCurrentLocale();

        /** @var \Spryker\Zed\CategoryGuiExtension\Dependency\Plugin\CategoryRelationReadPluginInterface[] $categoryRelationReadPlugins */
        $categoryRelationReadPlugins = $this->getFactory()->getCategoryRelationReadPlugins();

        foreach ($categoryRelationReadPlugins as $categoryRelationReadPlugin) {
            $relations[] = [
                'name' => $categoryRelationReadPlugin->getRelationName(),
                'list' => $categoryRelationReadPlugin->getRelations($categoryTransfer, $localeTransfer),
            ];
        }

        return $relations;
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return \Generated\Shared\Transfer\NodeCollectionTransfer
     */
    protected function getCategoryChildNodeCollection(CategoryTransfer $categoryTransfer): NodeCollectionTransfer
    {
        $categoryNodeCollectionTransfer = $categoryTransfer->getNodeCollection();
        if (!$categoryNodeCollectionTransfer || $categoryNodeCollectionTransfer->getNodes()->count() === 0) {
            return new NodeCollectionTransfer();
        }

        return $categoryNodeCollectionTransfer->getNodes()->offsetGet(0)->getChildrenNodes() ?? new NodeCollectionTransfer();
    }
}
