<?php

/*
 * メール送信時に諸々データが上書きされてしまい
 * 条件分岐が複雑化してしまうので
 * 別のEventListenerとして定義
 */

namespace Plugin\CrossBorder1\Event;

use Eccube\Entity\Customer;
use Eccube\Entity\MailTemplate;
use Eccube\Entity\Order;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Plugin\CrossBorder1\Entity\OrderLang;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eccube\Common\EccubeConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Plugin\CrossBorder1\Service\Event\MailEventListener\Functions;
use Plugin\CrossBorder1\Repository\ConfigRepository;
use Twig\Environment;
use Eccube\Repository\BaseInfoRepository;
use Swift_Message;
use Eccube\Repository\MailTemplateRepository;
use Plugin\CrossBorder1\Repository\OrderLangRepository;


class MailEventListener implements EventSubscriberInterface
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

    private $orderLangRepository;

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
        MailTemplateRepository $mailTemplateRepository,
        OrderLangRepository $orderLangRepository
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
        $this->orderLangRepository = $orderLangRepository;
    }

    public static function getSubscribedEvents()
    {
        return
            [
                'Mail/order.twig' => 'onMailOrderChangeLang',
                'Mail/contact_mail.twig' => 'onMailContactChangeLang',
                'Mail/entry_confirm.twig' => 'onMailEntryConfirmChangeLang',
                'Mail/entry_complete.twig' => 'onMailEntryCompleteChangeLang',
                'Mail/customer_withdraw_mail.twig' => 'onMailCustomerWithdrawMailChangeLang',
                'Mail/forgot_mail.twig' => 'onMailForgotMailChangeLang',
                'Mail/reset_complete_mail.twig' => 'onMailResetCompleteMailChangeLang',
                'Mail/shipping_notify.twig' => 'onMailShippingNotifyChangeLang',
                'Mail/shipping_notify.html.twig' => 'onMailShippingNotifyChangeLang',
                EccubeEvents::MAIL_ORDER => 'onChangeOrderMailParams',
            ];
    }

    //受注メール内変数多言語処理
    public function onMailOrderChangeLang(TemplateEvent $event)
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        $Order = $event->getParameter('Order');
        $this->functions->setSessionOrderPref($Order);
        $this->functions->setChangeLangOrder($Order, $locale);
        $BaseInfo = $this->BaseInfo;
        $this->functions->setSessionDefaultBaseInfo($BaseInfo);
        $this->functions->setChangeLangBaseInfo($BaseInfo, $locale);
        $this->functions->setSessionMailType('order');
    }

    //お問い合わせメール多言語処理
    public function onMailContactChangeLang(TemplateEvent $event)
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        $Pref = $event->getParameter('data')['pref'];
        $this->functions->setChangeLangMasterData($Pref, $locale, 'Eccube\\Entity\\Master\\Pref');
        $BaseInfo = $this->BaseInfo;
        $this->functions->setChangeLangBaseInfo($BaseInfo, $locale);
        $this->functions->setSessionMailType('contact');
    }

    //会員登録(仮登録)メール多言語処理
    public function onMailEntryConfirmChangeLang(TemplateEvent $event)
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        /**@var Customer $Customer*/
        $Customer = $event->getParameter('Customer');
        $Pref = $Customer->getPref();
        $Sex = $Customer->getSex();
        $Job = $Customer->getJob();
        $this->functions->setChangeLangMasterData($Pref, $locale, 'Eccube\\Entity\\Master\\Pref');
        $this->functions->setChangeLangMasterData($Sex, $locale, 'Eccube\\Entity\\Master\\Sex');
        $this->functions->setChangeLangMasterData($Job, $locale, 'Eccube\\Entity\\Master\\Job');
        $BaseInfo = $this->BaseInfo;
        $this->functions->setChangeLangBaseInfo($BaseInfo, $locale);
        $this->functions->setSessionMailType('entry_confirm');
    }

    //会員登録(完了)メール多言語処理
    public function onMailEntryComplete(TemplateEvent $event)
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        $BaseInfo = $this->BaseInfo;
        $this->functions->setChangeLangBaseInfo($BaseInfo, $locale);
        $this->functions->setSessionMailType('entry_complete');
    }

    //退会メール多言語処理
    public function onMailCustomerWithdrawMailChangeLang(TemplateEvent $event)
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        $BaseInfo = $this->BaseInfo;
        $this->functions->setChangeLangBaseInfo($BaseInfo, $locale);
        $this->functions->setSessionMailType('customer_withdraw_mail');
    }

    //パスワードリセットメール多言語処理
    public function onMailForgotMailChangeLang()
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        $BaseInfo = $this->BaseInfo;
        $this->functions->setChangeLangBaseInfo($BaseInfo, $locale);
        $this->functions->setSessionMailType('forgot_mail');
    }

    public function onMailResetCompleteMailChangeLang(TemplateEvent $event)
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        $BaseInfo = $this->BaseInfo;
        $this->functions->setChangeLangBaseInfo($BaseInfo, $locale);
        $this->functions->setSessionMailType('reset_complete_mail');
    }

    public function onMailShippingNotifyChangeLang(TemplateEvent $event)
    {
        $session_flag = false;
        $debug = debug_backtrace(2);
        foreach($debug as $result){
            if($result['function'] === "sendShippingNotifyMail"){
                $session_flag = true;
                break;
            }
        }
        /**@var Order $Order*/
        $Order = $event->getParameter('Order');
        /**@var OrderLang $OrderLang */
        $OrderLang = $this->orderLangRepository->findOneBy(
            [
                'order_id' => $Order->getId(),
            ]
        );
        if(!is_null($OrderLang)){
            $locale = $OrderLang->getLanguage();
            $BaseInfo = $this->BaseInfo;
            $this->request->getCurrentRequest()->setDefaultLocale($locale);
            if($session_flag){
                $this->functions->setSessionOrderPref($Order);
                $this->functions->setSessionOrderCustomerOrderStatus($Order);
                $this->functions->setSessionDefaultBaseInfo($BaseInfo);
                $this->functions->setSessionMailType('shipping_notify');
            }
            $this->functions->setChangeLangBaseInfo($BaseInfo, $locale);
            $this->functions->setChangeLangOrder($Order, $locale);
        }
    }

    //受注メールSwiftMail設定多言語処理&&メール内で使用したEntityなどの値をリセット
    public function onChangeOrderMailParams(EventArgs $args)
    {
        /**@var MailTemplate $MailTemplate */
        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_order_mail_template_id']);
        $locale = $this->request->getCurrentRequest()->getLocale();
        $mail_subject = $this->functions->getChangeLangMailSubject($MailTemplate, $locale);
        /**@var Swift_Message $message */
        $message = $args->getArgument('message');
        $message->setSubject('['.$this->BaseInfo->getShopName().'] '.$mail_subject);
        $Order = $args->getArgument('Order');
        $this->functions->setDefaultOrder($Order);
        $this->functions->setDefaultBaseInfo($this->BaseInfo);
        $this->functions->resetMailSession();
    }
}
