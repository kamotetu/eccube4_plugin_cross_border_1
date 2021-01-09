<?php

namespace Plugin\CrossBorder1\Form\Extension\Admin;

use Eccube\Entity\DeliveryTime;
use Eccube\Form\Type\Admin\DeliveryTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Eccube\Common\EccubeConfig;


class DeliveryTimeTypeExtension extends AbstractTypeExtension
{
    private $langContentRepository;

    private $request;

    private $eccubeConfig;

    public function __construct(
        LangContentRepository $langContentRepository,
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
        //name
        $builder->add(
            'delivery_time_content',
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

            if($data instanceof DeliveryTime && !empty($data->getId())){
                $LangContent = $this->langContentRepository->findOneBy(
                    [
                        'entity' => get_class($data),
                        'entity_id' => $data->getId(),
                        'entity_field' => 'delivery_time',
                        'language' => $this->request->getCurrentRequest()->getLocale(),
                    ]
                );
                if(!is_null($LangContent)){
                    $options = $form->get('delivery_time_content')->getConfig()->getOptions();
                    $options['data'] = $LangContent->getContent();
                    $form->add(
                        'delivery_time_content',
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
        return DeliveryTimeType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Eccube\Entity\DeliveryTime',
                'allow_extra_fields' => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'delivery_time';
    }
}
