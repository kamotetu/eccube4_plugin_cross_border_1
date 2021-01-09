<?php

namespace Plugin\CrossBorder1\Form\Extension\Admin;

use Eccube\Entity\Delivery;
use Eccube\Form\Type\Admin\DeliveryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Common\EccubeConfig;


class DeliveryTypeExtension extends AbstractTypeExtension
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
            'delivery_content',
            TextType::class,
            [
                'required' => false,
                'mapped'=> false,
                'attr' =>
                    [
                        'style' => "border-color:#638dff",
                    ],
                'constraints' => [
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_stext_len']]),
                ],
            ]
        );
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $form = $event->getForm();
            $data = $event->getData();
            if($data instanceof Delivery && !empty($data->getId())){
                $LangContent = $this->langContentRepository->findOneBy(
                    [
                        'entity' => get_class($data),
                        'entity_id' => $data->getId(),
                        'entity_field' => 'name',
                        'language' => $this->request->getCurrentRequest()->getLocale(),
                    ]
                );
            }
            if(!is_null($LangContent)){
                $options = $form->get('delivery_content')->getConfig()->getOptions();
                $options['data'] = $LangContent->getContent();
                $form->add(
                    'delivery_content',
                    TextType::class,
                    $options
                );
            }
        });

    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return DeliveryType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Eccube\Entity\Delivery',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'delivery';
    }
}
