<?php

namespace Plugin\CrossBorder1\Form\Extension\Admin;

use Eccube\Form\Type\Admin\ShopMasterType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Common\EccubeConfig;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Symfony\Component\HttpFoundation\RequestStack;


class ShopMasterTypeExtension extends AbstractTypeExtension
{
    private $eccubeConfig;

    private $langContentRepository;

    private $request;

    public function __construct(
        EccubeConfig $eccubeConfig,
        LangContentRepository $langContentRepository,
        RequestStack $request
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->langContentRepository = $langContentRepository;
        $this->request = $request;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'shop_master_content1',
            TextType::class,
            [
                'required' => false,
                'mapped' => false,
                'constraints' =>
                    [
                        new Assert\Length(
                            [
                                'max' => $this->eccubeConfig['eccube_stext_len'],
                            ]
                        ),
                    ],
                'attr' =>
                    [
                        'style' => "border-color:#638dff",
                    ],
            ]
        )->add(
            'shop_master_content2',
            TextType::class,
            [
                'required' => false,
                'mapped' => false,
                'constraints' =>
                    [
                        new Assert\Length(
                            [
                                'max' => $this->eccubeConfig['eccube_stext_len'],
                            ]
                        ),
                    ],
                'attr' =>
                    [
                        'style' => "border-color:#638dff",
                    ],
            ]
        )->add(
            'shop_master_content3',
            TextareaType::class,
            [
                'required' => false,
                'mapped' => false,
                'constraints' =>
                    [
                        new Assert\Length(
                            [
                                'max' => $this->eccubeConfig['eccube_lltext_len'],
                            ]
                        )
                    ],
                'attr' =>
                    [
                        'style' => "border-color:#638dff",
                    ],
            ]
        )->add(
            'shop_master_content4',
            TextareaType::class,
            [
                'required' => false,
                'mapped' => false,
                'constraints' =>
                    [
                        new Assert\Length(
                            [
                                'max' => $this->eccubeConfig['eccube_lltext_len'],
                            ]
                        )
                    ],
                'attr' =>
                    [
                        'style' => "border-color:#638dff",
                    ],
            ]
        );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $form = $event->getForm();
            $data = $event->getData();
            if(!empty($data->getId())){
                for($i = 1;4>=$i;++$i){
                    switch($i){
                        case 1:
                            $entity_field = 'company_name';
                            break;
                        case 2:
                            $entity_field = 'shop_name';
                            break;
                        case 3:
                            $entity_field = 'good_traded';
                            break;
                        case 4:
                            $entity_field = 'message';
                            break;
                    }
                    if($i <= 2){
                        $LangContent = $this->langContentRepository->findOneBy(
                            [
                                'entity' => get_class($data),
                                'entity_id' => $data->getId(),
                                'entity_field' => $entity_field,
                                'language' => $this->request->getCurrentRequest()->getLocale(),
                            ]
                        );
                        if(!is_null($LangContent)){
                            $options = $form->get('shop_master_content' . $i)->getConfig()->getOptions();
                            $options['data'] = $LangContent->getContent();
                            $form->add(
                                'shop_master_content' . $i,
                                TextType::class,
                                $options
                            );
                        }
                    }else{
                        $LangContent = $this->langContentRepository->findOneBy(
                            [
                                'entity' => get_class($data),
                                'entity_id' => $data->getId(),
                                'entity_field' => $entity_field,
                                'language' => $this->request->getCurrentRequest()->getLocale(),
                            ]
                        );
                        if(!is_null($LangContent)){
                            $options = $form->get('shop_master_content' . $i)->getConfig()->getOptions();
                            $options['data'] = $LangContent->getContent();
                            $form->add(
                                'shop_master_content' . $i,
                                TextareaType::class,
                                $options
                            );
                        }
                    }

                }
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ShopMasterType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => \Eccube\Entity\BaseInfo::class,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'shop_master';
    }
}
