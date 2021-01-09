<?php

namespace Plugin\CrossBorder1\Service\Event\EventListener;

use Eccube\Entity\BaseInfo;
use Eccube\Entity\CustomerFavoriteProduct;
use Eccube\Entity\Delivery;
use Eccube\Entity\Master\CustomerOrderStatus;
use Eccube\Entity\Master\Pref;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Eccube\Entity\Payment;
use Eccube\Entity\Shipping;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Eccube\Entity\CartItem;
use Eccube\Entity\Product;
use Plugin\CrossBorder1\Entity\LangContent;
use Eccube\Entity\ClassCategory;

class Functions
{
    private $langContentRepository;

    public function __construct(
        LangContentRepository $langContentRepository
    )
    {
        $this->langContentRepository = $langContentRepository;
    }

    public function setChangeLangProduct(Product $item, $locale)
    {
        $LangContents = $this->langContentRepository->findBy(
            [
                'entity' => 'Eccube\\Entity\\Product',
                'entity_id' => $item->getId(),
                'language' => $locale,
            ]
        );

        if(!empty($LangContents)){
            foreach($LangContents as $LangContent){
                $entity_field = $LangContent->getEntityField();
                switch($entity_field){
                    case 'name':
                        $item->setName($LangContent->getContent());
                        break;
                    case 'description_detail':
                        $item->setDescriptionDetail($LangContent->getContent());
                        break;
                    case 'description_list':
                        $item->setDescriptionList($LangContent->getContent());
                        break;
                    case 'free_area':
                        $item->setFreeArea($LangContent->getContent());
                        break;
                }
            }
        }

        $tags = $item->getTags();
        if(!empty($tags)){
            foreach($tags as $tag){
                $LangContent = $this->langContentRepository->findOneBy(
                    [
                        'entity' => 'Eccube\\Entity\\Tag',
                        'entity_id' => $tag->getId(),
                        'entity_field' => 'name',
                        'language' => $locale
                    ]
                );
                if(!is_null($LangContent)){
                    $tag->setName($LangContent->getContent());
                }
            }
        }
        return $item;
    }

    public function setChangeLangCustomerFavoriteProduct(CustomerFavoriteProduct $item, $locale)
    {
        $Product = $item->getProduct();
        $LangContents = $this->langContentRepository->findBy(
            [
                'entity' => 'Eccube\\Entity\\Product',
                'entity_id' => $Product->getId(),
                'language' => $locale,
            ]
        );

        if(!empty($LangContents)){
            foreach($LangContents as $LangContent){
                $entity_field = $LangContent->getEntityField();
                switch($entity_field){
                    case 'name':
                        $Product->setName($LangContent->getContent());
                        break;
                    case 'description_detail':
                        $Product->setDescriptionDetail($LangContent->getContent());
                        break;
                    case 'description_list':
                        $Product->setDescriptionList($LangContent->getContent());
                        break;
                    case 'free_area':
                        $Product->setFreeArea($LangContent->getContent());
                        break;
                }
            }
        }
        $item->setProduct($Product);
        return $item;
    }

    public function setChangeLangCartItem(CartItem $item, $locale)
    {
        $ProductClass = $item->getProductClass();
        $Product = $ProductClass->getProduct();
        $trans_item = $this->setChangeLangProduct($Product, $locale);
        $ProductClass->setProduct($trans_item);
        $item->setProductClass($ProductClass);
        $ClassCategory1 = $ProductClass->getClassCategory1();
        if(!is_null($ClassCategory1) && $ClassCategory1 instanceof ClassCategory){
            $this->setChangeLangClassCategory($ClassCategory1, $locale);
        }
        $ClassCategory2 = $ProductClass->getClassCategory2();
        if(!is_null($ClassCategory2) && $ClassCategory2 instanceof ClassCategory){
            $this->setChangeLangClassCategory($ClassCategory2, $locale);
        }
        return $item;
    }

    public function setChangeLangClassCategory(ClassCategory $ClassCategory, $locale)
    {
        /** @var LangContent $LangContent */
        $LangContent = $this->langContentRepository->findOneBy(
            [
                'entity' => 'Eccube\\Entity\\ClassCategory',
                'entity_id' => $ClassCategory->getId(),
                'entity_field' => 'name',
                'language' => $locale
            ]
        );
        if(!is_null($LangContent)){
            $ClassCategory->setName($LangContent->getContent());
        }
        $ClassName = $ClassCategory->getClassName();
        $LangContent = $this->langContentRepository->findOneBy(
            [
                'entity' => 'Eccube\\Entity\\ClassName',
                'entity_id' => $ClassName->getId(),
                'entity_field' => 'name',
                'language' => $locale
            ]
        );
        if(!is_null($LangContent)){
            $ClassName->setName($LangContent->getContent());
        }
    }

    public function setChangeLangBaseInfo(BaseInfo $BaseInfo, $locale)
    {
        $LangContents = $this->langContentRepository->findBy(
            [
                'entity' => get_class($BaseInfo),
                'entity_id' => $BaseInfo->getId(),
                'language' => $locale
            ]
        );
        if(!empty($LangContents)){
            /** @var LangContent $LangContent*/
            foreach($LangContents as $LangContent){
                $entity_field = $LangContent->getEntityField();
                switch($entity_field){
                    case 'company_name':
                        $BaseInfo->setCompanyName($LangContent->getContent());
                        break;
                    case 'shop_name':
                        $BaseInfo->setShopName($LangContent->getContent());
                        break;
                    case 'good_traded':
                        $BaseInfo->setGoodTraded($LangContent->getContent());
                        break;
                    case 'message':
                        $BaseInfo->setMessage($LangContent->getContent());
                        break;
                }
            }
        }
        return $BaseInfo;
    }


    public function setChangeLangOrderItem(OrderItem $OrderItem, $locale)
    {
        /**@var Product $Product*/
        $Product = $OrderItem->getProduct();
        if(!is_null($Product) && !empty($Product->getId())){

            $LangContent = $this->langContentRepository->findOneBy(
                [
                    'entity' => 'Eccube\\Entity\\Product',
                    'entity_id' => $Product->getId(),
                    'entity_field' => 'name',
                    'language' => $locale,
                ]
            );
            if(!is_null($LangContent)){
                $OrderItem->setProductName($LangContent->getContent());
            }
            $ProductClass = $OrderItem->getProductClass();
            $ClassCategory1 = $ProductClass->getClassCategory1();
            if(!is_null($ClassCategory1) && $ClassCategory1 instanceof ClassCategory){
                $this->setChangeLangClassCategory($ClassCategory1, $locale);
            }
            $ClassCategory2 = $ProductClass->getClassCategory2();
            if(!is_null($ClassCategory2) && $ClassCategory2 instanceof ClassCategory){
                $this->setChangeLangClassCategory($ClassCategory2, $locale);
            }
        }
    }

    public function setChangeLangPaymentMethod(Order $Order, $locale)
    {
        $Payment = $Order->getPayment();
        if(!empty($Payment)){
            /** @var LangContent $LangContent */
            $LangContent = $this->langContentRepository->findOneBy(
                [
                    'entity' => 'Eccube\\Entity\\Payment',
                    'entity_id' => $Payment->getId(),
                    'entity_field' => 'payment_method',
                    'language' => $locale,
                ]
            );
            if(!is_null($LangContent)){
                $Payment->setMethod($LangContent->getContent());
                $Order->setPayment($Payment);
            }
        }
        return $Order;
    }

    public function setChangeLangShippingDeliveryTime(Shipping $Shipping, $locale)
    {
        /**@var LangContent $LangContent */
        $LangContent = $this->langContentRepository->findOneBy(
            [
                'entity' => 'Eccube\\Entity\\DeliveryTime',
                'entity_id' => $Shipping->getTimeId(),
                'entity_field' => 'delivery_time',
                'language' => $locale
            ]
        );
        if(!is_null($LangContent)){
            $Shipping->setShippingDeliveryTime($LangContent->getContent());
        }
    }

    public function setChangeLangOrderMasterData($MasterData, $locale, $entity)
    {
        /** @var LangContent $LangContent */
        $LangContent = $this->langContentRepository->findOneBy(
            [
                'entity' => $entity,
                'entity_id' => $MasterData->getId(),
                'entity_field' => 'name',
                'language' => $locale
            ]
        );
        if(!is_null($LangContent)){
            $MasterData->setName($LangContent->getContent());
        }
    }

    public function setChangeLangOrder(Order $Order, $locale)
    {
        $CustomerOrderStatus = $Order->getCustomerOrderStatus();
        if(!is_null($CustomerOrderStatus) && $CustomerOrderStatus instanceof CustomerOrderStatus){
            $entity = 'Eccube\\Entity\\Master\\CustomerOrderStatus';
            $this->setChangeLangOrderMasterData($CustomerOrderStatus, $locale, $entity);
        }

        $Pref = $Order->getPref();
        if(!is_null($Pref) && $Pref instanceof Pref){
            $entity = 'Eccube\\Entity\\Master\\Pref';
            $this->setChangeLangOrderMasterData($Pref, $locale, $entity);
        }

        $Payment = $Order->getPayment();
        if(!is_null($Payment) && $Payment instanceof Payment){
            $LangContent = $this->langContentRepository->findOneBy(
                [
                    'entity' => 'Eccube\\Entity\\Payment',
                    'entity_id' => $Payment->getId(),
                    'entity_field' => 'payment_method',
                    'language' => $locale,
                ]
            );
            if(!is_null($LangContent)){
                $Order->setPaymentMethod($LangContent->getContent());
            }
        }

        $OrderItems = $Order->getOrderItems();
        $this->setChangeLangOrderItems($OrderItems, $locale);
        $Shippings = $Order->getShippings();
        $this->setChangeLangShippings($Shippings, $locale);
    }

    public function setDefaultPaymentMethod(Order $Order)
    {
        $Payment = $Order->getPayment();
        $Order->setPaymentMethod($Payment->getMethod());
    }

    public function setChangeLangOrderItems($OrderItems, $locale)
    {
        foreach($OrderItems as $OrderItem){
            if($OrderItem instanceof OrderItem){
                $this->setChangeLangOrderItem($OrderItem, $locale);
            }
        }
    }

    public function setChangeLangShippings($Shippings, $locale)
    {
        foreach($Shippings as $Shipping){
            if($Shipping instanceof Shipping){
                $this->setChangeLangShippingDeliveryTime($Shipping, $locale);
                $this->setChangeLangShippingDeliveryName($Shipping, $locale);
                $this->setChangeLangShippingPref($Shipping, $locale);
            }
        }
    }

    public function setChangeLangShippingDeliveryName(Shipping $Shipping, $locale)
    {
        $Delivery = $Shipping->getDelivery();
        if(!is_null($Delivery) && $Delivery instanceof Delivery){
            /**@var LangContent $LangContent */
            $LangContent = $this->langContentRepository->findOneBy(
                [
                    'entity' => 'Eccube\\Entity\\Delivery',
                    'entity_id' => $Delivery->getId(),
                    'entity_field' => 'name',
                    'language' => $locale,
                ]
            );
            if(!is_null($LangContent)){
                $Shipping->setShippingDeliveryName($LangContent->getContent());
                $Shipping->getDelivery()->setName($LangContent->getContent());
            }
        }
    }

    public function setChangeLangShippingPref(Shipping $Shipping, $locale)
    {
        $Pref = $Shipping->getPref();
        if(is_null($Pref) && $Pref instanceof Pref){
            /**@var LangContent $LangContent */
            $LangContent = $this->langContentRepository->findOneBy(
                [
                    'entity' => 'Eccube\\Entity\\Master\\Pref',
                    'entity_id' => $Pref->getId(),
                    'entity_field' => 'name',
                    'language' => $locale
                ]
            );
            if(!is_null($LangContent)){
                $Pref->setName($LangContent->getContent());
            }
        }
    }
}
