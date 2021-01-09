<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\CrossBorder1\Twig\Extension;

use Eccube\Entity\ClassCategory;
use Eccube\Entity\ClassName;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Service\CartService;
use Plugin\CrossBorder1\Entity\LangContent;
use Twig\Extension\AbstractExtension;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartServiceExtension extends AbstractExtension
{
    /**
     * @var CartService
     */
    protected $cartService;

    protected $langContentRepository;

    protected $request;

    public function __construct(
        CartService $cartService,
        LangContentRepository $langContentRepository,
        RequestStack $request
    )
    {
        $this->cartService = $cartService;
        $this->langContentRepository = $langContentRepository;
        $this->request = $request;
    }

    public function getFunctions()
    {
        return [
            new \Twig_Function('get_all_carts', [$this, 'get_all_carts'], ['is_safe' => ['all']]),
        ];
    }

    public function get_all_carts()
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        $Carts = $this->cartService->getCarts();
        foreach($Carts as $Cart){
            foreach($Cart->getCartItems() as $CartItem){
                $ProductClass = $CartItem->getProductClass();
                if(!is_null($ProductClass) && $ProductClass instanceof ProductClass){
                    $Product = $ProductClass->getProduct();
                    if(!is_null($Product) && $Product instanceof Product){
                        /**@var LangContent $LangContent */
                        $LangContent = $this->langContentRepository->findOneBy(
                            [
                                'entity' => 'Eccube\\Entity\\Product',
                                'entity_id' => $Product->getId(),
                                'entity_field' => 'name',
                                'language' => $locale
                            ]
                        );
                        if(!is_null($LangContent)){
                            $Product->setName($LangContent->getContent());
                        }
                    }
                    $ClassCategory1 = $ProductClass->getClassCategory1();
                    if(!is_null($ClassCategory1) && $ClassCategory1 instanceof ClassCategory){
                        $LangContent = $this->langContentRepository->findOneBy(
                            [
                                'entity' => 'Eccube\\Entity\\ClassCategory',
                                'entity_id' => $ClassCategory1->getId(),
                                'entity_field' => 'name',
                                'language' => $locale
                            ]
                        );
                        if(!is_null($LangContent)){
                            $ClassCategory1->setName($LangContent->getContent());
                        }
                        $ClassName1 = $ClassCategory1->getClassName();
                        if(!is_null($ClassName1) && $ClassName1 instanceof ClassName){
                            $LangContent = $this->langContentRepository->findOneBy(
                                [
                                    'entity' => 'Eccube\\Entity\\ClassName',
                                    'entity_id' => $ClassName1->getId(),
                                    'entity_field' => 'name',
                                    'language' => $locale
                                ]
                            );
                            if(!is_null($LangContent)){
                                $ClassName1->setName($LangContent->getContent());
                            }
                        }
                    }

                    $ClassCategory2 = $ProductClass->getClassCategory2();
                    if(!is_null($ClassCategory2) && $ClassCategory2 instanceof ClassCategory){
                        $LangContent = $this->langContentRepository->findOneBy(
                            [
                                'entity' => 'Eccube\\Entity\\ClassCategory',
                                'entity_id' => $ClassCategory2->getId(),
                                'entity_field' => 'name',
                                'language' => $locale,
                            ]
                        );
                        if(!is_null($LangContent)){
                            $ClassCategory2->setName($LangContent->getContent());
                        }
                        $ClassName2 = $ClassCategory2->getClassName();
                        if(!is_null($ClassName2) && $ClassName2 instanceof ClassName){
                            $LangContent = $this->langContentRepository->findOneBy(
                                [
                                    'entity' => 'Eccube\\Entity\\ClassName',
                                    'entity_id' => $ClassName2->getId(),
                                    'entity_field' => 'name',
                                    'language' => $locale,
                                ]
                            );
                            if(!is_null($LangContent)){
                                $ClassName2->setName($LangContent->getContent());
                            }
                        }
                    }
                }
            }
        }
        return $Carts;
    }
}
