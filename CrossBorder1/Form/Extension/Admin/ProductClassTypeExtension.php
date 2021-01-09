<?php

namespace Plugin\CrossBorder1\Form\Extension\Admin;

use Eccube\Form\Type\Admin\ProductClassType;
//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
//use Symfony\Component\Form\FormEvent;
//use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
//use Plugin\CrossBorder1\Repository\LangContentRepository;
//use Symfony\Component\HttpFoundation\RequestStack;
//use Eccube\Form\Type\PriceType;

class ProductClassTypeExtension extends AbstractTypeExtension
{
//    private $langContentRepository;
//
//    private $request;

    public function __construct(
//        LangContentRepository $langContentRepository,
//        RequestStack $request
    )
    {
//        $this->langContentRepository = $langContentRepository;
//        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //price02(販売価格)
//        $builder->add(
//            'content1',
//            PriceType::class,
//            [
//                'required' => false,
//                'mapped'=> false,
//                'attr' =>
//                    [
//                        'style' => "border-color:#638dff",
//                    ],
//            ]
//        //price01(通常価格)
//        )->add(
//            'content2',
//            PriceType::class,
//            [
//                'required' => false,
//                'mapped' => false,
//                'attr' =>
//                    [
//                        'style' => "border-color:#638dff",
//                    ],
//            ]
//        //delivery_duration_id(発送日目安)
//        )->add(
//            'content3',
//            ChoiceType::class,
//            [
//                'required' => false,
//                'placeholder' => 'common.select__unspecified',
//                'mapped' => false,
//                'choices' => [],
//                'attr' =>
//                    [
//                        'style' => "border-color:#638dff",
//                    ],
//            ]
//        );
//        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
//            $form = $event->getForm();
//            $data = $event->getData();
//            if(!is_null($data)){
//                //price02(販売価格)
//                $LangContent = $this->langContentRepository->findOneBy(
//                    [
//                        'entity' => get_class($data),
//                        'entity_id' => $data['id'],
//                        'language' => $this->request->getCurrentRequest()->getLocale(),
//                        'entity_field' => 'price02',
//                    ]
//                );
//                if(!is_null($LangContent)){
//                    $options = $form->get('content1')->getConfig()->getOptions();
//                    $options['data'] = (int)$LangContent->getContent();
//                    $form->add(
//                        'content1',
//                        PriceType::class,
//                        $options
//                    );
//                }
//
//                //price01(通常価格)
//                $LangContent = $this->langContentRepository->findOneBy(
//                    [
//                        'entity' => get_class($data),
//                        'entity_id' => $data['id'],
//                        'language' => $this->request->getCurrentRequest()->getLocale(),
//                        'entity_field' => 'price01',
//                    ]
//                );
//                if(!is_null($LangContent)){
//                    $options = $form->get('content2')->getConfig()->getOptions();
//                    $options['data'] = (int)$LangContent->getContent();
//                    $form->add(
//                        'content2',
//                        PriceType::class,
//                        $options
//                    );
//                }
//                //delivery_duration_id(発送日目安)
//                $delivery_duration_options = $form->get('delivery_duration')->getConfig()->getOptions();
//                $result = $delivery_duration_options['choice_loader']->loadChoiceList()->getChoices();
//                $choices = [];
//                foreach($result as $value){
//                    $choices[$value['name']] = $value['id'];
//                }
//                $form->add(
//                    'content3',
//                    ChoiceType::class,
//                    [
//                        'required' => false,
//                        'placeholder' => 'common.select__unspecified',
//                        'mapped' => false,
//                        'choices' => $choices,
//                        'attr' =>
//                            [
//                                'style' => "border-color:#638dff",
//                            ],
//                    ]
//                );
//                $LangContent = $this->langContentRepository->findOneBy(
//                    [
//                        'entity' => get_class($data),
//                        'entity_id' => $data['id'],
//                        'language' => $this->request->getCurrentRequest()->getLocale(),
//                        'entity_field' => 'delivery_duration_id',
//                    ]
//                );
//                if(!is_null($LangContent)){
//                    $options = $form->get('content3')->getConfig()->getOptions();
//                    $options['data'] = (int)$LangContent->getContent();
//                    $form->add(
//                        'content3',
//                        ChoiceType::class,
//                        $options
//                    );
//                }
//            }
//        });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ProductClassType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_product_class';
    }
}
