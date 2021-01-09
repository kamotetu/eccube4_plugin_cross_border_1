<?php

namespace Plugin\CrossBorder1\Event;

use Eccube\Entity\MailTemplate;
use Eccube\Entity\Order;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Plugin\CrossBorder1\Entity\LangContent;
use Plugin\CrossBorder1\Form\Type\Common\ChangeLangType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Eccube\Common\EccubeConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Plugin\CrossBorder1\Service\Event\EventListener\Functions;
use Plugin\CrossBorder1\Repository\ConfigRepository;
use Twig\Environment;
use Eccube\Repository\BaseInfoRepository;
use Swift_Message;
use Eccube\Repository\MailTemplateRepository;


class EventListener implements EventSubscriberInterface
{
    private $eccubeConfig;

    private $container;

    private $authorizationChecker;

    private $formFactory;

    private $twig;

    private $request;

    private $functions;

    private $configRepository;

    private $twig_env;

    private $BaseInfo;

    private $mailTemplateRepository;

    public function __construct(
        EccubeConfig $eccubeConfig,
        ContainerInterface $container,
        AuthorizationCheckerInterface $authorizationChecker,
        FormFactoryInterface $formFactory,
        \Twig_Environment $twig,
        RequestStack $request,
        Functions $functions,
        ConfigRepository $configRepository,
        Environment $twig_env,
        BaseInfoRepository $baseInfoRepository,
        MailTemplateRepository $mailTemplateRepository
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->container = $container;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->request = $request;
        $this->functions = $functions;
        $this->configRepository = $configRepository;
        $this->twig_env = $twig_env;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->mailTemplateRepository = $mailTemplateRepository;

    }

    public static function getSubscribedEvents()
    {
        return
            [
                KernelEvents::REQUEST => 'onKernelRequest',
                'default_frame.twig' => 'onAddLangForm',
                'Product/list.twig' => 'onProductListChangeLang',
                'Product/detail.twig' => 'onProductDetailChangeLang',
                'Cart/index.twig' => 'onCartIndexChangeLang',
                '@admin/default_frame.twig' => 'onAddLangForm',
                '@admin/Content/news_edit.twig' => 'onNewsEditInsertTwig',
                'Shopping/index.twig' => 'onShoppingIndexChangeLang',
                'Shopping/confirm.twig' => 'onShoppingConfirmChangeLang',
                'Mypage/favorite.twig' => 'onMypageFavoriteChangeLang',
                'Mypage/index.twig' => 'onMypageIndexChangeLang',
                'Mypage/history.twig' => 'onMypageHistoryChangeLang',
            ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        //選択肢が一つだけの場合、その言語を標準にする
        $admin_route = $this->eccubeConfig->get('eccube_admin_route');
        if(strpos($event->getRequest()->getUri(), $admin_route) === false) {
            $Configs = $this->configRepository->findBy(
                [
                    'visible' => 1,
                ]
            );
        }else{
            $Configs = $this->configRepository->findAll();
        }
        if (!empty($Configs) && count($Configs) === 1) {
            $event->getRequest()->setDefaultLocale($Configs[0]->getBackendName());
            return;
        }
        $request = $event->getRequest();
        $session = $event->getRequest()->getSession();

        if(!empty($request->get('lang'))){
            $lang = $request->get('lang');
            $session->set('lang', $lang);
        }elseif(!empty($session->get('lang'))){
            $lang = $session->get('lang');
        }else{
            $lang = $this->eccubeConfig->get('locale');
        }
        $admin_route = $this->eccubeConfig->get('eccube_admin_route');

        //管理画面はenvの言語で表示
        if(strpos($request->getUri(), $admin_route) === false){
            $translator = $this->container->get('translator');
            $translator->setLocale($lang);
        }
        $request = $event->getRequest();
        $request->setDefaultLocale($lang);
//        $request->setLocale($lang);
    }

    public function onAddLangForm(TemplateEvent $event)
    {
        $builder = $this->formFactory->createNamedBuilder(
            '',
            ChangeLangType::class,
            []
        );
        $event->setParameter('lang_form', $builder->getForm()->createView());
        //管理画面
        if($event->getView() === '@admin/default_frame.twig'){
            $this->onDefaultFlameAdminInsertTwig($event);
        //フロント
        }elseif($event->getView() === 'default_frame.twig'){
            $this->onHeaderInsertTwig($event);
            $this->onDefaultFrameChangeLang($event);
        }
    }

    public function onHeaderInsertTwig(TemplateEvent $event)
    {
        $twig = '@CrossBorder1/default/Block/header_insert.twig';
        $event->addSnippet($twig);
    }

    public function onMasterdataInsertTwig(TemplateEvent $event)
    {
        $twig = '@CrossBorder1/admin/Setting/System/masterdata_insert.twig';
        $event->addSnippet($twig);
    }

    public function onDefaultFlameAdminInsertTwig(TemplateEvent $event)
    {
        $twig = '@CrossBorder1/admin/lang_form_insert.twig';
        $event->addSnippet($twig);
        $this->handleFunction($event);
    }

    public function onProductInsertTwig(TemplateEvent $event)
    {
        $twig = '@CrossBorder1/admin/Product/product_lang_form_insert.twig';
        $event->addSnippet($twig);
    }

    public function onClassNameInsertTwig(TemplateEvent $event)
    {
        $twig = '@CrossBorder1/admin/Product/class_name_lang_form_insert.twig';
        $event->addSnippet($twig);
    }

    public function onCategoryInsertTwig(TemplateEvent $event)
    {
        $twig = "@CrossBorder1/admin/Product/category_lang_form_insert.twig";
        $event->addSnippet($twig);
    }

    public function onTagInsertTwig(TemplateEvent $event)
    {
        $twig = '@CrossBorder1/admin/Product/tag_insert.twig';
        $event->addSnippet($twig);
    }

    public function onDefaultFrameChangeLang(TemplateEvent $event)
    {
        if($this->eccubeConfig->get('locale') !== $this->request->getCurrentRequest()->getLocale()){
            $params = $event->getParameters();
            if(array_key_exists('BaseInfo', $params)){
                $BaseInfo = $params['BaseInfo'];
                $locale = $this->request->getCurrentRequest()->getLocale();
                $this->functions->setChangeLangBaseInfo($BaseInfo, $locale);
                $event->setParameter('BaseInfo', $BaseInfo);
            }
        }
    }

    public function onProductListChangeLang(TemplateEvent $event)
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        if($locale === $this->eccubeConfig->get('locale')){
            return;
        }
        $pagination = $event->getParameter('pagination');
        $items = $pagination->getItems();
        $trans_items = [];
        foreach($items as $item){
            $trans_item = $this->functions->setChangeLangProduct($item, $locale);
            $trans_items[] = $trans_item;
        }
        $pagination->setItems($trans_items);
        $event->setParameter('pagination', $pagination);
    }

    public function onMypageFavoriteChangeLang(TemplateEvent $event)
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        if($locale === $this->eccubeConfig->get('locale')){
            return;
        }
        $pagination = $event->getParameter('pagination');
        $items = $pagination->getItems();
        $trans_items = [];
        foreach($items as $item){
            $trans_item = $this->functions->setChangeLangCustomerFavoriteProduct($item, $locale);
            $trans_items[] = $trans_item;
        }
        $pagination->setItems($trans_items);
        $event->setParameter('pagination', $pagination);
    }

    public function onMypageIndexChangeLang(TemplateEvent $event)
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        if($locale === $this->eccubeConfig->get('locale')){
            return;
        }
        $pagination = $event->getParameter('pagination');
        $items = $pagination->getItems();
        foreach($items as $Order){
            $this->functions->setChangeLangOrder($Order, $locale);
        }
    }

    public function onMypageHistoryChangeLang(TemplateEvent $event)
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        /**@var Order $Order*/
        $Order = $event->getParameter('Order');
        $this->functions->setChangeLangOrder($Order, $locale);
        $this->functions->setChangeLangShippings($Order->getShippings(), $locale);
    }

    public function onProductDetailChangeLang(TemplateEvent $event)
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        $Product = $event->getParameter('Product');
        $trans_item = $this->functions->setChangeLangProduct($Product, $locale);
        $event->setParameter('Product', $trans_item);
    }

    public function onCartIndexChangeLang(TemplateEvent $event)
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        $Carts = $event->getParameter('Carts');
        $trans_carts = [];
        foreach($Carts as $Cart){
            $CartItems = $Cart->getCartitems();
            $clone_cart_items = clone $CartItems;
            $Cart->clearCartItems();
            foreach($clone_cart_items as $CartItem){
                $trans_item = $this->functions->setChangeLangCartItem($CartItem, $locale);
                $Cart->addCartItem($trans_item);
            }

            $trans_carts[] = $Cart;
        }
        $event->setParameter('Carts', $trans_carts);
    }

    public function onNewsEditInsertTwig(TemplateEvent $event)
    {
        $twig = '@CrossBorder1/admin/Content/news_edit_insert.twig';
        $event->addSnippet($twig);
    }

    public function onClassCategoryInsertTwig(TemplateEvent $event)
    {
        $twig = '@CrossBorder1/admin/Product/class_category_insert.twig';
        $event->addSnippet($twig);
    }

    public function onShopMasterInsertTwig(TemplateEvent $event)
    {
        $twig = '@CrossBorder1/admin/Setting/Shop/shop_master_insert.twig';
        $event->addSnippet($twig);
    }

    public function onPaymentEditInsertTwig(TemplateEvent $event)
    {
        $twig = '@CrossBorder1/admin/Setting/Shop/payment_edit_insert.twig';
        $event->addSnippet($twig);
    }

    public function onDeliveryEditInsertTwig(TemplateEvent $event)
    {
        $twig = '@CrossBorder1/admin/Setting/Shop/delivery_edit_insert.twig';
        $event->addSnippet($twig);
        $twig = '@CrossBorder1/admin/Setting/Shop/delivery_time_prototype_insert.twig';
        $event->addSnippet($twig);
    }

    public function onShoppingIndexChangeLang(TemplateEvent $event)
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        /** @var Order $Order */
        $Order = $event->getParameter('Order');
        $this->functions->setChangeLangOrder($Order, $locale);
        $this->functions->setChangeLangOrderItems($Order->getOrderItems(), $locale);

        $event->setParameter('Order', $Order);
    }

    public function onShoppingConfirmChangeLang(TemplateEvent $event)
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        /** @var Order $Order */
        $Order = $event->getParameter('Order');
        $this->functions->setChangeLangOrder($Order, $locale);
        $this->functions->setChangeLangOrderItems($Order->getOrderItems(), $locale);
        $this->functions->setChangeLangPaymentMethod($Order, $locale);
        $this->functions->setChangeLangShippings($Order->getShippings(), $locale);

        $event->setParameter('Order', $Order);
    }

    public function onMailEditInsertTwig(TemplateEvent $event)
    {
        $twig = '@CrossBorder1/admin/Setting/Shop/mail_insert.twig';
        $event->addSnippet($twig);
    }

    public function handleFunction(TemplateEvent $event)
    {
        $params = $event->getParameters();
        if(isset($params['form'])){
            if($params['form'] instanceof FormView){
                $children = $params['form']->children;
                switch(true){
                    case //商品登録・編集
                        isset($children['product_content1']) &&
                        isset($children['product_content2']) &&
                        isset($children['product_content3']) &&
                        isset($children['product_content4']):
                        return $this->onProductInsertTwig($event);
                        break;
                    case
                        isset($children['class_name_content']):
                        return $this->onClassNameInsertTwig($event);
                        break;
                    case
                        isset($children['masterdata']):
                        return $this->onMasterdataInsertTwig($event);
                        break;
                    case
                        isset($children['product_tag_content']):
                        return $this->onTagInsertTwig($event);
                        break;
                    case
                        isset($children['category_content']):
                        return $this->onCategoryInsertTwig($event);
                        break;
                    case
                        isset($children['news_content1']) &&
                        isset($children['news_content2']):
                        return $this->onNewsEditInsertTwig($event);
                        break;
                    case
                        isset($children['class_category_content']):
                        return $this->onClassCategoryInsertTwig($event);
                        break;
                    case
                        isset($children['shop_master_content1']) &&
                        isset($children['shop_master_content2']) &&
                        isset($children['shop_master_content3']) &&
                        isset($children['shop_master_content4']):
                        return $this->onShopMasterInsertTwig($event);
                        break;
                    case
                        isset($children['payment_register_content']):
                        return $this->onPaymentEditInsertTwig($event);
                        break;
                    case
                        isset($children['delivery_content']):
                        return $this->onDeliveryEditInsertTwig($event);
                        break;
                    case
                        isset($children['mail_content']):
                        return $this->onMailEditInsertTwig($event);
                        break;
                }
            }
        }
    }
}
