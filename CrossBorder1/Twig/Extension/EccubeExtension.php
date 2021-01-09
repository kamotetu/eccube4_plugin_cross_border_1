<?php

namespace Plugin\CrossBorder1\Twig\Extension;

use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Twig\Extension\AbstractExtension;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\TwigFunction;
use Plugin\CrossBorder1\Repository\LangContentRepository;

class EccubeExtension extends AbstractExtension
{
    private $request;

    private $langContentRepository;

    public function __construct(
        RequestStack $request,
        LangContentRepository $langContentRepository
    )
    {
        $this->request = $request;
        $this->langContentRepository = $langContentRepository;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getCurrentLocale', [$this, 'getCurrentLocale']),
            new Twigfunction('class_categories_as_json', [$this, 'getClassCategoriesAsJson']),
        ];
    }

    public function getCurrentLocale()
    {
        return $this->request->getCurrentRequest()->getLocale();
    }

    /**
     * Get the ClassCategories as JSON.
     *
     * @param Product $Product
     *
     * @return string
     */
    public function getClassCategoriesAsJson(Product $Product)
    {
        $Product->_calc();
        $class_categories = [
            '__unselected' => [
                '__unselected' => [
                    'name' => trans('common.select'),
                    'product_class_id' => '',
                ],
            ],
        ];
        foreach ($Product->getProductClasses() as $ProductClass) {
            /** @var ProductClass $ProductClass */
            if (!$ProductClass->isVisible()) {
                continue;
            }
            /* @var $ProductClass \Eccube\Entity\ProductClass */
            $ClassCategory1 = $ProductClass->getClassCategory1();
            $ClassCategory2 = $ProductClass->getClassCategory2();
            if ($ClassCategory2 && !$ClassCategory2->isVisible()) {
                continue;
            }

            if(!is_null($ClassCategory2)){
                $LangContent = $this->langContentRepository->findOneBy(
                    [
                        'entity' => get_class($ClassCategory2),
                        'entity_id' => $ClassCategory2->getId(),
                        'entity_field' => 'name',
                        'language' => $this->request->getCurrentRequest()->getLocale(),
                    ]
                );

                if(!is_null($LangContent)){
                    $ClassCategory2->setName($LangContent->getContent());
                }
            }

            $class_category_id1 = $ClassCategory1 ? (string) $ClassCategory1->getId() : '__unselected2';
            $class_category_id2 = $ClassCategory2 ? (string) $ClassCategory2->getId() : '';
            $class_category_name2 = $ClassCategory2 ? $ClassCategory2->getName().($ProductClass->getStockFind() ? '' : trans('front.product.out_of_stock_label')) : '';

            $class_categories[$class_category_id1]['#'] = [
                'classcategory_id2' => '',
                'name' => trans('common.select'),
                'product_class_id' => '',
            ];
            $class_categories[$class_category_id1]['#'.$class_category_id2] = [
                'classcategory_id2' => $class_category_id2,
                'name' => $class_category_name2,
                'stock_find' => $ProductClass->getStockFind(),
                'price01' => $ProductClass->getPrice01() === null ? '' : number_format($ProductClass->getPrice01()),
                'price02' => number_format($ProductClass->getPrice02()),
                'price01_inc_tax' => $ProductClass->getPrice01() === null ? '' : number_format($ProductClass->getPrice01IncTax()),
                'price02_inc_tax' => number_format($ProductClass->getPrice02IncTax()),
                'product_class_id' => (string) $ProductClass->getId(),
                'product_code' => $ProductClass->getCode() === null ? '' : $ProductClass->getCode(),
                'sale_type' => (string) $ProductClass->getSaleType()->getId(),
            ];
        }

        return json_encode($class_categories);
    }
}
