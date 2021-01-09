<?php

namespace Plugin\CrossBorder1\Form\Type\Admin;

use Plugin\CrossBorder1\Entity\Config;
use Plugin\CrossBorder1\Entity\LangContent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Common\EccubeConfig;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Symfony\Component\HttpFoundation\RequestStack;



class ConfigLangType extends AbstractType
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

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'sort_no',
            HiddenType::class,
            [
                'label' => false,
            ]
        )->add(
            'name',
            TextType::class,
            [
                'required' => false,
                'constraints' =>
                    [
                        new Assert\Length(
                            [
                                'max' => 32,
                            ]
                        )
                    ]
            ]
        )->add(
            'visible',
            HiddenType::class,
            [
                'label' => false,
                'data' => 1,

            ]
        )->add(
            'backend_name',
            TextType::class,
            [
                'constraints' =>
                    [
                        new Assert\Length(
                            [
                                'max' => 16,
                            ]
                        )
                    ],
                'required' => false,
            ]
        )->add(
            'config_name_content',
            TextType::class,
            [
                'required' => false,
                'mapped'=> false,
                'attr' =>
                    [
                        'style' => "border-color:#638dff",
                    ],
                'constraints' =>
                    [
                        new Assert\Length(
                            [
                                'max' => 32,
                            ]
                        )
                    ]
            ]
        );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $form = $event->getForm();
            $data = $event->getData();
            if(!is_null($data) && is_array($data)){
                /**@var LangContent $LangContent*/
                $LangContent = $this->langContentRepository->findOneBy(
                    [
                        'entity' => 'Plugin\\CrossBorder1\\Entity\\Config',
                        'entity_id' => $data['id'],
                        'entity_field' => 'name',
                        'language' => $this->request->getCurrentRequest()->getLocale()
                    ]
                );
                if(!is_null($LangContent)){
                    $options = $form->get('config_name_content')->getConfig()->getOptions();
                    $options['data'] = $LangContent->getContent();
                    $form->add(
                        'config_name_content',
                        TextType::class,
                        $options
                    );
                }
                if($data['visible'] === false){
                    $options = $form->get('visible')->getConfig()->getOptions();
                    $options['data'] = 0;
                    $form->add(
                        'visible',
                        HiddenType::class,
                        $options
                    );
                }
            }
        });
    }

//    /**
//     * {@inheritdoc}
//     */
//    public function configureOptions(OptionsResolver $resolver)
//    {
//        $resolver->setDefaults(
//            [
//                'data_class' => 'Plugin\CrossBorder1\Entity\Config',
//                'query_builder' => function (EntityRepository $er){
//                    return $er->createQueryBuilder('c')->orderBy('c.sort_no', 'ASC');
//                },
//            ]
//        );
//    }
}
