<?php

namespace Plugin\CrossBorder1\Form\Extension\Admin;

use Eccube\Form\Type\Admin\ProductType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Common\EccubeConfig;


class ProductTypeExtension extends AbstractTypeExtension
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
            'product_content1',
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
        //description_detail
        )->add(
            'product_content2',
            TextareaType::class,
            [
                'required' => false,
                'mapped' => false,
                'attr' =>
                    [
                        'style' => "border-color:#638dff",
                    ],
                'constraints' => [
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_ltext_len']]),
                ],
            ]
        //description_list
        )->add(
            'product_content3',
            TextareaType::class,
            [
                'required' => false,
                'mapped' => false,
                'attr' =>
                    [
                        'style' => "border-color:#638dff",
                    ],
                'constraints' => [
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_ltext_len']]),
                ],
            ]
        //free_area
        )->add(
            'product_content4',
            TextareaType::class,
            [
                'required' => false,
                'mapped' => false,
                'attr' =>
                    [
                        'style' => "border-color:#638dff",
                    ],
            ]
        );
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $form = $event->getForm();
            $data = $event->getData();

            for($i = 1;4 >= $i;++$i){
                switch($i){
                    case 1:
                        $entity_field = 'name';
                        break;
                    case 2:
                        $entity_field = 'description_detail';
                        break;
                    case 3:
                        $entity_field = 'description_list';
                        break;
                    case 4:
                        $entity_field = 'free_area';
                        break;
                }
                if($i === 1){
                    $LangContent = $this->langContentRepository->findOneBy(
                        [
                            'entity' => get_class($data),
                            'entity_id' => $data['id'],
                            'language' => $this->request->getCurrentRequest()->getLocale(),
                            'entity_field' => $entity_field,
                        ]
                    );
                    if(!is_null($LangContent)){
                        $options = $form->get('product_content' . $i)->getConfig()->getOptions();
                        $options['data'] = $LangContent->getContent();
                        $form->add(
                            'product_content' . $i,
                            TextType::class,
                            $options
                        );
                    }
                }else{
                    $LangContent = $this->langContentRepository->findOneBy(
                        [
                            'entity' => get_class($data),
                            'entity_id' => $data['id'],
                            'language' => $this->request->getCurrentRequest()->getLocale(),
                            'entity_field' => $entity_field,
                        ]
                    );
                    if(!is_null($LangContent)){
                        $options = $form->get('product_content' . $i)->getConfig()->getOptions();
                        $options['data'] = $LangContent->getContent();
                        $form->add(
                            'product_content' . $i,
                            TextareaType::class,
                            $options
                        );
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
        return ProductType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_product';
    }
}
