<?php

namespace Plugin\CrossBorder1\Form\Extension\Admin;

use Eccube\Entity\MailTemplate;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Plugin\CrossBorder1\Repository\LangcontentRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Admin\ProductTag;
use Eccube\Form\Type\Admin\MailType;

class MailTypeExtension extends AbstractTypeExtension
{
    private $langContentRepository;

    private $request;

    private $eccubeConfig;

    public function __construct(
        LangcontentRepository $langContentRepository,
        RequestStack $request,
        EccubeConfig $eccubeConfig
    )
    {
        $this->langContentRepository = $langContentRepository;
        $this->request = $request;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'mail_content',
            TextType::class,
            [
                'required' => false,
                'mapped'=> false,
                'attr' =>
                    [
                        'style' => "border-color:#638dff",
                    ],
            ]
        );
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $form = $event->getForm();
            $data = $event->getData();
            if(!empty($data) && $data instanceof MailTemplate){
                $entity = get_class($data);
                $locale = $this->request->getCurrentRequest()->getLocale();
                $entity_id = $data->getId();
                $LangContent = $this->langContentRepository->findOneBy(
                    [
                        'entity' => $entity,
                        'entity_id' => $entity_id,
                        'entity_field' => 'mail_subject',
                        'language' => $locale,
                    ]);
                if(!is_null($LangContent)){
                    $options = $form->get('mail_content')->getConfig()->getOptions();
                    $options['data'] = $LangContent->getContent();
                    $form->add(
                        'mail_content',
                        TextType::class,
                        $options
                    );
                }
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return MailType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mail';
    }
}
