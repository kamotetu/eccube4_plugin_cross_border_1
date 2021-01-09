<?php

namespace Plugin\CrossBorder1\Form\Extension\Admin;

use Eccube\Form\Type\Admin\ClassCategoryType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Common\EccubeConfig;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Symfony\Component\HttpFoundation\RequestStack;


class ClassCategoryTypeExtension extends AbstractTypeExtension
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
            'class_category_content',
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
        );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $form = $event->getForm();
            $data = $event->getData();
            if(!empty($data->getId())){
                $LangContent = $this->langContentRepository->findOneBy(
                    [
                        'entity' => get_class($data),
                        'entity_id' => $data->getId(),
                        'entity_field' => 'name',
                        'language' => $this->request->getCurrentRequest()->getLocale(),
                    ]
                );
                if(!is_null($LangContent)){
                    $options = $form->get('class_category_content')->getConfig()->getOptions();
                    $options['data'] = $LangContent->getContent();
                    $form->add(
                        'class_category_content',
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
        return ClassCategoryType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Eccube\Entity\ClassCategory',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_class_category';
    }
}
