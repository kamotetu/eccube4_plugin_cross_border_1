<?php

/*
 * メール送信時に諸々データが上書きされてしまうため
 * EventListenerと別にFunctionsを定義
 */

namespace Plugin\CrossBorder1\Service\Event\MailEventListener;

use Eccube\Entity\BaseInfo;
use Eccube\Entity\ClassCategory;
use Eccube\Entity\Delivery;
use Eccube\Entity\DeliveryTime;
use Eccube\Entity\MailTemplate;
use Eccube\Entity\Master\CustomerOrderStatus;
use Eccube\Entity\Master\Pref;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Eccube\Entity\Payment;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Entity\Shipping;
use Plugin\CrossBorder1\Entity\LangContent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Eccube\Repository\DeliveryTimeRepository;

class Functions
{
    private $session;

    private $langContentRepository;

    private $deliveryTimeRepository;

    public function __construct(
        SessionInterface $session,
        LangContentRepository $langContentRepository,
        DeliveryTimeRepository $deliveryTimeRepository
    )
    {
        $this->session = $session;
        $this->langContentRepository = $langContentRepository;
        $this->deliveryTimeRepository = $deliveryTimeRepository;
    }

    public function setChangeLangOrder(Order $Order, $locale)
    {
        $CustomerOrderStatus = $Order->getCustomerOrderStatus();
        if(!is_null($CustomerOrderStatus) && $CustomerOrderStatus instanceof CustomerOrderStatus){
            $entity = 'Eccube\\Entity\\Master\\CustomerOrderStatus';
            $this->setChangeLangMasterData($CustomerOrderStatus, $locale, $entity);
        }

        $Pref = $Order->getPref();
        if(!is_null($Pref) && $Pref instanceof Pref){
            $entity = 'Eccube\\Entity\\Master\\Pref';
            $this->setChangeLangMasterData($Pref, $locale, $entity);
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

    public function setSessionOrderPref(Order $Order)
    {
        $this->setSessionDefaultData('order_pref_name', $Order->getPref()->getName());
    }

    public function setSessionOrderCustomerOrderStatus(Order $Order)
    {
        $this->setSessionDefaultData('order_customer_order_status', $Order->getCustomerOrderStatus()->getName());
    }

    //マスタデータの値を変更
    public function setChangeLangMasterData($MasterData, $locale, $entity)
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

    //OrderItemsの値を変更
    public function setChangeLangOrderItems($OrderItems, $locale)
    {
        foreach($OrderItems as $OrderItem){
            if($OrderItem instanceof OrderItem){
                $this->setChangeLangOrderItem($OrderItem, $locale);
            }
        }
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
                    'language' => $locale
                ]
            );
            if(!is_null($LangContent)){
                $OrderItem->setProductName($LangContent->getContent());
            }
            $ProductClass = $OrderItem->getProductClass();
            $ClassCategory1 = $ProductClass->getClassCategory1();
            if(!is_null($ClassCategory1) && $ClassCategory1 instanceof ClassCategory){
                $LangContent = $this->langContentRepository->findOneBy(
                    [
                        'entity' => 'Eccube\\Entity\\ClassCategory',
                        'entity_id' => $ClassCategory1->getId(),
                        'entity_field' => 'name',
                        'language' => $locale,
                    ]
                );
                if(!is_null($LangContent)){
                    $OrderItem->setClassCategoryName1($LangContent->getContent());
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
                    $OrderItem->setClassCategoryName2($LangContent->getContent());
                }
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

    //BaseInfoの元の情報をセッションに保存
    public function setSessionDefaultBaseInfo(BaseInfo $BaseInfo)
    {
        $this->setSessionDefaultData('base_info_company_name', $BaseInfo->getCompanyName());
        $this->setSessionDefaultData('base_info_shop_name', $BaseInfo->getShopName());
        $this->setSessionDefaultData('base_info_good_traded', $BaseInfo->getGoodTraded());
        $this->setSessionDefaultData('base_info_message', $BaseInfo->getMessage());
    }

    //セッションがセットする値を持っている場合上書きしない
    public function setSessionDefaultData($key, $value)
    {
        if(
            !$this->session->has($key) ||
            $this->session->get($key) === null ||
            $this->session->get($key) === ''
        ){
            $this->session->set($key, $value);
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
//        return $BaseInfo;
    }

    public function getChangeLangMailSubject(MailTemplate $MailTemplate, $locale)
    {
        /**@var LangContent $LangContent */
        $LangContent = $this->langContentRepository->findOneBy(
            [
                'entity' => 'Eccube\\Entity\\MailTemplate',
                'entity_id' => $MailTemplate->getId(),
                'entity_field' => 'mail_subject',
                'language' => $locale
            ]
        );
        if(!is_null($LangContent)){
            return $LangContent->getContent();
        }else{
            return $MailTemplate->getMailSubject();
        }
    }

    public function setDefaultOrder(Order $Order)
    {
        //都道府県マスタを元の値にセット
        $Order->getPref()->setName($this->session->get('order_pref_name'));
        $this->setDefaultPaymentMethod($Order);
        $this->setDefaultOrderItems($Order);
        $this->setDefaultShippings($Order);
    }

    //BaseInfoの情報を元に戻す
    public function setDefaultBaseInfo(BaseInfo $BaseInfo)
    {
        $BaseInfo->setCompanyName($this->session->get('base_info_company_name'));
        $BaseInfo->setShopName($this->session->get('base_info_shop_name'));
        $BaseInfo->setGoodTraded($this->session->get('base_info_good_traded'));
        $BaseInfo->setMessage($this->session->get('base_info_message'));
    }

    public function setDefaultPaymentMethod(Order $Order)
    {
        $Payment = $Order->getPayment();
        $Order->setPaymentMethod($Payment->getMethod());
    }

    public function setDefaultOrderItems(Order $Order)
    {
        $OrderItems = $Order->getOrderItems();
        foreach($OrderItems as $OrderItem){
            if($OrderItem->isProduct()){
                //ProductNameをリセット
                $OrderItem->setProductName($OrderItem->getProduct()->getName());
                $ProductClass = $OrderItem->getProductClass();
                if(!is_null($ProductClass) && $ProductClass instanceof ProductClass){
                    //ClassCategoryのname,ClassNameのnameをリセット
                    $ClassCategory1 = $ProductClass->getClassCategory1();
                    if(!is_null($ClassCategory1) && $ClassCategory1 instanceof ClassCategory){
                        $OrderItem->setClassCategoryName1($ClassCategory1->getName());
                    }
                    $ClassCategory2 = $ProductClass->getClassCategory2();
                    if(!is_null($ClassCategory2) && $ClassCategory2 instanceof ClassCategory){
                        $OrderItem->setClassCategoryName2($ClassCategory2->getName());
                    }
                }
            }
        }
    }

    public function setDefaultShippings(Order $Order)
    {
        $Shippings = $Order->getShippings();
        foreach($Shippings as $Shipping){
            $Shipping->setShippingDeliveryName($Shipping->getDelivery()->getName());
            $delivery_time_id = $Shipping->getTimeId();
            if(!is_null($delivery_time_id)){
                $DeliveryTime = $this->deliveryTimeRepository->find($delivery_time_id);
                if(!is_null($DeliveryTime) && $DeliveryTime instanceof DeliveryTime){
                    $Shipping->setShippingDeliveryTime($DeliveryTime->getDeliveryTime());
                }
            }
        }
    }

    public function resetMailSession()
    {
        $this->session->set('base_info_company_name', '');
        $this->session->set('base_info_shop_name', '');
        $this->session->set('base_info_good_traded', '');
        $this->session->set('base_info_message', '');
        $this->session->set('order_pref_name', '');
        $this->session->set('order_customer_order_status', '');
        $this->session->set('mail_type', '');
    }

    public function setSessionMailType($value)
    {
        $this->setSessionDefaultData('mail_type', $value);
    }
}
