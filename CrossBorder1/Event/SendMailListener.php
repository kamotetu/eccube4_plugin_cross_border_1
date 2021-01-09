<?php

namespace Plugin\CrossBorder1\Event;

use Eccube\Entity\ClassCategory;
use Eccube\Entity\MailTemplate;
use Eccube\Entity\Order;
use Eccube\Entity\ProductClass;
use Plugin\CrossBorder1\Entity\LangContent;
use Swift_Events_SendListener;
use Swift_Events_SendEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Eccube\Repository\MailTemplateRepository;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Common\EccubeConfig;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Repository\Master\PrefRepository;
use Plugin\CrossBorder1\Service\Event\MailEventListener\Functions;

class SendMailListener implements Swift_Events_SendListener
{
    protected $logger;

    private $request;

    private $mailTemplateRepository;

    private $langContentRepository;

    private $BaseInfo;

    private $eccubeConfig;

    private $em;

    private $prefRepository;

    private $functions;

    /**
     * MailerLoggerUtil constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger,
        RequestStack $request,
        MailTemplateRepository $mailTemplateRepository,
        LangContentRepository $langContentRepository,
        BaseInfoRepository $baseInfoRepository,
        EccubeConfig $eccubeConfig,
        EntityManagerInterface $em,
        PrefRepository $prefRepository,
        Functions $functions
    )
    {
        $this->logger = $logger;
        $this->request = $request;
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->langContentRepository = $langContentRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->eccubeConfig = $eccubeConfig;
        $this->em = $em;
        $this->prefRepository = $prefRepository;
        $this->functions = $functions;
    }

    /**
     * @param Swift_Events_SendEvent $evt
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        if($this->request->getCurrentRequest() !== null){
            $session = $this->request->getCurrentRequest()->getSession();
            $mail_type = $session->get('mail_type');
            if($mail_type !== 'shipping_notify'){
                return;
            }
            /**@var MailTemplate $MailTemplate */
            $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_shipping_notify_mail_template_id']);

            if(!is_null($MailTemplate)){
                /**@var LangContent $LangContent */
                $LangContent = $this->langContentRepository->findOneBy(
                    [
                        'entity' => 'Eccube\\Entity\\MailTemplate',
                        'entity_id' => $MailTemplate->getId(),
                        'entity_field' => 'mail_subject',
                        'language' => $this->request->getCurrentRequest()->getLocale(),
                    ]
                );
                if(!is_null($LangContent)){
                    /**@var \Swift_Message $message*/
                    $message = $evt->getMessage();
                    $message->setSubject('['.$this->BaseInfo->getShopName().'] '. $LangContent->getContent());
                }
            }
        }
    }

    /**
     * @param Swift_Events_SendEvent $evt
     */
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        if($this->request->getCurrentRequest() !== null){
            $session = $this->request->getCurrentRequest()->getSession();
            $mail_type = $session->get('mail_type');
            if($mail_type !== 'shipping_notify'){
                $session->set('mail_type', '');
                return;
            }
            $uow = $this->em->getUnitOfWork();
            $id_entity_map = $uow->getIdentityMap();
            $this->BaseInfo->setCompanyName($session->get('base_info_company_name'));
            $this->BaseInfo->setShopName($session->get('base_info_shop_name'));
            $this->BaseInfo->setGoodTraded($session->get('base_info_good_traded'));
            $this->BaseInfo->setMessage($session->get('base_info_message'));
            $order_id = max(array_keys($id_entity_map['Eccube\\Entity\\Order']));
            /**@var Order $Order*/
            $Order = $id_entity_map['Eccube\\Entity\\Order'][$order_id];
            //PaymentMethodを元に
            $this->functions->setDefaultPaymentMethod($Order);
            //Prefマスタデータを元に
            $Pref = $Order->getPref();
            $Pref->setName($session->get('order_pref_name'));
            $CustomerOrderStatus = $Order->getCustomerOrderStatus();
            $CustomerOrderStatus->setName($session->get('order_customer_order_status'));
            $this->functions->setDefaultOrderItems($Order);
            $this->functions->setDefaultShippings($Order);
            $this->request->getCurrentRequest()->setLocale($session->get('lang'));
            $this->functions->resetMailSession();
        }
    }
}
