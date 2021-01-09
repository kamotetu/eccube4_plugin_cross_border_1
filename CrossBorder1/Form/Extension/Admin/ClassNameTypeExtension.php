<?php

namespace Plugin\CrossBorder1\Form\Extension\Admin;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Plugin\CrossBorder1\Repository\LangcontentRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Admin\ClassNameType;


class ClassNameTypeExtension extends AbstractTypeExtension
{
    private $trans;

    private $langContentRepository;

    private $request;

    private $eccubeConfig;

    public function __construct(
        TranslatorInterface $trans,
        LangcontentRepository $langContentRepository,
        RequestStack $request,
        EccubeConfig $eccubeConfig
    )
    {
        $this->trans = $trans;
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
        //name(規格名)
            'class_name_content',
            TextType::class,
            [
                'required' => false,
                'mapped'=> false,
                'constraints' =>
                    [
                        new Assert\Length(
                            [
                                'max' => $this->eccubeConfig['eccube_stext_len'],
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
                $LangContent = $this->langContentRepository->findOneBy(
                    [
                        'entity' => get_class($data),
                        'entity_id' => $data->getId(),
                        'entity_field' => 'name',
                        'language' => $this->request->getCurrentRequest()->getLocale(),
                    ]
                );
                if(!is_null($LangContent)){
                    $options = $form->get('class_name_content')->getConfig()->getOptions();
                    $options['data'] = $LangContent->getContent();
                    $form->add(
                        'class_name_content',
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
        return ClassNameType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_class_name';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'allow_extra_fields' => true, //entityにないフォームのvalueをサーバーへ送信する
            ]
        );
    }

}
